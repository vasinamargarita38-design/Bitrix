<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

class CustomNewsGrouped extends \CBitrixComponent
{
	/**
	 * Валидация и подготовка входных параметров компонента
	 */
	public function onPrepareComponentParams($arParams)
	{
		// Тип инфоблока — обязательная строка
		$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
		
		// ID инфоблока — приводим к числу
		$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
		
		// Количество элементов
		$arParams["NEWS_COUNT"] = intval($arParams["NEWS_COUNT"]) > 0 ? intval($arParams["NEWS_COUNT"]) : 20;

		// Правильное ТЗ-подключение фильтрации по полям через FILTER_NAME (как в news.list)
		$arParams["FILTER_NAME"] = trim($arParams["FILTER_NAME"]);

		$arParams["FILTER_NAME_PART"] = trim($arParams["FILTER_NAME_PART"]);

		return $arParams;
	}

	/**
	 * Проверка подключения модулей и валидности критических параметров
	 */
	protected function checkModules()
	{
		if (!Loader::includeModule("iblock")) {
			throw new \Exception("Модуль Информационных блоков не установлен.");
		}

		if (empty($this->arParams["IBLOCK_TYPE"])) {
			throw new \Exception("Не указан обязательный параметр: Тип инфоблока (IBLOCK_TYPE).");
		}
	}

	/**
	 * Формирование массива фильтрации с учетом динамического внешнего фильтра
	 */
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

		// Если в визуальном редакторе ввели слово для фильтрации названий
		if (!empty($this->arParams["FILTER_NAME_PART"])) {
			$arFilter["%NAME"] = $this->arParams["FILTER_NAME_PART"]; // Знак % означает поиск подстроки (LIKE в SQL)
		}

		return $arFilter;
	}

	/**
	 * Выборка элементов и их группировка по ID инфоблоков
	 */
	protected function getElements()
	{
		$arResult = array("ITEMS" => array());
		$arFilter = $this->prepareFilter();

		// Поля для выборки
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
			
			// ТЗ: Группируем элементы в $arResult['ITEMS'] по ID инфоблоков
			$arResult["ITEMS"][$arItem["IBLOCK_ID"]][] = $arItem;
		}

		return $arResult;
	}

	/**
	 * Точка входа компонента
	 */
	public function executeComponent()
	{
		try {
			// ТЗ: Проверка параметров и выкидывание исключения
			$this->checkModules();

			// Получаем внешний динамический фильтр для генерации уникального кеша
			$arExternalFilter = (!empty($this->arParams["FILTER_NAME"]) && is_array($GLOBALS[$this->arParams["FILTER_NAME"]])) 
				? $GLOBALS[$this->arParams["FILTER_NAME"]] 
				: array();

			// Старт кеширования (зависит от параметров и внешнего фильтра)
			if ($this->startResultCache(false, array($arExternalFilter))) {
				$this->arResult = $this->getElements();
				$this->includeComponentTemplate();
			}
			
		} catch (\Exception $e) {
			// ТЗ: Вывод ошибок через ShowError при возникновении исключений
			$this->abortResultCache();
			ShowError($e->getMessage());
		}
	}
}
