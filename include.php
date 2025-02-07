<?php
\Bitrix\Main\Loader::registerAutoloadClasses(
    'mws.numerator',
    array(
        "MwsHandlerDocs" => "classes/handler.php",
        "Mywebstor\Numerator\Client\NumeratorClientTable" => "lib/numeratorClientTable.php",
        "Mywebstor\Numerator\MwsNumerator"=>"lib/numerator.php",
    )
);