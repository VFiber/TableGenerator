<?php
namespace TableGenerator\Render;

use TableGenerator\DataObject;
use TableGenerator\TableableDataSourceInterface;

class BaseRender implements \TableGenerator\RenderInterface
{
	/**
	 * @var null|DataObject
	 */
	protected $dataObject = null;

	public function setDataObject(TableableDataSourceInterface $dataObject)
	{
		$this->dataObject = $dataObject;

		return $this;
	}

	public function renderTable()
	{
		trigger_error('woot?! renderTable method not implemented.');
	}
}