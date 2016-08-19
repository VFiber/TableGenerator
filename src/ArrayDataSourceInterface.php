<?php

namespace TableGenerator;

interface ArrayDataSourceInterface
{
	/**
	 * @param array $data Data that needs to be displayed. Eg.: [['col1' => 'First row first field', 'col2' => 'field2 value'],['col1' => 'mezo','col2' => 'field2 value']]
	 *
	 * @return $this
	 */
	public function setData(array $data);

	/**
	 * @param array $row For masochists
	 *
	 * @see setData For mass-data upload
	 * @return $this
	 */
	public function addRow($row);
}