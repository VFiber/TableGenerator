<?php

namespace TableGenerator;

interface TableableDataSourceInterface
{

	/**
	 * Defines a column on the data source.
	 *
	 * @param string $columnId      Column ID. If you redefine a column, it will overwrite the previous one.
	 * @param string $displayedName The renderers using this string to display column "title" to the user in table head and foot.
	 *
	 * @return $this For easy method chaining.
	 */
	public function setColumn($columnId = 'internal_unique_colname', $displayedName = 'First table col');

	/**
	 * Returns the defined column list.
	 *
	 * @see setColumn()
	 * @return array
	 */
	public function getColumnIds();

	/**
	 * Returns with the specified column name (title), displayed for the end-user.
	 *
	 * @param string $coulmnId Internal column ID
	 *
	 * @return mixed
	 */
	public function getColumnDisplayedName($coulmnId = 'internal_unique_colname');

	/**
	 * Returns with an array of string displayed names (column titles).
	 *
	 * @return array Array of string.
	 */
	public function getHeaderRow();

	/**
	 * Returns with the rendered / transformed dataset.
	 *
	 * @param bool $assocArray
	 *
	 * @return mixed
	 */
	public function getRenderedArray($assocArray = true);
}