<?php
namespace TableGenerator;

/**
 * Class TransformableDataTrait
 *
 * Use this, if you plan to modify your data source on display.
 *
 * @package TableGenerator
 */
trait TransformableDataTrait
{
	/**
	 * The data is requested as raw (without formatting) on output.
	 *
	 * @var bool
	 */
	protected $rawMode = false;

	/**
	 * Holds table config parameters.
	 *
	 * @var array
	 */
	protected $headerData = null;

	/**
	 * @inheritdoc
	 */
	public function getColumnIds()
	{
		return array_keys($this->headerData);
	}

	/**
	 * @inheritdoc
	 */
	public function getColumnDisplayedName($coulmnId = 'internal_unique_colname')
	{
		if (!isset($this->headerData[$coulmnId]))
		{
			return false;
		}

		return $this->headerData[$coulmnId]['displayedName'];
	}

	/**
	 * @inheritdoc
	 */
	public function addTransformFunction($columnId, callable $formatter)
	{
		if (isset($this->headerData[$columnId]) && is_callable($formatter))
		{
			$this->setColumn($columnId, $this->headerData[$columnId]['displayedName'], $formatter);
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function setColumn($columnId = 'internal_unique_colname', $displayedName = 'First table col', callable $formatter = null)
	{
		$this->headerData[$columnId] = [
			'displayedName' => $displayedName,
			'formatter'     => (is_callable($formatter) ? $formatter : false)
		];

		return $this;
	}

	/**
	 * @param array $columns Assoc array: ['field_id' => 'DisplayedName For field_id column'] or ['field_id' => ['displayedName'
	 *                       =>"blabla",'formatter' => function($field, $row)... ]]
	 *
	 * <pre>
	 *        //callable could be anything that returns true for is_callable($callable)
	 *        $callable = [$classInstance, 'functionName'];
	 *        $callable = function ($col, $rowFromDataSource)
	 *        {
	 *            return floor($col);
	 *        };
	 *        $callable = [$classInstance, 'functionName'];
	 *        //col definition example
	 *        $stuff = [
	 *            'name'  => 'Column name to display',
	 *            //simple name with transform function
	 *            'name2' => ['Column name to display', $callable],
	 *            //for the lazy ones, this time 'name3' is set both for internal and displayed name
	 *            'name3' => $callable,
	 *        ];
	 * </pre>
	 *
	 * @return $this
	 */
	public function setColumnsAsArray(array $columns)
	{

		foreach ($columns as $columnId => $data)
		{
			if (!$this->setColAsConfigArray($columnId, $data))
			{
				trigger_error('Unknown column head data at position: ' . $columnId);
			}
		}

		return $this;
	}

	protected function setColAsConfigArray($columnId, $data)
	{
		if ($columnId !== 0 && empty($columnId))
		{
			//empty col, wtf, why would anyone want to specify an empty col?
			return false;
		}

		//we catch format functions without nice displayable name here
		if (is_callable($data))
		{
			$this->setColumn($columnId, $columnId, $data);

			return true;
		}

		//if its a string, most likely the user wants to be super-fast: ['assocID' => 'Name to display as head']
		if (is_string($data))
		{
			$this->setColumn($columnId, $data);

			return true;
		}

		if (empty($data))
		{
			//he wants an empty col for some reason (spacing perhaps?!)
			$this->setColumn($columnId, $columnId);
		}

		if (!is_array($data))
		{
			//its not a string and not a callable and an array, we do not know what was the poets' real intention
			trigger_error('Column config for col "'.$columnId.'" cannot be interpreted!');

			return false;
		}

		if (isset($data['displayedName']))
		{
			//someone was really meat: ['displayedName' =>"blabla",'formatter' => function($field, $row)... ]
			$this->setColumn($columnId, $data['displayedName'], (!empty($data['formatter']) ? $data['formatter'] : null));

			return true;
		}

		$elementCount = count($data);

		if ($elementCount == 1)
		{
			//nincs formázó, de tömb, a név lesz az
			$keys = array_keys($data);
			is_string($data[$keys[1]]);
			$this->setColumn($columnId, $data[$keys[1]]);

			return true;
		}

		if ($elementCount == 2)
		{
			//we've got 2 element in the array, we assumes its first displayed name, second as a callable
			$keys = array_keys($data);
			$this->setColumn($columnId, $data[$keys[0]], $data[$keys[1]]);

			return true;
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function setRawMode($rawMode = false)
	{
		$this->rawMode = $rawMode;
	}

	/**
	 * Pull a particular property from each assoc. array in a simple assoc array,
	 * returning and array of the property values from each item.
	 *
	 * @param  array  $a    Array to get data from
	 * @param  string $prop Property to read
	 *
	 * @return array        Array of property values
	 */
	static function pluck($a, $prop)
	{
		$out = [];

		foreach ($a as $key => $value)
		{
			$out[$key] = $value[$prop];
		}

		return $out;
	}
}