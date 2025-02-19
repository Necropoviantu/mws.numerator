<?php
\Bitrix\Main\Loader::registerAutoloadClasses(
    'mws.numerator',
    array(
        "MwsNumeratorRest" => "classes/restservice.php",
        "MwsHandlerDocs" => "classes/handler.php",
        "Mywebstor\Numerator\Client\NumeratorClientTable" => "lib/numeratorClientTable.php",
        "Mywebstor\Numerator\Client\NumeratorAllTable" => "lib/numeratorServiceAll.php",
        "Mywebstor\Numerator\Client\NumeratorPhoneTable" => "lib/numeratorPhone.php",

        "Mywebstor\Numerator\MwsNumerator"=>"lib/numerator.php",
        "Mywebstor\Numerator\MwsNumeratorAll"=>"lib/numeratorAll.php",
        "Mywebstor\Numerator\MwsNumeratorPhone"=>"lib/numeratorPhones.php",
    )
);