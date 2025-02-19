<?php
namespace Mywebstor\Numerator;

use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Numerator\Generator;
use Mywebstor\Numerator\Client\NumeratorAllTable;
class MwsNumeratorAll extends Numerator
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
        if( !$numerator && $numerator['ID'] <= 0){
            $numerator = $this->createNumerator();
        }
        $numberGenerator = \Bitrix\Main\Numerator\Numerator::load($numerator['NUMERATOR_ID']);
        $number = $numberGenerator->getNext();
        NumeratorAllTable::update($numerator['ID'],[
            'CURRENT_NUM'=>$number,
        ]);

        return $number;

    }

    private function checkNumerator($city, $type){
        \Bitrix\Main\Loader::includeModule("mws.numerator");
        $numerator =  NumeratorAllTable::getlist(['filter'=>[
            'CITY_ID'=>$city,
            'CLIENT_TYPE'=>$type
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

      $num = NumeratorAllTable::add([
          'CITY_ID' => $city,
          'NUMERATOR_ID'=>$result->getId(),
          'CLIENT_TYPE'=>$type,
          'CURRENT_NUM'=>0
      ]);

      $numer = NumeratorAllTable::getById($num->getId());

        return $numer;



    }





}