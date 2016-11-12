<?php
/**
 * Created by PhpStorm.
 * User: Fiber
 * Date: 2016. 11. 12.
 * Time: 12:38
 */

namespace TableGenerator;

/**
 * Class TableableDataSourceTrait
 * This trait covers mostly the TableGenerator\TableableDataSourceInterface implements with very basic functionality
 * @package TableGenerator
 */
trait TableableDataSourceTrait
{
	/**
	 * Holds table config parameters.
	 *
	 * @var array
	 */
	protected $headerData = null;

	/**
	 * @inheritdoc
	 */
	public function setColumn($columnId = 'internal_unique_colname', $displayedName = 'First table col')
	{
		$this->headerData[$columnId] = [
			'displayedName' => $displayedName,
		];

		return $this;
	}

	/**
	 * Returns the defined column list.
	 *
	 * @see setColumn()
	 * @return array
	 */
	public function getColumnIds()
	{
		return array_keys($this->headerData);
	}

	/**
	 * Returns with the specified column name (title), displayed for the end-user.
	 *
	 * @param string $coulmnId Internal column ID
	 *
	 * @return mixed
	 */
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
	 * Returns with an array of string displayed names (column titles).
	 *
	 * @return array Array of string.
	 */
	public function getHeaderRow()
	{
		if (empty($this->headerData))
		{
			return [];
		}

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

	/**
	 * Returns with the rendered / transformed dataset.
	 *
	 * @param bool $assocArray
	 *
	 * @return mixed
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
}