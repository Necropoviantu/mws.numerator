<?php
namespace Mywebstor\Numerator;

use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Numerator\Generator;
use Mywebstor\Numerator\Client\NumeratorPhoneTable;
use Bitrix\Main\Config\Option;


class MwsNumeratorPhone extends Numerator
{
    public $CITY;
    public $TYPE;

    public function __construct($CITY , $TYPE)
    {
        $this->CITY = $CITY;
        $this->TYPE = $TYPE;
    }

    public function init()
    {
        if($this->CITY <= 0){
            return false;
        }
        if($this->TYPE <= 0){
            return false;
        }

        $numerator = $this->checkNumerator($this->CITY, $this->TYPE);
//        if( !$numerator && $numerator['ID'] <= 0){
//            $numerator = $this->createNumerator();
//        }
        $numberGenerator = \Bitrix\Main\Numerator\Numerator::load($numerator['NUMERATOR_ID']);
        $number = $numberGenerator->getNext();
        NumeratorPhoneTable::update($numerator['ID'],[
            'CURRENT_NUM'=>$number,
        ]);

        return $number;

    }

    private function checkNumerator($city, $type){
        \Bitrix\Main\Loader::includeModule("mws.numerator");
        \Bitrix\Main\Loader::includeModule("iblock");
        $numerator_all = [
            'city' => Option::get('mws.numerator', 'numerator_all_city', ''),
            'service' => Option::get('mws.numerator', 'numerator_all_service', ''),
            'type' => Option::get('mws.numerator', 'numerator_all_type', ''),
        ];

        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_all['city'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_all['city'],
                'ID' => $city,
            ],
            'select' => [
                "ID",
                "NAME",
                "PREFIX_RU"=>"PREFIKS_RU.VALUE",
                "PREFIX_EN"=>"PREFIKS_EN.VALUE",
                "REALATED_CITY" =>"SVYAZANNYY_GOROD_DLYA_NUMERATOROV.IBLOCK_GENERIC_VALUE"

            ]
        ));
        $cityes =  $res->fetch();
        $cityId = 0;
        if($cityes["REALATED_CITY"]){
            $cityId =$cityes["REALATED_CITY"];
        }else{
            $cityId=  $cityes['ID'];


        }


        $numerator =  NumeratorPhoneTable::getlist(['filter'=>[
            'CITY_ID'=>$cityId,
            'OPS_TYPE'=>$type
        ]])->fetch();
        return $numerator;

    }

    private function createNumerator($city, $type)
    {
        $config = [
            Numerator::getType() => [
                'name' => 'Клиент '.$city,
                'template' => '{NUMBER}',
            ],
            \Bitrix\Main\Numerator\Generator\RandomNumberGenerator::getType() => [
                'length' => '6',
            ],
            \Bitrix\Main\Numerator\Generator\SequentNumberGenerator::getType() => [
                'start' => '1',
                'step' => '1',
            ],
            \Bitrix\Main\Numerator\Generator\PrefixNumberGenerator::getType()  => [
                'prefix' => '',
            ],
        ];
        $numerator = Numerator::create();
        $numerator->setConfig($config);
        /** @var \Bitrix\Main\Entity\AddResult $result **/
        $result = $numerator->save();

        $num = NumeratorPhoneTable::add([
            'CITY_ID' => $city,
            'NUMERATOR_ID'=>$result->getId(),
            'OPS_TYPE'=>$type,
            'CURRENT_NUM'=>0
        ]);

        $numer = NumeratorPhoneTable::getById($num->getId());

        return $numer;



    }





}