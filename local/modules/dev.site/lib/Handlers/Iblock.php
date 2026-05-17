<?php

namespace Dev\Site\Handlers; 

use Bitrix\Main\Type\DateTime;
use CIBlock;
use CIBlockElement;
use CIBlockSection;

class Iblock
{
    private static $isLogging = false;

        public static function addLog($arFields)
    {
        if (self::$isLogging) {
            return;
        }

        $res = \CIBlock::GetList([], ['CODE' => 'LOG']);
        $logIblockId = ($iblock = $res->Fetch()) ? $iblock['ID'] : false;

        if (!$logIblockId || $arFields['IBLOCK_ID'] == $logIblockId) {
            return;
        }

        if (isset($arFields['RESULT']) && !$arFields['RESULT']) {
            return;
        }

        self::$isLogging = true;

        $iblockRes = \CIBlock::GetByID($arFields['IBLOCK_ID']);
        if (!$iblock = $iblockRes->Fetch()) {
            self::$isLogging = false;
            return;
        }

        $sectionRes = \CIBlockSection::GetList([], ['IBLOCK_ID' => $logIblockId, 'CODE' => $iblock['CODE']], false, ['ID']);
        if ($section = $sectionRes->Fetch()) {
            $sectionId = $section['ID'];
        } else {
            $bs = new \CIBlockSection;
            $sectionId = $bs->Add([
                'IBLOCK_ID' => $logIblockId,
                'NAME' => $iblock['NAME'],
                'CODE' => $iblock['CODE'],
                'ACTIVE' => 'Y'
            ]);
        }

        if (!$sectionId) {
            self::$isLogging = false;
            return;
        }

        $sectionChain = '';
        if (!empty($arFields['IBLOCK_SECTION_ID'])) {
            $nav = \CIBlockSection::GetNavChain($arFields['IBLOCK_ID'], $arFields['IBLOCK_SECTION_ID'], ['NAME']);
            $chain = [];
            while ($section = $nav->Fetch()) {
                $chain[] = $section['NAME'];
            }
            $sectionChain = implode(' -> ', $chain);
        }

        $previewText = $iblock['NAME'] . ' -> ' . ($sectionChain ? $sectionChain . ' -> ' : '') . $arFields['NAME'];

        $el = new \CIBlockElement;
        $el->Add([
            'IBLOCK_ID' => $logIblockId,
            'IBLOCK_SECTION_ID' => $sectionId,
            'NAME' => $arFields['ID'],
            'ACTIVE' => 'Y',
            'ACTIVE_FROM' => new \Bitrix\Main\Type\DateTime(), // Полный путь к классу даты
            'PREVIEW_TEXT' => $previewText,
            'PREVIEW_TEXT_TYPE' => 'text'
        ]);

        self::$isLogging = false;
    }
    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Only\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }
}
