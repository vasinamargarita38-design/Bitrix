<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arTypesEx = CIBlockParameters::GetIBlockTypes();

$arIBlocks = array(0 => "--- All IBlocks ---");
$dbIblock = CIBlock::GetList(
	array("SORT" => "ASC"),
	array("TYPE" => ($arCurrentValues["IBLOCK_TYPE"] ? $arCurrentValues["IBLOCK_TYPE"] : ""))
);
while($arRes = $dbIblock->Fetch())
{
	$arIBlocks[$arRes["ID"]] = "[".$arRes["ID"]."] ".$arRes["NAME"];
}

$arComponentParameters = array(
	"GROUPS" => array(
		"NEWS_GROUP_SETTINGS" => array(
			"NAME" => "Main Settings"
		),
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "NEWS_GROUP_SETTINGS",
			"NAME" => "IBlock Type",
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "NEWS_GROUP_SETTINGS",
			"NAME" => "IBlock ID",
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '0',
			"ADDITIONAL_VALUES" => "Y",
		),
		"FILTER_NAME_PART" => array(
			"PARENT" => "NEWS_GROUP_SETTINGS",
			"NAME" => "Filter: Word in Title",
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"NEWS_COUNT" => array(
			"PARENT" => "NEWS_GROUP_SETTINGS",
			"NAME" => "Elements Count",
			"TYPE" => "STRING",
			"DEFAULT" => "20",
		),
		"CACHE_TIME"  =>  array("DEFAULT"=>3600),
	),
);
