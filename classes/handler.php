<?php

use Bitrix\Main\Numerator\Numerator;
use Mywebstor\Numerator\MwsNumerator;
use Mywebstor\Numerator\Client\NumeratorClientTable;
use Bitrix\Main\SystemException;


//Осталось завести шаблоны и условия
class MwsHandlerDocs {
    static function _onBeforeProcessDocument($event)
    {
        \Bitrix\Main\Diag\Debug::writeToFile(print_r("Создание",true),"","_DOC_log.log");
        $application = \Bitrix\Main\Application::getInstance();
        Bitrix\Main\Loader::includeModule('documentgenerator');
        Bitrix\Main\Loader::includeModule('mws.numerator');
        Bitrix\Main\Loader::includeModule('crm');



        $document = $event->getParameter('document');
        $template = $document->getTemplate();
        $templateID  =$template->ID;
        if($template && $template->MODULE_ID == 'crm'){

            $provider = $document->getProvider();
            $ownerType = $provider->getCrmOwnerType();
            if($ownerType == 2){
                $dealId = $provider->getSource();

                $DealRes = Bitrix\Crm\DealTable::query()
                    ->where('ID',$dealId)
                    ->setSelect(array("ID","CATEGORY_ID",'COMPANY_ID'))
                    ->setLimit(1)
                    ->fetchObject();

                if($DealRes){

                    \Bitrix\Main\Diag\Debug::writeToFile(print_r($DealRes->get('COMPANY_ID'),true),"","_DOC_log.log");
                    if(!$DealRes->get('COMPANY_ID')){

                        throw new Bitrix\Main\SystemException('Не указана компания  ошибка создания документа');
                    }

                    $num = new \Mywebstor\Numerator\MwsNumerator($DealRes->get('COMPANY_ID'));

                    $document->setValues(['DocumentNumber' => $num->init()]);



                }
            }
        }



    }
    static function _OnAfterDelete($fields){

        \Bitrix\Main\Diag\Debug::writeToFile(print_r( 'Удаление',true),"","_DOC_log.log");
        global $APPLICATION;
        Bitrix\Main\Loader::includeModule('documentgenerator');
        Bitrix\Main\Loader::includeModule('mws.numerator');
        Bitrix\Main\Loader::includeModule('crm');
        $document = $fields->getParameter('document');


        $template = $document->getTemplate();
        if($template && $template->MODULE_ID == 'crm'){
            $provider = $document->getProvider();
            $ownerType = $provider->getCrmOwnerType();
            if($ownerType == 2){
                $dealId = $provider->getSource();
                $DealRes = Bitrix\Crm\DealTable::query()
                    ->where('ID',$dealId)
                    ->setSelect(array("ID","CATEGORY_ID",'COMPANY_ID'))
                    ->setLimit(1)
                    ->fetchObject();


                if($DealRes){

                    if(!$DealRes->get('COMPANY_ID')){
                        throw new Bitrix\Main\SystemException('Не указана компания  ошибка создания документа');
                    }
                    $getNumerator = Mywebstor\Numerator\Client\NumeratorClientTable::query()
                        ->where('COMPANY_ID',$DealRes->get('COMPANY_ID'))
                        ->setSelect(array("*"))
                        ->setLimit(1)
                        ->fetchObject();
                    $getAllDealsCompany = \Bitrix\Crm\DealTable::getList([
                        "filter"=>['COMPANY_ID'=>$getNumerator->get('COMPANY_ID')],
                        'select'=>['ID']
                    ]);
                    $dealIds =[];

                    while ($deal = $getAllDealsCompany->fetch()) {
                        $dealIds[] = $deal['ID'];
                    }
                    //Забираем все документы созданные по сделке с темплейтами

                    $docs = \Bitrix\DocumentGenerator\Model\DocumentTable::getlist([
                        'order'=>['ID'=>'DESC'],
                        'filter'=>[
                            //TEMPLATE_ID=>[]
                            '=PROVIDER' => mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class),
                            'VALUE'=>$dealIds,
                        ],
                        'select'=>['ID','NUMBER','TEMPLATE_ID']
                    ])->fetchAll();
                    \Bitrix\Main\Diag\Debug::writeToFile(print_r(   $docs,true),"","_DOC_log.log");

                    if($docs[0]['ID'] == $document->ID){

                        $numerator = Numerator::load($getNumerator->get("NUMERATOR_ID"));
                        $numerator->setNextSequentialNumber($docs[0]['NUMBER']);
                        $num = NumeratorClientTable::update( $getNumerator->get('ID') ,[
                            'CURRENT_NUM'=>$docs[0]['NUMBER']
                        ]);

                    }

                }

            }

        }









    }
    //Сложный нумератор
    static function setNumberOnStage($fields)
    {
        Bitrix\Main\Loader::includeModule('crm');
        $arErrorsTmp=[];
        $entityTypeID = \CCrmOwnerType::Deal;
        $factory = Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeID);
        $item = $factory->getItem($fields['ID']);
        $cat = $item->get('CATEGORY_ID'); //воронка
        if($cat != 1) return;
        $stage = $item->get('STAGE_ID');//стадия
        if($stage != 'C1:PREPARATION') return;
        $abonType = $item->get('UF_CRM_64EC912D44388'); //фл-юл
        if(!$abonType)return;
        $service = $item->get('UF_CRM_1710254867');//Услуга
        if(!$service)return;
        $sity  = $item->get('UF_CRM_64EDAFBE9651B');//город
        if(!$sity)return;

    }


}

