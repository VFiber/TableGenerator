<?php

namespace TableGenerator;


interface TransformableDataInterface extends TableableDataSourceInterface
{
	/**
	 * @param array $columns Assoc array: ['field_id' => 'DisplayedName For field_id column'] or ['field_id' => ['displayedName'
	 *                       =>"blabla",'formatter' => function($field, $row)... ]]
	 *
	 * @return bool
	 */
	public function setColumnsAsArray(array $columns);

	/**
	 * Defines a column on the data.
	 *
	 * @param string        $columnId
	 * @param string        $displayedName Col title
	 * @param callable|null $formatter see: TransformableDataInterface::addTransformFunction
	 *
	 * @see TransformableDataInterface::addTransformFunction
	 *
	 * @return $this
	 */
	public function setColumn($columnId = 'internal_unique_colname', $displayedName = 'First table col', callable $formatter = null);

	/**
	 * Adds a transform function to an already defined column.
	 *
	 * @param string   $columnId
	 * @param callable $formatter (function name or any other callable. The params passed during call is the field content, second the data row.
	 * Examples:
	 * <pre>
	 * //simple transform for pretty display (and considered bad practice,
	 *  Business Logic should be here not display formatting, but well...)
	 * $datasource->setColumn('id',function($id,$sor){return '#'.$id;});
	 * or
	 *  $datasource->setColumn('price','function_name_to_call');
	 * or
	 *  $datasource->setColumn('price','Classname::staticFunction');
	 * or
	 *  $datasource->setColumn('price',[$object,'functionNameOnObject']);
	 * </pre>
	 *
	 * @see call_user_func()
	 * @link http://php.net/manual/en/language.types.callable.php
	 * @return $this
	 */
	public function addTransformFunction($columnId, callable $formatter);

	/**
	 * Az alkalmazott mezőtranszformációk (formázások) nélkül adja vissza a sorokat nyersen.
	 *
	 * @param bool $rawMode
	 *
	 * @return mixed
	 */
	public function setRawMode($rawMode = false);
}