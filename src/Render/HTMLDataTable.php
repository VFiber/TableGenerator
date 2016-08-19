<?php

namespace TableGenerator\Render;

/**
 * Class HTMLDataTable
 *
 * Ugly solution to a nice JS-searchable renderer.
 *
 * @package TableGenerator\Render
 */
class HTMLDataTable extends HTMLTable
{
	protected $individualColumnSearch = false;
	/**
	 * @var bool|array
	 */
	protected $ignoreSearchFields = false;
	const JSON_ENCODE_FLAGS = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;

	public function __construct(
		array $htmlAttributes = [
			'id'    => 'rendered_table',
			'class' => 'rendered_table_class'
		], array $dataTableAttributes = []
	) {

		$htmlAttributes['width'] = '100%';//szándékos, ne ugráljon a táblázat betöltés közben

		parent::__construct($htmlAttributes);
	}

	/**
	 * Allows col-search in the table.
	 *
	 * @param bool  $individualColumnSearch
	 * @param array $doNotAddSearchFor List of individual search-excluded cols
	 *
	 * @return $this
	 */
	public function setIndividualColumnTextSearch($individualColumnSearch, array $doNotAddSearchFor = [])
	{
		//FIXME: do as a select
		$this->individualColumnSearch = (bool)$individualColumnSearch;
		$this->ignoreSearchFields = $doNotAddSearchFor;

		return $this;
	}

	protected function renderHead()
	{
		parent::renderHead();
	}

	protected function renderBody()
	{
		return true;
	}

	protected function renderFoot()
	{
		parent::renderFoot();

		$colIdsToIgnore = [];
		if ($this->ignoreSearchFields)
		{
			$columnIds = $this->dataObject->getColumnIds();
			foreach ($columnIds as $key => $id)
			{
				if (in_array($id, $this->ignoreSearchFields))
				{
					$colIdsToIgnore[] = $key;
				}
			}
		}
		?>
		<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.11/css/jquery.dataTables.css">
		<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.11/js/jquery.dataTables.js"></script>
		<script type="text/javascript">var targetTable = $('#<?=$this->tableHTMLAttributes['id']?>');
			var <?=$this->tableHTMLAttributes['id']?>=
			targetTable.DataTable({
				data: JSON.parse('<?=str_replace('\\', '\\\\',
					json_encode($this->dataObject->getRenderedArray(false), static::JSON_ENCODE_FLAGS))?>'),
				dom: '<"top"if<"clear">>rt<"bottom"<"dt_pagi_container"<"dt_pagination"p>><"dt_information"i><"dt_length"l><"clear">>',
				lengthMenu: [[200, -1, 10, 50, 500, 1000, 2000], [200, 'Mind', 10, 50, 500, 1000, 2000]],
				language: {url: "//cdn.datatables.net/plug-ins/1.10.7/i18n/Hungarian.json"}, stateSave: true
			});
				<?php if ($this->individualColumnSearch) { ?>var headTr = $('#<?=$this->tableHTMLAttributes['id']?> thead tr:last-child');
			var clone = headTr.clone().attr('class', 'search');
			var colNames = ["<?=implode('","', $columnIds)?>"];
			$.each(clone.children(), function (i, v) {
				var html = '';
				if (["<?=implode('","', $colIdsToIgnore)?>"].indexOf('' + i) == -1) {
					html = '<input type="search" class="search i' + i + ' ' + colNames[i] + '" placeholder="' + $(v).text() + ' keresés" />';
				}
				$(v).html(html);
			});
			headTr.after(clone);
			var testi = 0;<?=$this->tableHTMLAttributes['id']?>.columns().every(function () {
				var that = this;
				var inputItem = $('#<?=$this->tableHTMLAttributes['id']?> input.search.i' + testi, this.footer());
				inputItem.val(that.search());
				inputItem.on('keyup change', function () {
					if (that.search() !== this.value) {
						that.search(this.value).draw();
					}
				});
				testi++;
			});
			<?php
			}
			?>

		</script>
		<?php
	}
}