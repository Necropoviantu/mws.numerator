<?php

namespace Mywebstor\Numerator\Client;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\Query\Join;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\UserTable;


class NumeratorClientTable extends DataManager {

public static function getTableName() {
    return 'mws_numerator_client';
}

public static function getMap() {
    return [
        new IntegerField('ID',
            array(
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('MWS_NUMERATOR_CLIENT_ID'),
            )
        ),
        new IntegerField('COMPANY_ID',
            array(
                'required' => true,
                'default_value' => '',
                'title' => Loc::getMessage('MWS_NUMERATOR_CLIENT_COMPANY_ID'),
            )
        ),

        new IntegerField('NUMERATOR_ID',
            array(
                'required' => true,
                'default_value' => '',
                'title' => Loc::getMessage('MWS_NUMERATOR_CLIENT_NUMERATOR_ID'),
            )
        ),
        new IntegerField('CURRENT_NUM',
            array(
                'required' => true,
                'default_value' => '',
                'title' => Loc::getMessage('MWS_NUMERATOR_CLIENT_CURRENT_NUM'),
            )
        ),

    ];
}



}