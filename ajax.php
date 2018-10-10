<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

$return=array();

//if ($APPLICATION->CaptchaCheckCode($_POST['captcha_word'], $_POST['captcha_code']))
if (1)
{
    ob_start();
    ?>
    <p>
        <a href='javascript:window.location = "/load/feed.php"'  >Выгрузка товаров</a><br/>
        <a href='javascript:window.location = "/load/create_xls.php"'  >Шины (.xls)</a><br/>
        <a href='javascript:window.location = "/load/create_xls.php?action=s"'  >Шины (.xls)</a><br/>
        <a  href='javascript:window.location = "/load/create_xls.php?action=d"'>Диски (.xls)</a><br/>
    </p>
    <?php
    $page = ob_get_contents();
    ob_end_clean();
    $return['success']=$page;
}else{
    $return['error']='Капча введена неверно!!!';
}
echo json_encode($return);