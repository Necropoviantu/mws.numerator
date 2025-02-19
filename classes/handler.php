<?php
use Bitrix\Main\Config\Option;
use Bitrix\Main\Numerator\Numerator;
use Mywebstor\Numerator\MwsNumerator;
use Mywebstor\Numerator\MwsNumeratorAll;
use Mywebstor\Numerator\MwsNumeratorPhone;
use Mywebstor\Numerator\Client\NumeratorClientTable;
use Bitrix\Main\SystemException;
use Bitrix\Crm\History\Entity\DealStageHistoryTable;
use Bitrix\Crm\DealTable;

use Bitrix\Main\Diag\Debug;
use Bitrix\Main\EventManager;

use Mywebstor\Numerator\Client\NumeratorAllTable;
use Mywebstor\Numerator\Client\NumeratorPhoneTable;


//Осталось завести шаблоны и условия
class MwsHandlerDocs {
    static function _onBeforeProcessDocument($event)
    {
        $active = Option::get('mws.numerator', 'active_doc_nemerator', '');
        if($active == 'Y') {
            \Bitrix\Main\Diag\Debug::writeToFile(print_r("Создание", true), "", "_DOC_log.log");
            $application = \Bitrix\Main\Application::getInstance();
            Bitrix\Main\Loader::includeModule('documentgenerator');
            Bitrix\Main\Loader::includeModule('mws.numerator');
            Bitrix\Main\Loader::includeModule('crm');


            $document = $event->getParameter('document');
            $template = $document->getTemplate();
            $templateID = $template->ID;
            if ($template && $template->MODULE_ID == 'crm') {

                $provider = $document->getProvider();
                $ownerType = $provider->getCrmOwnerType();
                if ($ownerType == 2) {
                    $dealId = $provider->getSource();

                    $DealRes = Bitrix\Crm\DealTable::query()
                        ->where('ID', $dealId)
                        ->setSelect(array("ID", "CATEGORY_ID", 'COMPANY_ID'))
                        ->setLimit(1)
                        ->fetchObject();

                    if ($DealRes) {

                        $LKtoUpdate = COption::GetOptionString("mws.numerator", "mws_numerator_template_document", 0);
                        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
                        $hlEntity = $hlblockTable::getList(array(
                            "filter" => ['UF_TEMPLATE_CATEGORY' => $DealRes['CATEGORY_ID']],
                            "select" => ['*'],
                        ));
                        $tempCat = [];

                        while ($row = $hlEntity->fetch()) {
                            $tempCat = explode(', ', $row['UF_TEMPLATE_TEMPLATES']);


                        }
                        \Bitrix\Main\Diag\Debug::writeToFile(print_r($tempCat, true), "", "_DOC_log.log");
                        if (count($tempCat) > 0) {

                            if (in_array($templateID, $tempCat)) {
                                \Bitrix\Main\Diag\Debug::writeToFile(print_r($DealRes->get('COMPANY_ID'), true), "", "_DOC_log.log");
                                if (!$DealRes->get('COMPANY_ID')) {

                                    throw new Bitrix\Main\SystemException('Не указана компания  ошибка создания документа');
                                }

                                $num = new \Mywebstor\Numerator\MwsNumerator($DealRes->get('COMPANY_ID'));

                                $document->setValues(['DocumentNumber' => $num->init()]);
                            }
                        }

                    }
                }
            }

        }

    }
    static function _OnAfterDelete($fields){
        $active = Option::get('mws.numerator', 'active_doc_nemerator', '');
        if($active == 'Y') {
            \Bitrix\Main\Diag\Debug::writeToFile(print_r('Удаление', true), "", "_DOC_log.log");
            global $APPLICATION;
            Bitrix\Main\Loader::includeModule('documentgenerator');
            Bitrix\Main\Loader::includeModule('mws.numerator');
            Bitrix\Main\Loader::includeModule('crm');
            $document = $fields->getParameter('document');


            $template = $document->getTemplate();
            if ($template && $template->MODULE_ID == 'crm') {
                $provider = $document->getProvider();
                $ownerType = $provider->getCrmOwnerType();
                if ($ownerType == 2) {
                    $dealId = $provider->getSource();
                    $DealRes = Bitrix\Crm\DealTable::query()
                        ->where('ID', $dealId)
                        ->setSelect(array("ID", "CATEGORY_ID", 'COMPANY_ID'))
                        ->setLimit(1)
                        ->fetchObject();


                    if ($DealRes) {
                        $LKtoUpdate = COption::GetOptionString("mws.numerator", "mws_numerator_template_document", 0);
                        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
                        $hlEntity = $hlblockTable::getList(array(
                            "filter" => ['UF_TEMPLATE_CATEGORY' => $DealRes['CATEGORY_ID']],
                            "select" => ['*'],
                        ));
                        $tempCat = [];

                        while ($row = $hlEntity->fetch()) {
                            $tempCat = explode(', ', $row['UF_TEMPLATE_TEMPLATES']);


                        }
                        \Bitrix\Main\Diag\Debug::writeToFile(print_r($tempCat, true), "", "_DOC_log.log");
                        if (count($tempCat) > 0) {
                            if (!$DealRes->get('COMPANY_ID')) {
                                throw new Bitrix\Main\SystemException('Не указана компания  ошибка создания документа');
                            }

                            $getNumerator = Mywebstor\Numerator\Client\NumeratorClientTable::query()
                                ->where('COMPANY_ID', $DealRes->get('COMPANY_ID'))
                                ->setSelect(array("*"))
                                ->setLimit(1)
                                ->fetchObject();
                            $getAllDealsCompany = \Bitrix\Crm\DealTable::getList([
                                "filter" => ['COMPANY_ID' => $getNumerator->get('COMPANY_ID')],
                                'select' => ['ID']
                            ]);
                            $dealIds = [];

                            while ($deal = $getAllDealsCompany->fetch()) {
                                $dealIds[] = $deal['ID'];
                            }
                            //Забираем все документы созданные по сделке с темплейтами

                            $docs = \Bitrix\DocumentGenerator\Model\DocumentTable::getlist([
                                'order' => ['ID' => 'DESC'],
                                'filter' => [
                                    //TEMPLATE_ID=>[]
                                    '=PROVIDER' => mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class),
                                    'VALUE' => $dealIds,
                                ],
                                'select' => ['ID', 'NUMBER', 'TEMPLATE_ID']
                            ])->fetchAll();
                            \Bitrix\Main\Diag\Debug::writeToFile(print_r($docs, true), "", "_DOC_log.log");

                            if ($docs[0]['ID'] == $document->ID) {

                                $numerator = Numerator::load($getNumerator->get("NUMERATOR_ID"));
                                $numerator->setNextSequentialNumber($docs[0]['NUMBER']);
                                $num = NumeratorClientTable::update($getNumerator->get('ID'), [
                                    'CURRENT_NUM' => $docs[0]['NUMBER']
                                ]);

                            }

                        }
                    }
                }

            }


        }
    }

    //Сложный нумератор
    static function setNumberOnStage($fields)
    {
        \Bitrix\Main\Loader::includeModule('crm');
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        $arErrorsTmp=[];
        $entityTypeID = \CCrmOwnerType::Deal;
        $factory = Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeID);
        $item = $factory->getItem($fields['ID']);
        $cat = $item->get('CATEGORY_ID'); //воронка
        if($cat != 1) return;
        $stage = $item->get('STAGE_ID');//стадия
        if($stage != 'C1:PREPARATION') return;


        $doc = self::addNumDog($fields['ID']);
        $mg = self::addNumPhone($fields['ID']);


    }


    private static function addNumDog($dealID)
    {
        \Bitrix\Main\Loader::includeModule('crm');
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        $arErrorsTmp=[];
        $entityTypeID = \CCrmOwnerType::Deal;
        $factory = Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeID);
        $item = $factory->getItem($dealID);

        $hasnum = $item->get('UF_CRM_1727162408');
        if($hasnum)return 0;
        $abonType = $item->get('UF_CRM_64EC912D44388');//фл-юл

        if(!$abonType)return 0;
        $service = $item->get('UF_CRM_1710254867');//Услуга

        if(!$service)return 0;
        $city  = $item->get('UF_CRM_64EDAFBE9651B');//город
        if(!$city)return 0;

        $numerator =  NumeratorAllTable::getlist(['filter'=>[
            'CITY_ID'=>  $city,
            'CLIENT_TYPE'=>$abonType
        ]])->fetch();
        if(!$numerator)return 0;
        $prefCity =  self::getCity($city);

        if(!$prefCity) return 0;

        $prefServ = self::getService($service);

        if(!$prefServ) return 0;

        $num = new Mywebstor\Numerator\MwsNumeratorAll($city,$abonType);


        $item->set('UF_CRM_1727162408',  $prefServ."/".($abonType == 32 ?"ФЛ" : "ЮЛ" )."/".$prefCity."-".   $num->init());
        $operation = $factory->getUpdateOperation($item);
        /*
        ** После чего указав параметры можно запускать операцию
        ** Будут приведены некоторые параметры запуска, их больше. Подробности в исходниках
        */
        $result = $operation
            ->disableCheckFields() //Не проверять обязательные поля
            ->disableCheckAccess() //Не проверять права доступа
            ->disableAfterSaveActions() //Не запускать события OnAfterCrmLeadAdd
            ->disableAutomation() //Запускать роботов (по идее должны по умолчанию запускаться)
            ->disableBizProc() //Запускать бизнес-процессы (по идее должны по умолчанию запускаться)
            ->launch(); //Запуск

        return 1;
    }
    private static function addNumPhone($dealID)
    {
        \Bitrix\Main\Loader::includeModule('crm');
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        $arErrorsTmp=[];
        $entityTypeID = \CCrmOwnerType::Deal;
        $factory = Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeID);
        $item = $factory->getItem($dealID);
        $service = $item->get('UF_CRM_1710254867');//Услуга

        if($service != 5630) return 0;

        $mg  = $item->get('UF_CRM_1739868398');//Международная связь
        if(!$mg)return 0;


        $city  = $item->get('UF_CRM_64EDAFBE9651B');//город
        if(!$city)return 0;

        $numerator =  NumeratorPhoneTable::getlist(['filter'=>[
            'CITY_ID'=>  $city,
            'OPS_TYPE'=> $mg
        ]])->fetch();


        if(!$numerator)return 0;
        $prefCity =  self::getCityPref($city);

        if(!$prefCity) return 0;


        $num = new Mywebstor\Numerator\MwsNumeratorPhone($city,$mg);


        if($mg == 15625){
            $item->set('UF_CRM_1739932235',  'МТС/'. $num->init(). '-'.$prefCity['PREFIX_RU'] );

        }
        if($mg==15624){

            $item->set('UF_CRM_1739932235',  'EU#'.$prefCity['PREFIX_EN']. '-' . $num->init());
        }








        $operation = $factory->getUpdateOperation($item);
        /*
        ** После чего указав параметры можно запускать операцию
        ** Будут приведены некоторые параметры запуска, их больше. Подробности в исходниках
        */
        $result = $operation
            ->disableCheckFields() //Не проверять обязательные поля
            ->disableCheckAccess() //Не проверять права доступа
            ->disableAfterSaveActions() //Не запускать события OnAfterCrmLeadAdd
            ->disableAutomation() //Запускать роботов (по идее должны по умолчанию запускаться)
            ->disableBizProc() //Запускать бизнес-процессы (по идее должны по умолчанию запускаться)
            ->launch(); //Запуск

        return 1;

    }


    private static function getCity($city){
        \Bitrix\Main\Loader::includeModule("iblock");
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        $numerator_all = [
            'city' => Option::get('mws.numerator', 'numerator_all_city', ''),
            'service' => Option::get('mws.numerator', 'numerator_all_service', ''),
            'type' => Option::get('mws.numerator', 'numerator_all_type', ''),
        ];

        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_all['city'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_all['city'],
                'ID' => $city,
                "!=PREFIKS_RU.VALUE"=>""

            ],
            'select' => [
                "ID",
                "NAME",
                "PREFIX"=>"PREFIKS_RU.VALUE"

            ]
        ));
        $cityes =  $res->fetch();
        return $cityes['PREFIX'];
    }
    private static function getCityPref($city){
        \Bitrix\Main\Loader::includeModule("iblock");
        \Bitrix\Main\Loader::includeModule('mws.numerator');
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

            ]
        ));
        $cityes =  $res->fetch();
        return $cityes;
    }



    private static function getService($service)
    {
        \Bitrix\Main\Loader::includeModule("iblock");
        \Bitrix\Main\Loader::includeModule('mws.numerator');

        $numerator_all = [
            'city' => Option::get('mws.numerator', 'numerator_all_city', ''),
            'service' => Option::get('mws.numerator', 'numerator_all_service', ''),
            'type' => Option::get('mws.numerator', 'numerator_all_type', ''),
        ];

        Bitrix\Main\Diag\Debug::writeToFile(print_r($numerator_all['service'],true),"","_test_log.log");
        Bitrix\Main\Diag\Debug::writeToFile(print_r($service,true),"","_test_log.log");


        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_all['service'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_all['service'],
                'ID' => $service,
                "!=PREFIKS.VALUE"=>""

            ],
            'select' => [
                "ID",
                "NAME",
                "PREF"=>"PREFIKS.VALUE"

            ]
        ));
        $service =  $res->fetch();
        if($service){
            return $service['PREF'];
        }else{
            return '';
        }
    }


}

