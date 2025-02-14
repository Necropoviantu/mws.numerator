<?php
\Bitrix\Main\Loader::registerAutoloadClasses(
    'mws.numerator',
    array(
        "MwsNumeratorRest" => "classes/restservice.php",
        "MwsHandlerDocs" => "classes/handler.php",
        "Mywebstor\Numerator\Client\NumeratorClientTable" => "lib/numeratorClientTable.php",
        "Mywebstor\Numerator\MwsNumerator"=>"lib/numerator.php",
    )
);