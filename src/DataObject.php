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
class DataObject extends TransformableData implements ArrayDataSourceInterface, TransformableDataInterface, \Iterator, \Countable
{
	/**
	 * @var array Holds data until rendering. Its very inefficient for large amount of data (in terms of memory usage).
	 */
	protected $bodyData = null;

	/**
	 * @var int Pointer in $bodyData.
	 */
	protected $position;

	/**
	 * DataObject constructor.
	 *
	 * @param array $columnArray List of cols. On empty array, this would be
	 * @param array $data
	 */
	public function __construct(array $columnArray = [], array $data = [])
	{
		if (!empty($columnArray))
		{
			$this->setColumnsAsArray($columnArray);
		}

		if (!empty($data))
		{
			$this->setData($data);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function rewind()
	{
		$this->position = 0;
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
			if ($data['formatter'])
			{
				$row[$field] =
					call_user_func($data['formatter'],
						(isset($this->bodyData[$this->position][$field]) ? $this->bodyData[$this->position][$field] : ''),
						$this->bodyData[$this->position]);
			}
			else
			{
				$row[$field] = $this->bodyData[$this->position][$field];
			}

			//ugly hack to remove every non-printable whitespace chars (tabs) that could cause nasty bugs in certain rendering modules.
			$row[$field] = preg_replace("/\s+/", " ", $row[$field]);
		}

		return $row;
	}

	/**
	 * @inheritdoc
	 */
	public function getRenderedArray($assocArray = true)
	{
		$transformedArray = [];

		foreach ($this as $fields)
		{
			$transformedArray[] = ($assocArray ? $fields : array_values($fields));
		}

		return $transformedArray;
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
	 * For the lazy ones who just want to make a table fast. Called by default if no columns set.
	 */
	public function setPrimitiveColumns()
	{
		$rawColumns = array_keys(reset($this->bodyData));

		foreach ($rawColumns as $key => $columnName)
		{
			$this->setColumn($columnName, $columnName);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function setData(array $data)
	{
		//FIXME_ asszoc tömbök
		$this->bodyData = array_values($data);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function addRow($row)
	{
		$this->bodyData[] = $row;

		return $this;
	}

	public function canDisplayCorrectly()
	{
		if (count($this->headerData) != count(reset($this->bodyData)))
		{
			trigger_error('Head column count and data column count does not match!');

			return false;
		}

		return true;
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

		return parent::getHeaderRow();
	}
}

