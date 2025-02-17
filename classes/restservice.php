<?php
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use \Bitrix\Main\Type\DateTime;

class MwsNumeratorRest extends IRestService
{

    public static function OnRestServiceBuildDescription()
    {
        return array(
            'mwsnumerator'=>array(
                //TODO активаторы
                "mwsnumerator.getActivateNumeratorTemplate"=>array(__CLASS__,"getActivateNumeratorTemplate"),
                "mwsnumerator.setActivateNumeratorTemplate"=>array(__CLASS__,"setActivateNumeratorTemplate"),
                //TODO темплейты
                "mwsnumerator.getTemplatesDoc" => array(__CLASS__, "getTemplatesDoc"),
                "mwsnumerator.hlblockTemplate.create" => array(__CLASS__, "hlblockTemplateCreate"),
                "mwsnumerator.hlblockTemplate.update" => array(__CLASS__, "hlblockTemplateUpdate"),
                "mwsnumerator.hlblockTemplate.getList" => array(__CLASS__, "hlblockTemplategetList"),
                "mwsnumerator.hlblockTemplate.delete" => array(__CLASS__, "hlblockTemplateDelete"),
                //TODO настройка через списки
                "mwsnumerator.getAllLists" => array(__CLASS__, "getAllLists"),

            ),
        );

    }
    public static function getActivateNumeratorTemplate($query, $nav, \CRestServer $server)
    {
        $active = Option::get('mws.numerator', 'active_doc_nemerator', '');
            return $active;
    }
    public static function setActivateNumeratorTemplate($query, $nav, \CRestServer $server)
    {
        Option::set('mws.numerator', 'active_doc_nemerator', $query['active']);

        return 'save';
    }

    public static function getTemplatesDoc($query, $nav, \CRestServer $server)
    {
        $cat = $query['category'];

        $res = \Bitrix\DocumentGenerator\Model\TemplateTable::getList(array(
            "filter"=>[
                "=PROVIDER.PROVIDER" => mb_strtolower(Bitrix\Crm\Integration\DocumentGenerator\DataProvider\Deal::class)."_category_" . $cat,
            ],

            "select"=>['ID','NAME']
        ));

        return $res->fetchAll();
    }
    public static function hlblockTemplateCreate($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('highloadblock');
        $LKtoUpdate = COption::GetOptionString("mws.numerator", "mws_numerator_template_document", 0);
        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
        $erorrs =[];
        $row = $query['row'];
        $hlEntity = $hlblockTable::add(array(
            "UF_TEMPLATE_CATEGORY" =>  $row["UF_TEMPLATE_CATEGORY"],
            "UF_TEMPLATE_TEMPLATES" =>  implode(', ',$row['UF_TEMPLATE_TEMPLATES']),
        ));
        if(!$hlEntity->isSuccess()){
            $erorrs[] = $hlEntity->getErrorMessages();

        }
        if(!empty($erorrs)){
            return $erorrs;
        }else{
            return 'Ok';
        }

    }
    public static function hlblockTemplateUpdate($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('highloadblock');
        $LKtoUpdate = COption::GetOptionString("mws.numerator", "mws_numerator_template_document", 0);
        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
        $erorrs =[];
        $rawData = $query['data'];

//            foreach ($query['rows'] as $row ) {
        $hlEntity = $hlblockTable::update( $rawData['ID'],array(
            "UF_TEMPLATE_CATEGORY" =>  $rawData["UF_TEMPLATE_CATEGORY"],
            "UF_TEMPLATE_TEMPLATES" =>  implode(', ',$rawData['UF_TEMPLATE_TEMPLATES']),
        ));
        if(!$hlEntity->isSuccess()){
            $erorrs[] = $hlEntity->getErrorMessages();
        }
//            }
        if(!empty($erorrs)){
            return $erorrs;
        }else{
            return 'Ok';
        }
    }
    public static function hlblockTemplategetList($query, $nav, \CRestServer $server)
    {
        $LKtoUpdate = COption::GetOptionString("mws.numerator", "mws_numerator_template_document", 0);
        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
        $hlEntity = $hlblockTable::getList(array(
            "filter" => $query['filter'] ?:[],
            "select" => ['*'],
        ));
        $result = [];

        while ($row = $hlEntity->fetch()) {
            $row['UF_TEMPLATE_TEMPLATES'] = explode(', ',$row['UF_TEMPLATE_TEMPLATES']);
            $result[] = $row;

        }




        return $result;
    }
    public static function hlblockTemplateDelete($query, $nav, \CRestServer $server)
    {
        Bitrix\Main\Loader::includeModule('highloadblock');
        $LKtoUpdate = COption::GetOptionString("mws.numerator", "mws_numerator_template_document", 0);
        $hlblockTable = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($LKtoUpdate)->getDataClass();
        $erorrs =[];
        foreach ($query['rows'] as $row ) {
            $hlEntity = $hlblockTable::delete($row['ID']);
            if(!$hlEntity->isSuccess()){
                $erorrs[] = $hlEntity->getErrorMessages();
            }
        }
        if(!empty($erorrs)){
            return $erorrs;
        }else{
            return 'ok';
        }
    }

    public static function getAllLists($query, $nav, \CRestServer $server)
    {
        $res = \CIBlock::GetList(
            Array(),
            Array(
                'TYPE'=>'lists',
            ), true
        );
        $result = [];
        while($ar_res = $res->Fetch())
        {
            $result[] =["ID"=>$ar_res['ID'],"NAME"=>$ar_res['NAME']];
        }
        return $result;
    }

}