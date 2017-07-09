<?php

namespace TableGenerator\Render;

use TableGenerator\DataObject;

class HTMLTable extends BaseRender
{
	protected $tableId = 'html_table';
	protected $tableHTMLClass = 'html_table';
	protected $tableHTMLAttributes = [];

	public $renderHead = true;
	public $renderFoot = true;

	protected $stripeRowClasses = [];

	/**
	 * HTMLTable constructor.
	 *
	 * @param array                              $htmlAttributes ['attribute' => 'value'] generates < table attribute='value'>
	 * @param \TableGenerator\DataObject|null $data
	 */
	public function __construct(array $htmlAttributes = ['id' =>'rendered_table', 'class' => 'rendered_table_class'], DataObject $data = null)
	{
		$this->tableHTMLAttributes = $htmlAttributes;

		if ($data != null)
		{
			$this->setDataObject($data);
		}
	}

	/**
	 * @param array $classList Pld: ['one', 'two', 'three'] and it generates <tr class="one">...</tr><tr class="two">...</tr><tr class="three">...</tr>
	 */
	public function setStripedRowClasses(array $classList = [])
	{
		$this->stripeRowClasses = $classList;
	}

	public function toScreen()
	{
		return $this->renderTable();
	}

	protected function renderHead()
	{
		$attributes = '';

		if (!empty($this->tableHTMLAttributes)) {
			$strings = [];
			foreach ($this->tableHTMLAttributes as $atr => $value)
			{
				$strings[] = $atr.'="'.$value.'"';
			}
			$attributes .= ' '.implode(' ',$strings).' ';
		}

		echo '<table' . $attributes . '>';
		if ($this->renderHead) {
			echo '<thead>';
			echo '<tr><th>',implode('</th><th>',$this->dataObject->getHeaderRow()),'</th></tr>';
			echo '</thead>';
		}
	}

	protected function renderFoot()
	{
		if ($this->renderFoot)
		{
			echo '<tfoot>';
			echo '<tr><th>',implode('</th><th>',$this->dataObject->getHeaderRow()),'</th></tr>';
			echo '</tfoot>';
		}

		echo '</table>';
	}

	protected function renderBody()
	{
		foreach ($this->dataObject as $key => $row)
		{
			echo '<tr>';
			foreach ($row as $class => $cellContent)
			{
				$c = 'class="c_' . $class . '"';
				echo '<td ' . $c . '>' . $cellContent . '</td>';
			}
			echo '</tr>';
		}
	}

	public function renderTable()
	{
		if ($this->dataObject === null)
		{
			return false;
		}

		$this->renderHead();
		$this->renderBody();
		$this->renderFoot();

		return true;
	}

}