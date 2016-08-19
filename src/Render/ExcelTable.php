<?php

namespace TableGenerator\Render;

/**
 * Renders a simple table.
 *
 * Class ExcelTable
 * @package TableGenerator\Render
 */
class ExcelTable extends BaseRender
{
	/**
	 * @var \PHPExcel;
	 */
	protected $pExcel = null;
	/**
	 * @var null
	 */
	protected $sheet = null;
	/**
	 * @var null|\PHPExcel_Worksheet_RowIterator
	 */
	protected $rowIterator = null;

	protected $fileTypes =
		[
			'Excel5' => 'application/vnd.ms-excel',
			'Excel2007' => 'application/vnd.ms-excel',
			'PDF' => 'application/pdf'
		];

	/**
	 * @var bool
	 */
	protected $autoSizeColumns = true;

	/**
	 * Ha valami oknál fogva nagyon elszállna egy sor mérete, itt célszerű kikapcsolni
	 * @param boolean $autoSizeColumns
	 */
	public function setAutoSizeColumns($autoSizeColumns)
	{
		$this->autoSizeColumns = $autoSizeColumns;
	}

	public function fileProperties(&$props)
	{
		$props = $this->pExcel->getProperties();
	}

	/**
	 *
	 * @param \PHPExcel|null $pe Ha meglévő PHPExcel-hez szeretnénk hozzáfűzni
	 * @param string $worksheetName
	 */
	public function __construct($worksheetName = '', \PHPExcel $pe = null)
	{
		if (!empty($pe) && is_a($pe, 'PHPExcel'))
		{
			$this->pExcel = $pe;
			//new sheet
			$this->sheet = $this->pExcel->createSheet();
		}
		else
		{
			$this->pExcel = new \PHPExcel();
			//default sheet
			$this->sheet = $this->pExcel->getActiveSheet();
		}

		if (!empty($worksheetName) && is_string($worksheetName))
		{
			$this->sheet->setTitle($worksheetName);
		}
		$this->rowIterator = $this->sheet->getRowIterator();
	}

	/**
	 * @param string $fileName
	 * @param string $saveFormat : 'Excel5', 'Excel2007','PDF'
	 * @param bool $inline Content-Disposition: inline / attached?
	 * @param bool $setHeaders sets
	 * @return bool
	 * @throws \PHPExcel_Reader_Exception
	 */
	public function toScreen($fileName = 'export.xls', $saveFormat = 'Excel5', $inline = false, $setHeaders = true)
	{
		if (!isset($this->fileTypes[$saveFormat]))
		{
			trigger_error('Unknown file type: ' . $saveFormat . ' should be: Excel5, Excel2007 or PDF ');
			return false;
		}

		if ($setHeaders)
		{
			header('Content-type: ' . $this->fileTypes[$saveFormat]);
			if ($inline)
			{
				header('Content-Disposition: inline; filename="' . $fileName . '"');
			}
			else
			{
				header('Content-Disposition: attachment; filename="' . $fileName . '"');
			}
		}

		$this->renderTable();
		//FIXME: exceptiont elkapni
		/**
		 * @var \PHPExcel_Writer_Excel5;
		 */
		\PHPExcel_IOFactory::createWriter($this->pExcel, $saveFormat)
			->save('php://output');

		return true;
	}

	public function toFile($fileName = '', $saveFormat = 'Excel5')
	{
		$this->renderTable();

		try
		{
			/**
			 * @var \PHPExcel_Writer_Excel5;
			 */
			$s = \PHPExcel_IOFactory::createWriter($this->pExcel, $saveFormat);
			$s->save($fileName);
		}
		catch (\PHPExcel_Reader_Exception $e)
		{
			trigger_error($e->getMessage());
			return false;
		}

		return true;
	}

	protected function renderHead()
	{
		$this->writeRow($this->rowIterator->current()->getCellIterator(), $this->dataObject->getHeaderRow());
		$this->rowIterator->next();
	}

	protected function renderBody()
	{
		foreach ($this->dataObject as $key => $row)
		{
			$this->writeRow($this->rowIterator->current()->getCellIterator(), $row);
			$this->rowIterator->next();
		}
	}

	protected function applyStyle()
	{
		//.... lehetőség a felüldefiniálásra, a tábla renderelése után hívódik meg, így már meglévő adatokkal tudunk dolgozni
		foreach (range('A', $this->sheet->getHighestColumn()) as $columnID)
		{
			$this->sheet->getColumnDimension($columnID)->setAutoSize(true);
		}
	}

	public function renderTable()
	{
		if (empty($this->dataObject))
		{
			return false;
		}

		$this->renderHead();
		$this->renderBody();
		$this->applyStyle();

		return true;
	}

	private function writeRow(\PHPExcel_Worksheet_CellIterator $iterator, array $data)
	{
		$iterator->setIterateOnlyExistingCells(false);

		foreach ($data as $header)
		{
			if (is_array($header))
			{
				$iterator->current()->setValueExplicit($header[0], $header[1]);
			}
			else
			{
				//bizonyos számokat szeret megenni az excel kérdés nélkül, így:
				$iterator->current()->setValueExplicit($header,\PHPExcel_Cell_DataType::TYPE_STRING);
			}

			$iterator->next();
		}
	}
}