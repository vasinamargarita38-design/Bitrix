<?php

namespace Dev\Site\Agents;

class Iblock
{
        public static function clearOldLogs()
    {
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $res = \CIBlock::GetList([], ['CODE' => 'LOG']);
            $logIblockId = ($iblock = $res->Fetch()) ? $iblock['ID'] : false;

            if ($logIblockId) {
                // 1. Выбираем ВСЕ элементы, от новых к старым
                $resElements = \CIBlockElement::GetList(
                    ['ID' => 'DESC'],
                    ['IBLOCK_ID' => $logIblockId],
                    false,
                    false,
                    ['ID']
                );

                $allIds = [];
                while ($el = $resElements->Fetch()) {
                    $allIds[] = $el['ID'];
                }

                // 2. Если элементов больше 10, отрезаем первые 10, а остальные удаляем
                if (count($allIds) > 10) {
                    $deleteIds = array_slice($allIds, 10);
                    foreach ($deleteIds as $id) {
                        \CIBlockElement::Delete($id);
                    }
                }
            }
        }
        return "\\Dev\\Site\\Agents\\Iblock::clearOldLogs();";
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Only\Site\Helpers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
