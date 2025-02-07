<?php

namespace Mywebstor\Numerator;

use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Numerator\Generator;
use Mywebstor\Numerator\Client\NumeratorClientTable;
class MwsNumerator extends Numerator
{
   public  $COMPANY;
   public function __construct($company)
   {
     $this->COMPANY = $company;
   }

   public function init()
   {
       if($this->COMPANY <= 0){
           return false;
       }

       $numerator = $this->checkNumerator($this->COMPANY);
       if($numerator['ID'] <= 0){
            $numerator = $this->createNumerator($this->COMPANY);
       }
       \Bitrix\Main\Diag\Debug::writeToFile(print_r($numerator,true),"","_DOC_log.log");
       $numberGenerator = \Bitrix\Main\Numerator\Numerator::load($numerator['NUMERATOR_ID']);
       $number = $numberGenerator->getNext();
        NumeratorClientTable::update($numerator['ID'],[
            'CURRENT_NUM'=>$number,
        ]);


        return $number;

   }
   private function checkNumerator($company)
  {
        \Bitrix\Main\Loader::includeModule('mws.numerator');

        $numerator =  NumeratorClientTable::getList(['filter' => ['COMPANY_ID' => $company]])->fetch();

        return $numerator;


  }
   private function createNumerator($company){
      $config = [
          Numerator::getType() => [
              'name' => 'Клиент '.$company,
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
      $num = NumeratorClientTable::add([
          'COMPANY_ID' => $company,
          'NUMERATOR_ID'=>$result->getId(),
          'CURRENT_NUM'=>0
          ]);
        $numer = NumeratorClientTable::getById($num->getId());

        return $numer;
  }


}