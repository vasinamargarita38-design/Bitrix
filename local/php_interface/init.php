<?php
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    "dev.site",
    [
        "Dev\\Site\\Agents\\Iblock" => "lib/Agents/Iblock.php",
    ]
);

if (Loader::includeModule('dev.site')) {
    $eventManager = \Bitrix\Main\EventManager::getInstance();
    $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', ['Dev\\Site\\Handlers\\Iblock', 'addLog']);
    $eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', ['Dev\\Site\\Handlers\\Iblock', 'addLog']);
}

