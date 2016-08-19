<?php

namespace TableGenerator;

abstract class TransformableData implements TransformableDataInterface
{

	private function __construct()
	{

	}

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
	 * @inheritdoc
	 *
	 * @return bool
	 */
	public function setColumnsAsArray(array $columns)
	{
		foreach ($columns as $columnId => $data)
		{
			$hasDisplayName = isset($data['displayedName']);
			$found = false;

			if (is_array($data))
			{
				if ($hasDisplayName)
				{
					//rendesen van összerakva: ['displayedName' =>"blabla",'formatter' => function($field, $row)... ]
					$this->setColumn($columnId, $data['displayedName'], (!empty($data['formatter']) ? $data['formatter'] : null));
					$found = true;
				}
				elseif (count($data) == 2)
				{
					//hmmmm, 2 elem van, feltételezzük, hogy elírta vagy numerikus tömbben adta meg, így első elem a név,
					// második a closure fgv, ha nem úgyis megdöglik... :D
					$keys = array_keys($data);
					$this->setColumn($columnId, $data[$keys[0]], $data[$keys[1]]);
					$found = true;
				}
				elseif (count($data) == 1)
				{
					//nincs formázó, de tömb, a név lesz az
					$keys = array_keys($data);
					$this->setColumn($columnId, $data[$keys[1]]);
					$found = true;
				}
			}
			else
			{
				if (is_string($data))
				{
					$found = true;
					$this->setColumn($columnId, $data);
				}
			}

			if (!$found)
			{
				trigger_error('Unknown column head data at position: ' . $columnId);

				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedArray($assocArray = true)
	{
		throw new \Exception('Not implemented in this class');
	}

	/**
	 * @inheritdoc
	 */
	public function setRawMode($rawMode = false)
	{
		$this->rawMode = $rawMode;
	}

	/**
	 * @inheritdoc
	 */
	public function getHeaderRow()
	{
		return self::pluck($this->headerData, 'displayedName');
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