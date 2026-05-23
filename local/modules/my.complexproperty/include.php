<?php


use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CComplexIblockProperty
{
    public static function GetUserTypeDescription()
    {
        return array(
            "PROPERTY_TYPE"        => "S",
            "USER_TYPE"            => "COMPLEX_CPROP_HTML",
            "DESCRIPTION"         => Loc::getMessage("MY_PROP_IBLOCK_DESC") ?: "Комплексное свойство (Строка + HTML)",
            "GetPropertyFieldHtml" => array(__CLASS__, "GetPropertyFieldHtml"),
            "ConvertToDB"          => array(__CLASS__, "ConvertToDB"),
            "ConvertFromDB"        => array(__CLASS__, "ConvertFromDB"),
        );
    }

    public static function ConvertToDB($arProperty, $value)
    {
        $strInputName = $value["DESCRIPTION"] ?: ""; 
        
        if (is_array($value["VALUE"])) {
            $value["VALUE"] = serialize($value["VALUE"]);
        }
        return $value;
    }

    public static function ConvertFromDB($arProperty, $value)
    {
        if (!empty($value["VALUE"]) && is_string($value["VALUE"])) {
            $value["VALUE"] = unserialize($value["VALUE"]);
        }
        return $value;
    }

    public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        if (!CModule::IncludeModule("fileman")) {
            return "Ошибка: модуль fileman необходим.";
        }

        $arValues = is_array($value["VALUE"]) ? $value["VALUE"] : array();
        $strInputName = $strHTMLControlName["VALUE"];
        $editorId = 'lhe_cp_' . preg_replace('/[^a-zA-Z0-9]/', '_', $strInputName);
        $htmlContent = $arValues["HTML_VAL"] ?? '';

        ob_start();
        
        echo '<div class="cprop-container" style="border: 1px solid #cedcde; padding: 12px; background: #fff; margin-bottom: 5px; border-radius: 4px;">';
        echo '<div style="margin-bottom: 10px;">';
        echo '<label style="font-weight: bold; display: block; margin-bottom: 4px;">' . (Loc::getMessage("MY_PROP_TITLE_LABEL") ?: "Заголовок (Строка):") . '</label>';
        echo '<input type="text" name="' . $strInputName . '[TEXT_VAL]" value="' . htmlspecialcharsbx($arValues["TEXT_VAL"] ?? '') . '" style="width: 100%; max-width: 500px;">';
        echo '</div>';
        echo '<div style="position: relative;">';
        echo '<label style="font-weight: bold; display: block; margin-bottom: 4px;">' . (Loc::getMessage("MY_PROP_HTML_LABEL") ?: "Описание (HTML редактор):") . '</label>';

        $LHE = new CHTMLEditor;
        $LHE->Show(array(
            'name' => $strInputName . "[HTML_VAL]", 
            'id' => $editorId,
            'inputName' => $strInputName . "[HTML_VAL]", 
            'content' => $htmlContent,
            'width' => '100%',
            'height' => '200',
            'bAllowPhp' => false,
            'limitPhpAccess' => true,
            'bbCode' => false,
            'bResizable' => true,
            'bAutoResize' => false
        ));

        echo '</div>';
        echo '</div>';

        return ob_get_clean();
    }
}


class CComplexUserField
{
    public static function GetUserTypeDescription()
    {
        return array(
            "USER_TYPE_ID" => "uf_complex_html",
            "CLASS_NAME"   => __CLASS__,
            "DESCRIPTION"  => Loc::getMessage("MY_PROP_UF_DESC") ?: "Комплексное UF-поле (Строка + HTML)",
            "BASE_TYPE"    => "string",
        );
    }

    public static function GetDBColumnType($arUserField)
    {
        return "text";
    }

    public static function OnBeforeSave($arUserField, $value)
    {
        $fieldName = $arUserField["FIELD_NAME"];
        
    
        if (isset($_REQUEST[$fieldName]) && is_array($_REQUEST[$fieldName])) {
            $value = $_REQUEST[$fieldName];
        }

        if (is_array($value)) {
            return serialize($value);
        }
        return $value;
    }

    public static function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        if (!CModule::IncludeModule("fileman")) {
            return "Ошибка: требуется модуль fileman.";
        }

        $value = $arHtmlControl["VALUE"];
        if (!empty($value) && is_string($value)) {
            $value = unserialize($value);
        }

        $strInputName = $arHtmlControl["NAME"];
        $editorId = 'lhe_uf_' . preg_replace('/[^a-zA-Z0-9]/', '_', $strInputName);
        $htmlContent = $value["HTML_VAL"] ?? '';

        ob_start();

        echo '<div class="uf-complex-block" style="padding: 12px; background: #f5f9f9; border: 1px solid #d7e2e3; border-radius: 4px;">';
        echo '<div style="margin-bottom: 8px;">';
        echo '<input type="text" name="' . $strInputName . '[TEXT_VAL]" value="' . htmlspecialcharsbx($value["TEXT_VAL"] ?? '') . '" placeholder="Текстовый маркер" style="width: 100%;">';
        echo '</div>';
        echo '<div>';

        $LHE = new CHTMLEditor;
        $LHE->Show(array(
            'name' => $strInputName . "[HTML_VAL]", 
            'id' => $editorId,
            'inputName' => $strInputName . "[HTML_VAL]", 
            'content' => $htmlContent,
            'width' => '100%',
            'height' => '180',
            'bAllowPhp' => false,
            'bbCode' => false
        ));

        echo '</div>';
        echo '</div>';

        return ob_get_clean();
    }
}
