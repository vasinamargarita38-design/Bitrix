<?php

class my_complexproperty extends CModule
{
    var $MODULE_ID = "my.complexproperty";
    var $MODULE_VERSION = "1.0.0";
    var $MODULE_VERSION_DATE = "2026-05-23";
    var $MODULE_NAME = "Комплексное HTML-свойство";
    var $MODULE_DESCRIPTION = "Модуль объединяет логику cprop и ufhtml для ИБ и Пользовательских полей";

    function DoInstall()
    {
        RegisterModule($this->MODULE_ID);

        RegisterModuleDependences(
            "iblock", 
            "OnIBlockPropertyBuildList", 
            $this->MODULE_ID, 
            "CComplexIblockProperty", 
            "GetUserTypeDescription"
        );

        RegisterModuleDependences(
            "main", 
            "OnUserTypeBuildList", 
            $this->MODULE_ID, 
            "CComplexUserField", 
            "GetUserTypeDescription"
        );
    }

    function DoUninstall()
    {
        UnRegisterModuleDependences(
            "iblock", 
            "OnIBlockPropertyBuildList", 
            $this->MODULE_ID, 
            "CComplexIblockProperty", 
            "GetUserTypeDescription"
        );

        UnRegisterModuleDependences(
            "main", 
            "OnUserTypeBuildList", 
            $this->MODULE_ID, 
            "CComplexUserField", 
            "GetUserTypeDescription"
        );

        UnRegisterModule($this->MODULE_ID);
    }
}
