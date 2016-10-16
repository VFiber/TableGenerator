<?php
namespace TableGenerator;

class DataObjectTest extends \PHPUnit_Framework_TestCase
{
	protected $assocDataArray =
		[
			[
				'col1' => 'foo',
				'col2' => 'bar',
				'col3' => 'bar',
				'col4' => 'bar',
				'col5' => 'bar',
				'col6' => 'bar',
				'col7' => 'bar',
				'col8' => 'bar',
			],
			[
				'col1' => 'foo',
				'col2' => 'bar',
				'col3' => 'bar',
				'col4' => 'bar',
				'col5' => 'bar',
				'col6' => 'bar',
				'col7' => 'bar',
				'col8' => 'bar',
			],
			[
				'col1' => 'foo',
				'col2' => 'bar',
				'col3' => 'bar',
				'col4' => 'bar',
				'col5' => 'bar',
				'col6' => 'bar',
				'col7' => 'bar',
				'col8' => 'bar',
			],
		];

	public function testConfiglessDataObject()
	{
		$do = new DataObject([], $this->assocDataArray);

		$this->assertEquals(3, $do->count());
		$this->assertEquals($this->assocDataArray, $do->getRenderedArray());
		foreach ($do as $index => $row)
		{
			$this->assertEquals($this->assocDataArray[$index], $row);
		}
	}

	public function testInvalidColDefinitions()
	{
		//none of these are valid definitions, shouldn't affect output at all, a standard configless should be triggered
		$cols = [
			''              => 'invalidCol',
			'invalidObject' => new \stdClass(),
			'invalidcol'    => new \stdClass(),
		];

		$do = @new DataObject($cols, $this->assocDataArray);

		foreach ($do as $index => $row)
		{
			$this->assertEquals($this->assocDataArray[$index], $row);
		}
	}

	public function testInvalidDataDefinitions()
	{
		$assocData = [
			'test'  => ['field1' => 'Field'],
			'test4' => ['field1' => 'Field'],
			'test5' => ['field1' => 'Field'],
			'test6' => ['field1' => 'Field'],
			'test7' => ['field1' => 'Field'],
			'test8' => ['field1' => 'Field'],
		];

		//it has to accept, but should clean the 'testX' indices
		$do = @new DataObject([],$assocData);

		foreach ($do as $field => $value)
		{
			$this->assertEquals(['field1' => 'Field'], $value);
		}
	}

	public function testCallableFunctions()
	{
		$cols = [
			'col1'       => function ()
			{
				return 'bar';
			},
			'col2'       => 'header_data_test',
			0            => 'asdf',
			5            => 'asdf2',
			'col3'       => [
				'header_data_with_formatter_function',
				function ($col3_original_value, $originalRowData)
				{
					return implode('', $originalRowData);
				}
			],
			//pure col with an instantiated formatter callable
			'col4'       => [$this, 'makeEmpty'],
			//pure col with a formatter callable
			'col5'       => ['TableGenerator\DataObjectTest', 'staticMakeEmpty'],
			'row_number' => [
				'Row.#',
				function ($empty, $originalRowData, $rowIndex)
				{
					return $rowIndex + 1;
				}
			],
		];

		$do = new DataObject($cols, $this->assocDataArray);
		//longer config but less confusing
		$do->setSkipEmptyParamsInFormatter(false);

		$expectedRow = [
			'col1' => 'bar',
			'col2' => 'bar',
			0      => '',
			5      => '',
			'col3' => implode('', $this->assocDataArray[0]),
			'col4' => '',
			'col5' => '',
		];

		$expectedHeader = [
			'col1'       => 'col1',
			'col2'       => 'header_data_test',
			0            => 'asdf',
			5            => 'asdf2',
			'col3'       => 'header_data_with_formatter_function',
			'col4'       => 'col4',
			'col5'       => 'col5',
			'row_number' => 'Row.#',
		];

		$this->assertEquals($expectedHeader, $do->getHeaderRow());

		foreach ($do as $index => $row)
		{
			$expectedRow['row_number'] = $index + 1;
			$this->assertEquals($expectedRow, $row);
		}
	}

	public function testCallableFunctionsWithShortClosureSignature()
	{
		$cols = [
			'col1'       => function ($fieldVal)
			{
				return $fieldVal . 'bar';
			},
			'col3'       => [
				'header_data_with_formatter_function',
				function (... $data)
				{
					//existing col, first parameter is the field itself, second the row, third is the index
					return implode('', $data[1]);
				}
			],
			//pure col with an instantiated formatter callable
			'col4'       => [$this, 'makeEmpty'],
			//pure col with a formatter callable
			'col5'       => ['TableGenerator\DataObjectTest', 'staticMakeEmpty'],
			'row_number' => [
				'Row.#',
				function (... $data)
				{
					//not existsing col, first is the row, second parameter should be the index
					return $data[1] + 1;
				}
			],
		];

		$do = new DataObject($cols, $this->assocDataArray, true);

		$expectedRow = [
			'col1' => 'foobar',
			'col3' => implode('', $this->assocDataArray[0]),
			'col4' => '',
			'col5' => '',
		];

		$expectedHeader = [
			'col1'       => 'col1',
			'col3'       => 'header_data_with_formatter_function',
			'col4'       => 'col4',
			'col5'       => 'col5',
			'row_number' => 'Row.#',
		];

		$this->assertEquals($expectedHeader, $do->getHeaderRow());

		foreach ($do as $index => $row)
		{
			$expectedRow['row_number'] = $index + 1;
			$this->assertEquals($expectedRow, $row);
		}
	}

	public function makeEmpty()
	{
		return '';
	}

	public static function staticMakeEmpty()
	{
		return '';
	}
}
