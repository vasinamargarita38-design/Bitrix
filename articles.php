<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); ?>
<?$APPLICATION->IncludeComponent(
    "bitrix:news.list",
    "articles_list", 
    Array(
        "IBLOCK_ID" => "5",
        "NEWS_COUNT" => "6",
        "PROPERTY_CODE" => array(""),
        "SET_TITLE" => "Y",
    )
);?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
