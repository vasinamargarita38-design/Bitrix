<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

// 1. Вызываем сам компонент детальной новости
$APPLICATION->IncludeComponent(
    "bitrix:news.detail",
    "",
    [
        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
        "ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
        "FIELD_CODE" => $arParams["DETAIL_FIELD_CODE"],
        "PROPERTY_CODE" => $arParams["DETAIL_PROPERTY_CODE"],
        "CACHE_TYPE" => "N", // Кэш отключен, чтобы изменения применились сразу
        "CACHE_TIME" => $arParams["CACHE_TIME"],
        "DISPLAY_NAME" => "Y",
        "DISPLAY_DATE" => "Y",
    ],
    $component
);

// 2. Жёстко выводим кнопку сразу ПОСЛЕ компонента
?>
<div style="max-width: 1000px; margin: 30px auto 0; padding: 0 20px; box-sizing: border-box; font-family: sans-serif;">
    <a class="article-card__button" href="/nov/" style="text-decoration: none; text-transform: uppercase; color: #333; font-size: 13px; border-bottom: 1px solid #333; padding-bottom: 2px; font-weight: bold; display: inline-block;">
        Назад к новостям
    </a>
</div>
