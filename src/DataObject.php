<?php

namespace TableGenerator;

/**
 * Class DataObject
 * A sample representation for an Array To Table interface.
 *
 * Simplest usage:
 * <pre>
 * $tableDataSource =
 * [
 *        [
 *            'im_a_col'   => 'First field in first row',
 *            'second_col' => 'second field in first row'
 *        ],
 *        [
 *            'im_a_col'   => 'First field in second row',
 *            'second_col' => 'second field in first row'
 *        ]
 *    ];
 * $do = new TableGenerator\DataObject([],$tableDataSource);
 * //you can pass this to a Render class by $render->setData($do);
 * </pre>
 *
 * @package TableGenerator
 * @see     \TableGenerator\Render\HTMLTable
 */
class DataObject implements ArrayDataSourceInterface, TransformableDataInterface, \Countable
{
	use ArrayDataSourceTrait,
		TransformableDataTrait;

	/**
	 * @var int Pointer in $bodyData.
	 */
	protected $position;

	/**
	 * @var bool If the formatter field doesn't exists in the original data source, the first parameter (what would be the field itself)
	 *      is skipped and starts with the row param instead (lets the user to use short config)
	 */
	protected $skipEmptyParamsInFormatter = true;

	/**
	 * @var array Cache the field config definitions at first run for faster processing.
	 */
	private $bodyFieldExist = [];

	/**
	 * DataObject constructor.
	 *
	 * @param array $columnArray List of cols. On empty array, this would be
	 * @param array $data
	 * @param bool $skipEmptyParamsInFormatter Skip first field if thats not exists in data (data rows has to be consistent)
	 */
	public function __construct(array $columnArray = [], array $data = [], $skipEmptyParamsInFormatter = false)
	{
		if (!empty($columnArray))
		{
			$this->setColumnsAsArray($columnArray);
		}

		if (!empty($data))
		{
			$this->setData($data);
		}

		$this->setSkipEmptyParamsInFormatter((bool)$skipEmptyParamsInFormatter);
	}

	/**
	 * @inheritdoc
	 */
	public function rewind()
	{
		$this->position = 0;
		$this->bodyFieldExist = [];
	}

	/**
	 * @inheritdoc
	 */
	public function current()
	{
		if ($this->rawMode)
		{
			return $this->bodyData[$this->position];
		}

		if ($this->headerData === null)
		{
			$this->setPrimitiveColumns();
		}

		$row = [];
		foreach ($this->headerData as $field => $data)
		{
			if ($this->position == 0)
			{
				//we cache this in every run, we can shave off a lot of isset command assuming the data is consistent ((row_count-1) * field_count times)
				$this->bodyFieldExist[$field] = isset($this->bodyData[$this->position][$field]);
			}

			if ($data['formatter'])
			{
				if (!$this->bodyFieldExist[$field] && $this->skipEmptyParamsInFormatter)
				{
					$callParams = [
						$this->bodyData[$this->position],
						$this->position
					];
				}
				else
				{
					$callParams = [
						($this->bodyFieldExist[$field] ? $this->bodyData[$this->position][$field] : ''),
						$this->bodyData[$this->position],
						$this->position
					];
				}

				$row[$field] = call_user_func_array($data['formatter'], $callParams);
			}
			else
			{
				$row[$field] = ($this->bodyFieldExist[$field] ? $this->bodyData[$this->position][$field] : '');
			}

			//ugly hack to remove every non-printable whitespace chars (mostly tabs) that could cause nasty bugs in certain rendering modules.
			$row[$field] = preg_replace("/\s+/", " ", $row[$field]);
		}

		return $row;
	}

	/**
	 * @inheritdoc
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * @inheritdoc
	 */
	public function next()
	{
		$this->position++;
	}

	/**
	 * @inheritdoc
	 */
	public function valid()
	{
		return isset($this->bodyData[$this->position]);
	}

	/**
	 * @inheritdoc
	 */
	public function count()
	{
		return count($this->bodyData);
	}

	/**
	 * @return boolean
	 */
	public function isSkipEmptyParamsInFormatter()
	{
		return $this->skipEmptyParamsInFormatter;
	}

	/**
	 * @param boolean $skipEmptyParamsInFormatter
	 */
	public function setSkipEmptyParamsInFormatter($skipEmptyParamsInFormatter)
	{
		$this->skipEmptyParamsInFormatter = $skipEmptyParamsInFormatter;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getHeaderRow()
	{
		if (empty($this->headerData))
		{
			$this->setPrimitiveColumns();
		}

		return self::pluck($this->headerData, 'displayedName');
	}

	/**
	 * For the lazy ones who just want to make a table fast. Called by default if no columns set.
	 */
	public function setPrimitiveColumns()
	{
		$rawColumns = array_keys(reset($this->bodyData));

		foreach ($rawColumns as $key => $columnName)
		{
			$this->setColumn($columnName, $columnName);
		}

		return $this;
	}
}