<?
define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Выгрузки");
?>
<p>
    <a href="feed.php" target="_blank">Выгрузка товаров</a>
</p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
