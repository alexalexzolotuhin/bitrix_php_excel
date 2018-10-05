<?php

require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");


CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');



function deb($var){
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}



class getXLS {
    public $masForXLS;
    public function __construct($category) {

        $this->loadAllData();
    }
    public function loadAllData(){
        $arSelect = Array("ID", "IBLOCK_ID", "DETAIL_PAGE_URL", "NAME", "DETAIL_PICTURE", "IBLOCK_SECTION_ID", "DETAIL_TEXT", "CATALOG_GROUP_1", "PROPERTY_*");
        $arFilter = Array("ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "IBLOCK_ID" => 3);
        $arFilter['INCLUDE_SUBSECTIONS ']='Y';
        //для дисков легковых
        $arFilter['SECTION_CODE ']='diski_legkovye';
        $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);

        $count=0;
        while($ob = $res->GetNextElement()){
            $count++;
            if($count==10){
                break;
            }

            $arFields = $ob->GetFields();
            $PROPERTY = $ob->GetProperties();
           // deb( $PROPERTY['KOD']);


         //   $arFields['PRICE'] = \CCatalogProduct::GetOptimalPrice($arFields['ID']);

            $array_xls=array();
            // диски
            $array_xls['COD']=$PROPERTY['KOD']['VALUE'];
            $array_xls['NOMENKL']=$arFields['NAME'];
            $array_xls['DIAMETR_SHINY']=$PROPERTY['DIAMETR_SHINY']['VALUE'];
            $array_xls['PCD']=$PROPERTY['PCD']['VALUE'];
            $array_xls['ET']=$PROPERTY['ET']['VALUE'];
            $array_xls['CB']=$PROPERTY['CB']['VALUE'];
            $array_xls['MODEL_SHINY']=$PROPERTY['MODEL_SHINY']['VALUE'];
            $array_xls['PROIZVODITEL']=$PROPERTY['PROIZVODITEL']['VALUE'];

            // разобраться с отстатками
            $array_xls['KOL_OSTATOK']=$PROPERTY['']['VALUE'];;
            $array_xls['KOL_SVOB_OSTATOK']=$PROPERTY['']['VALUE'];

            $PRICE_TYPE_ID = 3; // Оптовая цена
            //получаем различные типы цен по Id товара, нас интересует оптовая
            $rsPrices = CPrice::GetList(array(), array('PRODUCT_ID' => $arFields['ID'], 'CATALOG_GROUP_ID' => $PRICE_TYPE_ID));
            $PRICE_OPT=0;
            if ($arPrice = $rsPrices->Fetch())
            {
                $PRICE_OPT=$arPrice["PRICE"];
                echo $arFields['ID'].' '.$arPrice["PRICE"].' ; ';
              //  echo CurrencyFormat($arPrice["PRICE"], $arPrice["CURRENCY"]);
            }


            $array_xls['PRICE_OPT']=$PRICE_OPTж
            //эта ячейка всегда пустая- нужна для клиента
            $array_xls['PRICE_ZAKAZ']='';
            $this->masForXLS[]=$array_xls;
        }

        //deb( $this->masForXLS);
    }
}

$xls=new getXLS();

$action=isset($_GET['action'])?$_GET['action']:'default';
switch ($action){
    case 'default':
        echo '<a href="/load/create_xls.php?action=s">Шины</a><br/>';
        echo '<a href="/load/create_xls.php?action=d">Диски</a>';
        break;
    case 's':
        $xls=new getXLS('s');
        break;
    case 'd':

        break;
}


