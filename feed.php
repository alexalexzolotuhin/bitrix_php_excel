<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");



CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');

$allItems = array();
$allSections = array();
$sections = array();
$arSelect = Array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL", "NAME", "DETAIL_PICTURE", "IBLOCK_SECTION_ID", "DETAIL_TEXT", "CATALOG_GROUP_1", "PROPERTY_*");
$arFilter = Array("ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "IBLOCK_ID" => 3);

$sections = \Gw\Help\UserSection::get();
$stores = \Gw\Help\UserStore::get();
$arFilter['SECTION_ID'] = $sections;
$arFilter['PROPERTY_STORE'] = $stores;
$arFilter['INCLUDE_SUBSECTIONS'] = 'Y';

$res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
$count=0;
while($ob = $res->GetNextElement()){
    $count++;
    /*
    if($count==30){
        break;
    } */
    $arFields = $ob->GetFields();
    $arFields['PRICE'] = \CCatalogProduct::GetOptimalPrice($arFields['ID']);
    $allItems[] = $arFields;
    $sections[] = $arFields['IBLOCK_SECTION_ID'];
}

$iterator = \Bitrix\Iblock\SectionTable::getList([
    'select' => ['ID', 'NAME'],
    'filter' => ['ID' => $sections]
]);
while ($ob = $iterator->fetch())
{
    $allSections[$ob['ID']] = trim(htmlspecialchars(iconv('UTF-8', 'CP1251', $ob['NAME'])));
}


header("Content-type: application/xml; charset=windows-1251");

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=vygruzka.xml');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');


echo '<?xml version="1.0" encoding="windows-1251"?>';
?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="<?=date("Y-m-d H:i");?>">
    <shop>
        <name>opt.goodwheels.ru</name>
        <company>opt.goodwheels.ru</company>
        <url>https://opt.goodwheels.ru/</url>
        <currencies>
            <currency id="RUR" rate="1" plus="0"/>
        </currencies>
        <categories>
            <?foreach( $allSections as $section_id => $section) {?>
                <category id="<?=$section_id?>"><?=$section?></category>
            <?}?>
        </categories>
        <local_delivery_cost>0</local_delivery_cost>
        <offers>
            <?foreach($allItems as $arFields){?>
                <offer id="<?=$arFields['ID']?>" type="vendor.model" available="true">
                    <url>https://opt.goodwheels.ru<?=$arFields['DETAIL_PAGE_URL']?></url>
                    <?if ($arFields['PRICE']['DISCOUNT_PRICE'] < $arFields['PRICE']['PRICE']['PRICE']):?>
                        <price><?=$arFields['PRICE']['DISCOUNT_PRICE']?></price>
                        <oldprice><?=CCurrencyRates::ConvertCurrency($arFields['CATALOG_PRICE_1'], $arFields['CATALOG_CURRENCY_1'], "RUR")?></oldprice>
                    <?else:?>
                        <price><?=CCurrencyRates::ConvertCurrency($arFields['CATALOG_PRICE_1'], $arFields['CATALOG_CURRENCY_1'], "RUR")?></price>
                    <?endif?>
                    <currencyId>RUR</currencyId>
                    <categoryId type="Own"><?=$arFields['IBLOCK_SECTION_ID']?></categoryId>
                    <picture>https://opt.goodwheels.ru<?=CFile::GetPath($arFields['DETAIL_PICTURE'])?></picture>
                    <delivery>true</delivery>
                    <pickup>true</pickup>
                    <local_delivery_cost>1000</local_delivery_cost>
                    <model><?=htmlspecialchars(iconv('UTF-8', 'CP1251',$arFields['NAME']))?></model>
                    <description><![CDATA[<?=iconv('UTF-8', 'CP1251',$arFields['DETAIL_TEXT'])?>]]></description>
                    <manufacturer_warranty>true</manufacturer_warranty>
                    <vendor>opt.goodwheels.ru</vendor>
                </offer>
            <?}?>
        </offers>
    </shop>
</yml_catalog>