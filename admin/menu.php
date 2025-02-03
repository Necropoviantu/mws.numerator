<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

$aMenu[] = [
    "parent_menu" => "global_menu_custom",
    "sort" => 1900,
    "text" => Loc::getMessage('MWS_NUMERATOE_MODULE_MENU'),
    "title" => Loc::getMessage('MWS_NUMERATOR_MODULE_MENU_TITLE'),
    "url" => BX_ROOT . '/admin/mwsNumeratorSettings.php?lang=' . LANGUAGE_ID,
    "icon" => "util_menu_icon",
    "page_icon" => "util_page_icon"
];


return $aMenu;