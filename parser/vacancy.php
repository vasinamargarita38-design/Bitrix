<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
Loader::includeModule('iblock');

$iblockId = 4;
$csvPath = __DIR__ . "/vacancy.csv"; 

function getEnumId($iblockId, $propCode, $value) {
    $value = trim($value);
    if (!$value) return false;
    $res = CIBlockPropertyEnum::GetList([], ["IBLOCK_ID" => $iblockId, "CODE" => $propCode, "VALUE" => $value]);
    return ($enum = $res->Fetch()) ? $enum['ID'] : false;
}

if (($handle = fopen($csvPath, "r")) !== FALSE) {
    fgetcsv($handle, 0, ",");

    $el = new CIBlockElement;
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        foreach($data as $key => $val) {
            $current_encoding = mb_detect_encoding($val, ["UTF-8", "Windows-1251"], true);
            if ($current_encoding !== "UTF-8") {
                $data[$key] = mb_convert_encoding($val, "UTF-8", "Windows-1251");
            }
        }

        $propValues = [
            "OFFICE"       => getEnumId($iblockId, "OFFICE", $data[1]),
            "LOCATION"     => getEnumId($iblockId, "LOCATION", $data[2]),
            "SALARY_VALUE" => $data[7],
            "TYPE"         => getEnumId($iblockId, "TYPE", $data[8]),
            "ACTIVITY"     => getEnumId($iblockId, "ACTIVITY", $data[9]),
            "SCHEDULE"     => getEnumId($iblockId, "SCHEDULE", $data[10]),
            "FIELD"        => getEnumId($iblockId, "FIELD", $data[11]),
            "EMAIL"        => $data[12],
            "REQUIRE"      => $data[4],
            "DUTY"         => $data[5],
            "CONDITIONS"   => $data[6],
        ];

        $fields = [
            "IBLOCK_ID"       => $iblockId,
            "NAME"            => $data[3],
            "ACTIVE"          => "Y",
            "PREVIEW_TEXT"    => $data[4], 
            "DETAIL_TEXT"     => $data[5], 
            "PROPERTY_VALUES" => $propValues,
        ];

        if ($id = $el->Add($fields)) {
            echo "Добавлено: $id - " . $data[3] . "<br>";
        } else {
            echo "Ошибка: " . $el->LAST_ERROR . "<br>";
        }
    }
    fclose($handle);
}
echo "Загрузка завершена!";

