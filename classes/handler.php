<?php

use Mywebstor\Numerator\MwsNumerator;
class MwsHandlerDocs {
    static function _onBeforeProcessDocument($event)
    {
        \Bitrix\Main\Diag\Debug::writeToFile(print_r("Создание",true),"","_DOC_log.log");
        global $APPLICATION;
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
                    if(!$DealRes->get('COMPANY_ID')){
                        $APPLICATION->addError('Не указана компания  ошибка создания документа');
                    //throw new Bitrix\Main\SystemException('Не указана компания  ошибка создания документа');
                    }

                    $num = new \Mywebstor\Numerator\MwsNumerator($DealRes->get('COMPANY_ID'));
                    \Bitrix\Main\Diag\Debug::writeToFile(print_r( $num,true),"","_DOC_log.log");
                    $document->setValues(['DocumentNumber' => $num]);



                }
            }
        }
    }
    static function _OnAfterDelete($fields){
        \Bitrix\Main\Diag\Debug::writeToFile(print_r("Удаление",true),"","_DOC_log.log");
        Bitrix\Main\Loader::includeModule('crm');
        $document = $fields->getParameter('document');
        $result = $document->getFile();

        $docId = $result->getData();

        \Bitrix\Main\Diag\Debug::writeToFile(print_r( $docId['id'],true),"","_DOC_log.log");
        $template = $document->getTemplate();


        $provider = $document->getProvider();



        $ownerType = $provider->getCrmOwnerType();
        \Bitrix\Main\Diag\Debug::writeToFile(print_r( $ownerType,true),"","_DOC_log.log");
        $dealId = $provider->getSource();

        \Bitrix\Main\Diag\Debug::writeToFile(print_r( $dealId,true),"","_DOC_log.log");


    }


}

