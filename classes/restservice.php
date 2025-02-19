<?php
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Numerator\Numerator;
use Bitrix\Main\Numerator\Generator;
use \Bitrix\Main\Type\DateTime;
use Mywebstor\Numerator\Client\NumeratorAllTable;
use Mywebstor\Numerator\Client\NumeratorPhoneTable;
class MwsNumeratorRest extends IRestService
{

    public static function OnRestServiceBuildDescription()
    {
        return array(
            'mwsnumerator'=>array(
                //TODO активаторы
                "mwsnumerator.getActivateNumeratorTemplate"=>array(__CLASS__,"getActivateNumeratorTemplate"),
                "mwsnumerator.setActivateNumeratorTemplate"=>array(__CLASS__,"setActivateNumeratorTemplate"),
                "mwsnumerator.getActivateNumeratorService"=>array(__CLASS__,"getActivateNumeratorService"),
                "mwsnumerator.setActivateNumeratorService"=>array(__CLASS__,"setActivateNumeratorService"),
                "mwsnumerator.getActivateNumeratorPhone"=>array(__CLASS__,"getActivateNumeratorPhone"),
                "mwsnumerator.setActivateNumeratorPhone"=>array(__CLASS__,"setActivateNumeratorPhone"),

                //TODO темплейты
                "mwsnumerator.getTemplatesDoc" => array(__CLASS__, "getTemplatesDoc"),
                "mwsnumerator.hlblockTemplate.create" => array(__CLASS__, "hlblockTemplateCreate"),
                "mwsnumerator.hlblockTemplate.update" => array(__CLASS__, "hlblockTemplateUpdate"),
                "mwsnumerator.hlblockTemplate.getList" => array(__CLASS__, "hlblockTemplategetList"),
                "mwsnumerator.hlblockTemplate.delete" => array(__CLASS__, "hlblockTemplateDelete"),
                //TODO настройка через списки
                "mwsnumerator.getAllLists" => array(__CLASS__, "getAllLists"),
                "mwsnumerator.setAllSettings" => array(__CLASS__, "setAllSettings"),
                "mwsnumerator.getAllSettings" => array(__CLASS__, "getAllSettings"),
                "mwsnumerator.setPhoneSettings" => array(__CLASS__, "setPhoneSettings"),
                "mwsnumerator.getPhoneSettings" => array(__CLASS__, "getPhoneSettings"),

                "mwsnumerator.generatedAllNums" => array(__CLASS__, "generatedAllNums"),
                "mwsnumerator.getCityNumerators" => array(__CLASS__, "getCityNumerators"),
                "mwsnumerator.setCityNumeratorNum" => array(__CLASS__, "setCityNumeratorNum"),
                "mwsnumerator.HasNumeratorOnCity" => array(__CLASS__, "HasNumeratorOnCity"),
                "mwsnumerator.CityList" => array(__CLASS__, "CityList"),
                "mwsnumerator.createNumeratorOnCity" => array(__CLASS__, "createNumeratorOnCity"),


                "mwsnumerator.generatedPhoneNums" => array(__CLASS__, "generatedPhoneNums"),
                "mwsnumerator.getPhoneNumerators" => array(__CLASS__, "getPhoneNumerators"),
                "mwsnumerator.setPhoneNumeratorNum" => array(__CLASS__, "setPhoneNumeratorNum"),
                "mwsnumerator.CityListPhone" => array(__CLASS__, "CityListPhone"),
                "mwsnumerator.HasNumeratorOnPhone" => array(__CLASS__, "HasNumeratorOnPhone"),
                "mwsnumerator.createNumeratorOnPhone" => array(__CLASS__, "createNumeratorOnPhone"),


            ),
        );
    }
    public static function getActivateNumeratorTemplate($query, $nav, \CRestServer $server)
    {
        $active = Option::get('mws.numerator', 'active_doc_numerator', '');
            return $active;
    }
    public static function setActivateNumeratorTemplate($query, $nav, \CRestServer $server)
    {
        Option::set('mws.numerator', 'active_doc_numerator', $query['active']);

        return 'save';
    }


    public static function getActivateNumeratorPhone($query, $nav, \CRestServer $server)
    {
        $active = Option::get('mws.numerator', 'active_phone_numerator', '');
        return $active;
    }
    public static function setActivateNumeratorPhone($query, $nav, \CRestServer $server)
    {
        Option::set('mws.numerator', 'active_phone_numerator', $query['active']);

        return 'save';
    }


    public static function getActivateNumeratorService($query, $nav, \CRestServer $server)
    {
        $active = Option::get('mws.numerator', 'active_service_numerator', '');
        return $active;
    }
    public static function setActivateNumeratorService($query, $nav, \CRestServer $server)
    {
        Option::set('mws.numerator', 'active_service_numerator', $query['active']);

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
    public static function getAllSettings($query, $nav, \CRestServer $server)
    {
        $numerator_all = [
            'city' => Option::get('mws.numerator', 'numerator_all_city', ''),
            'service' => Option::get('mws.numerator', 'numerator_all_service', ''),
            'type' => Option::get('mws.numerator', 'numerator_all_type', ''),
        ];
        return $numerator_all;
    }
    public static function setAllSettings($query, $nav, \CRestServer $server){
        $set = $query['settings'];

        Option::set('mws.numerator', 'numerator_all_city', $set['city']);
        Option::set('mws.numerator', 'numerator_all_service', $set['service']);
        Option::set('mws.numerator', 'numerator_all_type', $set['type']);

        return 'save';

    }
    public static function getPhoneSettings($query, $nav, \CRestServer $server)
    {
        $numerator_phone = [
            'city' => Option::get('mws.numerator', 'numerator_phone_city', ''),
            'ops' => Option::get('mws.numerator', 'numerator_phone_ops', ''),
            'type' => Option::get('mws.numerator', 'numerator_phone_type', ''),
        ];
        return $numerator_phone;
    }
    public static function setPhoneSettings($query, $nav, \CRestServer $server){
        $set = $query['settings'];

        Option::set('mws.numerator', 'numerator_phone_city', $set['city']);
        Option::set('mws.numerator', 'numerator_phone_ops', $set['ops']);
        Option::set('mws.numerator', 'numerator_phone_type', $set['type']);

        return 'save';

    }
    public static function generatedAllNums($query, $nav, \CRestServer $server){
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
                "!=PREFIKS_RU.VALUE"=>""

            ],
            'select' => [
                "ID",
                "NAME",
                "PREFIX"=>"PREFIKS_RU.VALUE"

            ]
        ));
      $cityes =  $res->fetchAll();

        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_all['type'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_all['type'],


            ],
            'select' => [
                "ID",
                "NAME",
                          ]
        ));
        $types =  $res->fetchAll();

      foreach ($cityes as $city){
          foreach ($types as $type){
              $numerator =  NumeratorAllTable::getlist(['filter'=>[
                  'CITY_ID'=>$city['ID'],
                  'CLIENT_TYPE'=>$type['ID']
              ]])->fetch();
              if($numerator){
                  continue;
              }

              $config = [
                  Numerator::getType() => [
                      'name' => 'Город '.$city['NAME'],
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
                  'CITY_ID' => $city['ID'],
                  'NUMERATOR_ID'=>$result->getId(),
                  'CLIENT_TYPE'=>$type['ID'],
                  'CURRENT_NUM'=>0
              ]);


          }
      }
      return 'generated';

    }
    public static function getCityNumerators($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        \Bitrix\Main\Loader::includeModule("iblock");
        $numerator_all = [
            'city' => Option::get('mws.numerator', 'numerator_all_city', ''),
            'service' => Option::get('mws.numerator', 'numerator_all_service', ''),
            'type' => Option::get('mws.numerator', 'numerator_all_type', ''),
        ];


        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_all['city'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_all['city'],
                "!=PREFIKS_RU.VALUE"=>""

            ],
            'select' => [
                "ID",
                "NAME",
                "PREFIX"=>"PREFIKS_RU.VALUE"

            ]
        ));

        $cityes =  [];
       while($city =  $res->fetch()){
           $numerator =  NumeratorAllTable::getlist(['filter'=>['CITY_ID'=>$city['ID'],]])->fetchAll();
            $city['numerators'] = $numerator;
           if($city['numerators'] && count($city['numerators']) > 0) {
               $cityes[] = $city;
           }
       }



        return $cityes;
    }
    public static function setCityNumeratorNum($query, $nav, \CRestServer $server)
    {
        $num = $query['num'];
        $numerator = Numerator::load($num["NUMERATOR_ID"]);
        $numerator->setNextSequentialNumber($num['CURRENT_NUM']);
        $num = NumeratorAllTable::update($num["ID"], [
            'CURRENT_NUM' => $num["CURRENT_NUM"]
        ]);



        return 'ok';
    }
    public static function CityList($query, $nav, \CRestServer $server)
    {
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
                "!=PREFIKS_RU.VALUE"=>""

            ],
            'select' => [
                "ID",
                "NAME",
                "PREFIX"=>"PREFIKS_RU.VALUE"

            ]
        ));
        $cityes =  $res->fetchAll();
        return $cityes;
    }
    public static function HasNumeratorOnCity($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule('mws.numerator');

        $numerator =  NumeratorAllTable::getlist(['filter'=>[
            'CITY_ID'=>$query['city'],
        ]])->fetchAll();

        return $numerator;
    }
    public static function createNumeratorOnCity($query, $nav, \CRestServer $server){
            $city = $query['city_id'];
        \Bitrix\Main\Loader::includeModule("iblock");
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        $numerator_all = [
            'city' => Option::get('mws.numerator', 'numerator_all_city', ''),
            'service' => Option::get('mws.numerator', 'numerator_all_service', ''),
            'type' => Option::get('mws.numerator', 'numerator_all_type', ''),
        ];
        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_all['type'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_all['type'],


            ],
            'select' => [
                "ID",
                "NAME",
            ]
        ));
        $types =  $res->fetchAll();

        foreach ($types as $type){
            $numerator =  NumeratorAllTable::getlist(['filter'=>[
                'CITY_ID'=>$city ,
                'CLIENT_TYPE'=>$type['ID']
            ]])->fetch();
            if($numerator){
                continue;
            }

            $config = [
                Numerator::getType() => [
                    'name' => 'Город '.$city ,
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
                'CLIENT_TYPE'=>$type['ID'],
                'CURRENT_NUM'=>0
            ]);


        }


        return 'generated';
    }

    public static function generatedPhoneNums($query, $nav, \CRestServer $server){
        \Bitrix\Main\Loader::includeModule("iblock");
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        $numerator_phone = [
            'city' => Option::get('mws.numerator', 'numerator_phone_city', ''),
            'ops' => Option::get('mws.numerator', 'numerator_phone_ops', ''),
            'type' => Option::get('mws.numerator', 'numerator_phone_type', ''),
        ];





        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_phone['city'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_phone['city'],
                "!=PREFIKS_EN.VALUE"=>"",
                "!=PREFIKS_RU.VALUE"=>""
            ],
            'select' => [
                "ID",
                "NAME",
                "PREFIX"=>"PREFIKS_EN.VALUE",
                "PREFIX_RU"=>"PREFIKS_RU.VALUE"

            ]
        ));
        $cityes =  $res->fetchAll();

        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_phone['ops'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_phone['ops'],
            ],
            'select' => [
                "ID",
                "NAME",
            ]
        ));
        $types =  $res->fetchAll();

        foreach ($cityes as $city){
            foreach ($types as $type){
                $numerator =  NumeratorPhoneTable::getlist(['filter'=>[
                    'CITY_ID'=>$city['ID'],
                    'OPS_TYPE'=>$type['ID']
                ]])->fetch();
                if($numerator){
                    continue;
                }

                $config = [
                    Numerator::getType() => [
                        'name' => 'Город '.$city['NAME'],
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
                    'CITY_ID' => $city['ID'],
                    'NUMERATOR_ID'=>$result->getId(),
                    'OPS_TYPE'=>$type['ID'],
                    'CURRENT_NUM'=>0
                ]);


            }
        }
        return 'generated';

    }

    public static function getPhoneNumerators($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        \Bitrix\Main\Loader::includeModule("iblock");
        $numerator_phone = [
            'city' => Option::get('mws.numerator', 'numerator_phone_city', ''),
            'ops' => Option::get('mws.numerator', 'numerator_phone_ops', ''),
            'type' => Option::get('mws.numerator', 'numerator_phone_type', ''),
        ];



        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_phone['city'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_phone['city'],
                "!=PREFIKS_EN.VALUE"=>"",
                "!=PREFIKS_RU.VALUE"=>""
            ],
            'select' => [
                "ID",
                "NAME",
                "PREFIX_EN"=>"PREFIKS_EN.VALUE",
                "PREFIX_RU"=>"PREFIKS_RU.VALUE"

            ]
        ));

        $resl = \Bitrix\Iblock\Iblock::wakeUp($numerator_phone['ops'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_phone['ops'],

            ],
            'select' => [
                "ID",
                "NAME",

            ]
        ));

        $opss=[];
        while($ops = $resl->fetch()){
            $opss[$ops['ID']] = $ops['NAME'];



        }





        $result = [];

        while($num = $res->fetch()){
            $numerators =  NumeratorPhoneTable::getlist(['filter'=>['CITY_ID'=>$num['ID'],]])->fetchAll();

            foreach($numerators as &$numers){

                if($numers['OPS_TYPE']==15625){
                    $numers['PREF'] = $num['PREFIX_RU'];
                    $numers['OPS'] = $opss[$numers['OPS_TYPE']];
                    $num['numerators'][] =$numers;
                }
                if($numers['OPS_TYPE']==15624){
                    $numers['PREF'] = $num['PREFIX_EN'];
                    $numers['OPS'] = $opss[$numers['OPS_TYPE']];
                    $num['numerators'][] =$numers;
                }


            }

            $result[] =$num;
        }








        return $result;
    }

    public static function setPhoneNumeratorNum($query, $nav, \CRestServer $server)
    {
        $num = $query['num'];
        $numerator = Numerator::load($num["NUMERATOR_ID"]);
        $numerator->setNextSequentialNumber($num['CURRENT_NUM']);
        $num = NumeratorPhoneTable::update($num["ID"], [
            'CURRENT_NUM' => $num["CURRENT_NUM"]
        ]);



        return 'ok';
    }

    public static function CityListPhone($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule("iblock");
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        $numerator_phone = [
            'city' => Option::get('mws.numerator', 'numerator_phone_city', ''),
            'ops' => Option::get('mws.numerator', 'numerator_phone_ops', ''),
            'type' => Option::get('mws.numerator', 'numerator_phone_type', ''),
        ];





        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_phone['city'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_phone['city'],
                "!=PREFIKS_EN.VALUE"=>"",
                "!=PREFIKS_RU.VALUE"=>""
            ],
            'select' => [
                "ID",
                "NAME",
                "PREFIX"=>"PREFIKS_EN.VALUE",
                "PREFIX_RU"=>"PREFIKS_RU.VALUE"

            ]
        ));
        $cityes =  $res->fetchAll();
        return $cityes;
    }

    public static function HasNumeratorOnPhone($query, $nav, \CRestServer $server)
    {
        \Bitrix\Main\Loader::includeModule('mws.numerator');

        $numerator =  NumeratorPhoneTable::getlist(['filter'=>[
            'CITY_ID'=>$query['city'],
        ]])->fetchAll();

        return $numerator;
    }

    public static function createNumeratorOnPhone($query, $nav, \CRestServer $server){
        $city = $query['city_id'];
        \Bitrix\Main\Loader::includeModule("iblock");
        \Bitrix\Main\Loader::includeModule('mws.numerator');
        $numerator_phone = [
            'city' => Option::get('mws.numerator', 'numerator_phone_city', ''),
            'ops' => Option::get('mws.numerator', 'numerator_phone_ops', ''),
            'type' => Option::get('mws.numerator', 'numerator_phone_type', ''),
        ];

        $res = \Bitrix\Iblock\Iblock::wakeUp($numerator_phone['ops'])->getEntityDataClass()::getList(array(
            'filter' => [
                'IBLOCK_ID' => $numerator_phone['ops'],


            ],
            'select' => [
                "ID",
                "NAME",
            ]
        ));
        $types =  $res->fetchAll();

        foreach ($types as $type){
            $numerator =  NumeratorPhoneTable::getlist(['filter'=>[
                'CITY_ID'=>$city ,
                'OPS_TYPE'=>$type['ID']
            ]])->fetch();
            if($numerator){
                continue;
            }

            $config = [
                Numerator::getType() => [
                    'name' => 'Город '.$city ,
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
                'OPS_TYPE'=>$type['ID'],
                'CURRENT_NUM'=>0
            ]);


        }


        return 'generated';
    }
}