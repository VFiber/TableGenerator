<?php
namespace TableGenerator;

class DataObjectTest extends \PHPUnit_Framework_TestCase
{
	protected $assocArray =
		[
		['col1' => 'foo', 'col2' => 'bar', 'col3' => 'bar', 'col4' => 'bar', 'col5' => 'bar', 'col6' => 'bar', 'col7' => 'bar', 'col8' => 'bar',],
		['col1' => 'foo', 'col2' => 'bar', 'col3' => 'bar', 'col4' => 'bar', 'col5' => 'bar', 'col6' => 'bar', 'col7' => 'bar', 'col8' => 'bar',],
		['col1' => 'foo', 'col2' => 'bar', 'col3' => 'bar', 'col4' => 'bar', 'col5' => 'bar', 'col6' => 'bar', 'col7' => 'bar', 'col8' => 'bar',],
		];

	public function testConfiglessDataObject()
	{
		$do = new DataObject([], $this->assocArray);

		$this->assertEquals(3, $do->count());
		$this->assertEquals($this->assocArray, $do->getRenderedArray());
		foreach ($do as $index => $row)
		{
			$this->assertEquals($this->assocArray[$index], $row);
		}
	}

	public function testCallableFunction()
	{
		$cols = [
			'col1' => function ($a, $b)
			{
				return 'bar';
			},
			'col2' => function ($a, $b)
			{
				return 'foo';
			},
			'col3' => function ($a, $b)
			{
				return implode('', $b);
			},
		];

		$do = new DataObject($cols, $this->assocArray);

		$expectedRow = [
			'col1' => 'bar',
			'col2' => 'foo',
			'col3' => implode('', $this->assocArray[0])
		];

		foreach ($do as $index => $row)
		{
			$this->assertEquals($expectedRow, $row);
		}
	}

	protected function makeEmpty($a, $b)
	{
		return '';
	}
}
