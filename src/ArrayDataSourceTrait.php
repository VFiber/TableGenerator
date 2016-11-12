<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2016. 11. 12.
 * Time: 12:56
 */

namespace TableGenerator;


trait ArrayDataSourceTrait
{
	/**
	 * @var array Holds data until rendering. Its very inefficient for large amount of data (in terms of memory usage).
	 */
	protected $bodyData = null;

	/**
	 * @param array $data Data that needs to be displayed. Eg.: [['col1' => 'First row first field', 'col2' => 'field2 value'],['col1' => 'mezo','col2' => 'field2 value']]
	 *
	 * @return $this
	 */
	public function setData(array $data)
	{
		if (array_keys($data) !== range(0, count($data) - 1))
		{
			//tought a lot about this, but if you need another data as a sequence, you can easily re-define those with the use of different iterators
			trigger_error('The defined data contains associative indices instead of numeric. Extra indice data will be dropped.');
			$this->bodyData = array_values($data);
		}
		else
		{
			$this->bodyData = $data;
		}

		if (!is_array($this->bodyData))
		{
			$this->bodyData = [];
			throw new \Exception("Invalid data array, data cannot be interpreted as items!");
		}

		return $this;
	}

	/**
	 * @param array $row For masochists
	 *
	 * @see setData For mass-data upload
	 * @return $this
	 */
	public function addRow($row)
	{
		$this->bodyData[] = $row;

		return $this;
	}
}