<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Highloadblock\HighloadBlockTable;

const IBLOCK_CARS_ID = 6; 
const IBLOCK_BOOKINGS_ID = 7;
const HL_COMFORT_ID = 4;

try {
    if (!Loader::includeModule('iblock')) {
        throw new \Bitrix\Main\SystemException('Модуль Информационных blocks не установлен.');
    }

    global $USER;
    if (!$USER->IsAuthorized()) {
        throw new \Bitrix\Main\SystemException('Пользователь не авторизован.');
    }

    $request = Context::getCurrent()->getRequest();
    $dateFromRaw = $request->getQuery('from'); 
    $dateToRaw = $request->getQuery('to');

    if (empty($dateFromRaw) || empty($dateToRaw)) {
        throw new \Bitrix\Main\SystemException('Не указан период поездки (параметры from и to).');
    }

    $dateFromClean = str_replace('T', ' ', $dateFromRaw);
    $dateToClean = str_replace('T', ' ', $dateToRaw);
    if (strlen($dateFromClean) === 16) $dateFromClean .= ':00';
    if (strlen($dateToClean) === 16) $dateToClean .= ':00';

    $dateFrom = DateTime::createFromPhp(new \DateTime($dateFromClean));
    $dateTo = DateTime::createFromPhp(new \DateTime($dateToClean));

    if ($dateFrom->getTimestamp() >= $dateTo->getTimestamp()) {
        throw new \Bitrix\Main\SystemException('Дата начала поездки должна быть меньше даты окончания.');
    }

    $userData = UserTable::getList([
        'select' => ['UF_ACCESSIBLE_COMFORT'],
        'filter' => ['=ID' => $USER->GetID()]
    ])->fetch();

    $allowedComfortIds = $userData['UF_ACCESSIBLE_COMFORT'] ?? [];
    $allowedComfortLevels = [];

    if (!empty($allowedComfortIds) && Loader::includeModule('highloadblock')) {
        $hlblock = HighloadBlockTable::getById(HL_COMFORT_ID)->fetch();
        if ($hlblock) {
            $entity = HighloadBlockTable::compileEntity($hlblock);
            $entityDataClass = $entity->getDataClass();

            $hlDb = $entityDataClass::getList([
                'select' => ['UF_XML_ID'],
                'filter' => ['=ID' => $allowedComfortIds]
            ]);
            while ($hlRow = $hlDb->fetch()) {
                if (!empty($hlRow['UF_XML_ID'])) {
                    $allowedComfortLevels[] = $hlRow['UF_XML_ID'];
                }
            }
        }
    }

    $this->arResult['ITEMS'] = [];

    if (!empty($allowedComfortLevels)) {
        $bookedCarIds = [];
        $bookingsDb = \CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID" => IBLOCK_BOOKINGS_ID,
                "ACTIVE" => "Y",
                "<=PROPERTY_DATE_FROM" => $dateTo->toString(),
                ">=PROPERTY_DATE_TO" => $dateFrom->toString(),
            ],
            false,
            false,
            ["ID", "PROPERTY_CAR"]
        );

        while ($booking = $bookingsDb->Fetch()) {
            if (!empty($booking['PROPERTY_CAR_VALUE'])) {
                $bookedCarIds[] = $booking['PROPERTY_CAR_VALUE'];
            }
        }
        $bookedCarIds = array_unique($bookedCarIds);

        $arFilter = [
            "IBLOCK_ID" => IBLOCK_CARS_ID,
            "ACTIVE" => "Y",
            "PROPERTY_COMFORT_LEVEL" => $allowedComfortLevels
        ];

        if (!empty($bookedCarIds)) {
            $arFilter["!ID"] = $bookedCarIds;
        }

        $carsDb = \CIBlockElement::GetList(
            ["SORT" => "ASC", "NAME" => "ASC"],
            $arFilter,
            false,
            false,
            ["ID", "NAME", "PROPERTY_COMFORT_LEVEL", "PROPERTY_DRIVER"]
        );

        $driverIds = [];

        while ($car = $carsDb->Fetch()) {
            $this->arResult['ITEMS'][$car['ID']] = [
                'ID' => $car['ID'],
                'MODEL' => $car['NAME'],
                'COMFORT_LEVEL' => $car['PROPERTY_COMFORT_LEVEL_VALUE'],
                'DRIVER_ID' => $car['PROPERTY_DRIVER_VALUE'],
                'DRIVER_NAME' => '' 
            ];
            if (!empty($car['PROPERTY_DRIVER_VALUE'])) {
                $driverIds[] = $car['PROPERTY_DRIVER_VALUE'];
            }
        }

        if (!empty($driverIds)) {
            $driversDb = UserTable::getList([
                'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN'],
                'filter' => ['=ID' => array_unique($driverIds)]
            ]);
            while ($driver = $driversDb->fetch()) {
                $fullName = trim(sprintf('%s %s %s', $driver['LAST_NAME'], $driver['NAME'], $driver['SECOND_NAME']));
                foreach ($this->arResult['ITEMS'] as &$item) {
                    if ($item['DRIVER_ID'] == $driver['ID']) {
                        $item['DRIVER_NAME'] = $fullName ?: $driver['LOGIN'];
                    }
                }
                unset($item);
            }
        }
    }

} catch (\Exception $e) {
    $this->arResult['ERROR'] = $e->getMessage();
}

return $this->arResult;
