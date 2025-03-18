<?php
global $APPLICATION;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->SetTitle("Настройка подключения к BGBilling");
?>

<body>
<script type="module" crossorigin src="/local/modules/mws.numerator/admin/assets/index-BwStNmKN.js"></script>
<link rel="stylesheet" crossorigin href="/local/modules/mws.numerator/admin/assets/index-BBZFzPDu.css">
    <div id="app_numerator"></div>
  
</body>

<?php
require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
?>