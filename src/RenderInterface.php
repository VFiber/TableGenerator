<?php

namespace TableGenerator;

interface RenderInterface
{
	/**
	 * Sets the data source object for rendering.
	 *
	 * @param \TableGenerator\TableableDataSourceInterface $d
	 *
	 * @return $this
	 */
	public function setDataObject(TableableDataSourceInterface $d);

	/**
	 * Renders the table to the output
	 *
	 * @return mixed
	 */
	public function renderTable();
}