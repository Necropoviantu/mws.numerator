<?php

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

class  mws_numerator extends CModule
{

    public $MODULE_ID = 'mws.numerator';
    public $errors = "";
    static $events = array(
        array(
            "FROM_MODULE" => "rest",
            "FROM_EVENT" => "OnRestServiceBuildDescription",
            "TO_CLASS" => "MwsNumeratorRest",
            "TO_FUNCTION" => "OnRestServiceBuildDescription",
            "VERSION" => "1"
        ),
        array(
            "FROM_MODULE" => "documentgenerator",
            "FROM_EVENT" => "onBeforeProcessDocument",
            "TO_CLASS" => "MwsHandlerDocs",
            "TO_FUNCTION" => "_onBeforeProcessDocument",
            "VERSION" => "2"
        ),
        array(
            "FROM_MODULE" => "crm",
            "FROM_EVENT" => "onCrmDocumentGeneratorDocumentDelete",
            "TO_CLASS" => "MwsHandlerDocs",
            "TO_FUNCTION" => "_OnAfterDelete",
            "VERSION" => "2"
        ),
    );

    public function __construct(){
        $this->MODULE_GROUP_RIGHTS = "N";
        $this->MODULE_NAME = Loc::getMessage("MWS_NUMERATOR_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("MWS_NUMERATOR_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("MWS_NUMERATOR_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("MWS_NUMERATOR_PARTNER_URI");

        $arModuleVersion = array();
        include __DIR__ . "/version.php";
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
    }

    //инсталяционный блок
    public function DoInstall()
    {
        $this->InstallDB();
        $this->installFiles();
        $this->InstallEvents();
       // $this->installHlblock();
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        return true;
    }
    public function InstallDB()
    {
        global $DB, $APPLICATION;
        $this->errors = $DB->RunSQLBatch(__DIR__ . '/local/db/install.sql');
        if (is_array($this->errors)) {
            $APPLICATION->ThrowException(implode('<br />', $this->errors));
            return false;
        }
        return true;
    }

    public function installFiles()
    {

        CopyDirFiles(
            __DIR__ . "/local/admin/",
            \Bitrix\Main\Application::getDocumentRoot() . "/bitrix/admin/",
            true,
            true
        );

        return true;
    }

    public function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        foreach (static::$events as $event)
            switch ($event["VERSION"]) {
                case "2":
                    $eventManager->registerEventHandler($event["FROM_MODULE"], $event["FROM_EVENT"], $this->MODULE_ID, $event["TO_CLASS"], $event["TO_FUNCTION"]);
                    break;
                case "1":
                default:
                    $eventManager->registerEventHandlerCompatible($event["FROM_MODULE"], $event["FROM_EVENT"], $this->MODULE_ID, $event["TO_CLASS"], $event["TO_FUNCTION"]);
                    break;
            }
        return true;
    }
    // деинсталяциионный блок
    public function DoUninstall()
    {
        global $APPLICATION, $USER, $DB, $step;

        $step = intval($step);
        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("MWS_SED_FDOC_MODULE_UNINSTALL_TITLE", array("#MODULE_NAME#" => $this->MODULE_NAME)), __DIR__ . "/unstep1.php");
        } elseif ($step === 2) {
            if (!array_key_exists('savedata', $_REQUEST) || $_REQUEST['savedata'] != 'Y') {
                $this->UnInstallDB();
            }
        }
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        \Bitrix\Main\Config\Option::delete("mws.numerator");
        return true;
    }
    public function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/admin/mwsNumeratorSettings.php");
        return true;
    }
    public function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        foreach (static::$events as $event)
            $eventManager->unRegisterEventHandler($event["FROM_MODULE"], $event["FROM_EVENT"], $this->MODULE_ID, $event["TO_CLASS"], $event["TO_FUNCTION"]);
        return true;
    }

    //TODO Удаляем таблицу
    public function UnInstallDB()
    {
        /** @var \CMain $APPLICATION */
        /** @var \CDatabase $DB */
        global $DB, $APPLICATION;
        $this->errors = $DB->RunSQLBatch(__DIR__ . '/local/db/uninstall.sql');
        if (is_array($this->errors)) {
            throw new \Exception(implode('<br />', $this->errors));
            return false;
        }


        return true;
    }
}