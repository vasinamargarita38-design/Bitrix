<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

class CustomNewsGrouped extends \CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
		$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
		$arParams["NEWS_COUNT"] = intval($arParams["NEWS_COUNT"]) > 0 ? intval($arParams["NEWS_COUNT"]) : 20;
		$arParams["FILTER_NAME"] = trim($arParams["FILTER_NAME"]);
		$arParams["FILTER_NAME_PART"] = trim($arParams["FILTER_NAME_PART"]);

		return $arParams;
	}

	protected function checkModules()
	{
		if (!Loader::includeModule("iblock")) {
			throw new \Exception("Модуль Информационных блоков не установлен.");
		}

		if (empty($this->arParams["IBLOCK_TYPE"])) {
			throw new \Exception("Не указан обязательный параметр: Тип инфоблока (IBLOCK_TYPE).");
		}
	}

	protected function prepareFilter()
	{
		$arFilter = array(
			"IBLOCK_TYPE" => $this->arParams["IBLOCK_TYPE"],
			"ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y"
		);

		if ($this->arParams["IBLOCK_ID"] > 0) {
			$arFilter["IBLOCK_ID"] = $this->arParams["IBLOCK_ID"];
		}

		if (!empty($this->arParams["FILTER_NAME_PART"])) {
			$arFilter["%NAME"] = $this->arParams["FILTER_NAME_PART"];
		}

		return $arFilter;
	}

	protected function getElements()
	{
		$arResult = array("ITEMS" => array());
		$arFilter = $this->prepareFilter();

		$arSelect = array(
			"ID", 
			"IBLOCK_ID", 
			"NAME", 
			"PREVIEW_TEXT", 
			"DETAIL_PAGE_URL"
		);

		$dbElements = \CIBlockElement::GetList(
			array("SORT" => "ASC", "ACTIVE_FROM" => "DESC"),
			$arFilter,
			false,
			array("nTopCount" => $this->arParams["NEWS_COUNT"]),
			$arSelect
		);

		while ($obElement = $dbElements->GetNextElement()) {
			$arItem = $obElement->GetFields();
			$arResult["ITEMS"][$arItem["IBLOCK_ID"]][] = $arItem;
		}

		return $arResult;
	}

	public function executeComponent()
	{
		try {
			$this->checkModules();

			$arExternalFilter = (!empty($this->arParams["FILTER_NAME"]) && is_array($GLOBALS[$this->arParams["FILTER_NAME"]])) 
				? $GLOBALS[$this->arParams["FILTER_NAME"]] 
				: array();

			if ($this->startResultCache(false, array($arExternalFilter))) {
				$this->arResult = $this->getElements();
				$this->includeComponentTemplate();
			}
			
		} catch (\Exception $e) {
			$this->abortResultCache();
			ShowError($e->getMessage());
		}
	}
}
