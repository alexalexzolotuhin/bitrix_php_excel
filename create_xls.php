<?php
set_time_limit(50);

require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

if (ini_get('mbstring.func_overload') & 2) {
    $PHPEXCELPATH =  "lib/PHPExcel/Classes_overload2";
} else {
    $PHPEXCELPATH =  "lib/PHPExcel/Classes_overload0";
}
//require_once 'Classes/PHPExcel.php';
// Подключаем класс для работы с excel
require_once($PHPEXCELPATH.'/PHPExcel.php');
// Подключаем класс для вывода данных в формате excel
require_once($PHPEXCELPATH.'/PHPExcel/Writer/Excel5.php');



CModule::IncludeModule('iblock');
CModule::IncludeModule('catalog');



function deb($var){
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}



class getXLS {
    public $type;
    public $masForXLS;
    public function __construct($type) {
        $this->type=$type;
        $this->loadAllData();
    }
    public function loadAllData(){
        $arSelect = Array("ID", "IBLOCK_ID",  "NAME", "IBLOCK_SECTION_ID", "PROPERTY_*" );//
        $arFilter = Array("ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "IBLOCK_ID" => 3);
        $arFilter['INCLUDE_SUBSECTIONS']='Y';
        //для дисков легковых
        if($this->type=='d'){
            echo 1;
            $arFilter['SECTION_ID']=225;
            //$arFilter['SECTION_ID']=224; //'diski_legkovye';
        }
        if($this->type=='s'){
           // echo 2;
           // $arFilter['SECTION_ID']=49;//'shiny_legkovye_otechestvennye';
            $arFilter['SECTION_ID']=array(49,304);//'shiny_legkovye_otechestvennye';
          /*  $arFilter[]= array(
                "LOGIC" => "OR",
                array("SECTION_ID" => 49),
                array("SECTION_ID" => 304),
            ); */
        }
      //  deb($arFilter);

        $res = \CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);

        $count=0;
        while($ob = $res->GetNextElement()){
            $count++;
            if($count==100){
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
            $array_xls['PROIZVODITEL'] = $PROPERTY['PROIZVODITEL']['VALUE'];

            if($this->type=='d') {
                $array_xls['DIAMETR_SHINY'] = $PROPERTY['DIAMETR_SHINY']['VALUE'];
                $array_xls['PCD'] = $PROPERTY['PCD']['VALUE'];
                $array_xls['ET'] = $PROPERTY['ET']['VALUE'];
                $array_xls['CB'] = $PROPERTY['CB']['VALUE'];
                $array_xls['MODEL_SHINY'] = $PROPERTY['MODEL_SHINY']['VALUE'];

                $array_xls['KOL_SVOB_OSTATOK']=$PROPERTY['']['VALUE']; //ПУСТО!

            }
            if($this->type=='s') {
                $array_xls['KOD_PROIZVODITELYA'] = $PROPERTY['KOD_PROIZVODITELYA']['VALUE'];
                $array_xls['RAZMER_SHTRIKH_KOD'] = $PROPERTY['RAZMER_SHTRIKH_KOD']['VALUE'];
                $array_xls['YA_SEZONNOST'] = $PROPERTY['YA_SEZONNOST']['VALUE'];

            }

            // разобраться с отстатками
            $array_xls['KOL_OSTATOK']=$PROPERTY['']['VALUE'];// ПУСТО

            $PRICE_TYPE_ID = 3; // Оптовая цена
            //получаем различные типы цен по Id товара, нас интересует оптовая
            $rsPrices = CPrice::GetList(array(), array('PRODUCT_ID' => $arFields['ID'], 'CATALOG_GROUP_ID' => $PRICE_TYPE_ID));
            $PRICE_OPT=0;
            if ($arPrice = $rsPrices->Fetch())
            {
                $PRICE_OPT=$arPrice["PRICE"];
            }

            $array_xls['PRICE_OPT']=$PRICE_OPT;
            //эта ячейка всегда пустая- нужна для клиента

            $array_xls['ZAKAZ']='';
            $this->masForXLS[]=$array_xls;
        }

       // echo $count;
       // deb( $this->masForXLS);
    }

  public   function generateXls(){
        $this->masForXLS;

        // Создаем объект класса PHPExcel
        $xls = new PHPExcel();
        // Устанавливаем индекс активного листа
        $xls->setActiveSheetIndex(0);
        // Получаем активный лист
        $sheet = $xls->getActiveSheet();
        // Подписываем лист
        $sheet->setTitle('Выгрузка шины');

        // Вставляем текст в ячейку A1
        $sheet->setCellValue("A1", 'Выгрузка шины');
        $sheet->getStyle('A1')->getFill()->setFillType(
            PHPExcel_Style_Fill::FILL_SOLID);


        // Объединяем ячейки
       // $sheet->mergeCells('A1:H1');


      //стили для вунтернней таблицы
      $style_cell = array(
          'borders' => array(
              'allborders'=>array(
                  'style'=>PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array(
                      'rgb'=>'696969'
                  )
              )
          )
      );
      $header_cell = array(
          'fill' => array(
              'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
              'color'=>array(
                  'rgb' => 'CFCFCF'
              )
          )
      );

      $row_num=2;
      if($this->type=='d') {
          $sheet->setCellValueByColumnAndRow(0, $row_num, 'Код');
          $sheet->setCellValueByColumnAndRow(1, $row_num, 'Номенклатура');
          $sheet->getColumnDimension('B')->setWidth(80);//ширина
          $sheet->setCellValueByColumnAndRow(2, $row_num, 'Диаметр диски ');
          $sheet->setCellValueByColumnAndRow(3, $row_num, 'PCD ');
          $sheet->setCellValueByColumnAndRow(4, $row_num, 'ET');
          $sheet->setCellValueByColumnAndRow(5, $row_num, 'CB');
          $sheet->setCellValueByColumnAndRow(6, $row_num, 'Модель');
          $sheet->setCellValueByColumnAndRow(7, $row_num, 'Производитель');
          $sheet->setCellValueByColumnAndRow(8, $row_num, 'Количество остаток');
          $sheet->setCellValueByColumnAndRow(9, $row_num, 'Количество свободный остаток');
          $sheet->setCellValueByColumnAndRow(10, $row_num, 'Цена оптовая');
          $sheet->setCellValueByColumnAndRow(11, $row_num, 'Заказ');

          //вставляем фильтр
          $xls->getActiveSheet()->setAutoFilter('A2:L2');
          $sheet->getStyle('A' . $row_num . ':L' . $row_num)->applyFromArray($header_cell);
          $sheet->getStyle('A' . $row_num . ':L' . $row_num)->getAlignment()->setWrapText(true);//перенос по словам

          $sheet->getRowDimension($row_num)->setRowHeight(60);
      }
      if($this->type=='s') {
          $sheet->setCellValueByColumnAndRow(0, $row_num, 'Код');
          $sheet->setCellValueByColumnAndRow(1, $row_num, 'Код производителя');
          $sheet->setCellValueByColumnAndRow(2, $row_num, 'Размер (штрих-код)');
          $sheet->setCellValueByColumnAndRow(3, $row_num, 'я Сезонность');
          $sheet->setCellValueByColumnAndRow(4, $row_num, 'Производитель');
          $sheet->setCellValueByColumnAndRow(5, $row_num, 'Номенклатура');
          $sheet->setCellValueByColumnAndRow(6, $row_num, 'Количество');
          $sheet->setCellValueByColumnAndRow(7, $row_num, 'Цена оптовая');
          $sheet->setCellValueByColumnAndRow(8, $row_num, 'Заказ');
      }
        $row_num=3;
        foreach ( $this->masForXLS as $array_xls) {
            if($this->type=='d')
            {
                $sheet->setCellValueByColumnAndRow(0, $row_num, $array_xls['COD']);
                $sheet->setCellValueByColumnAndRow(1, $row_num, $array_xls['NOMENKL']);
                $sheet->setCellValueByColumnAndRow(2, $row_num, $array_xls['DIAMETR_SHINY']);
                $sheet->setCellValueByColumnAndRow(3, $row_num, $array_xls['PCD']);
                $sheet->setCellValueByColumnAndRow(4, $row_num, $array_xls['ET']);
                $sheet->setCellValueByColumnAndRow(5, $row_num, $array_xls['CB']);
                $sheet->setCellValueByColumnAndRow(6, $row_num, $array_xls['MODEL_SHINY']);
                $sheet->setCellValueByColumnAndRow(7, $row_num, $array_xls['PROIZVODITEL']);
                $sheet->setCellValueByColumnAndRow(8, $row_num, $array_xls['KOL_OSTATOK']);
                $sheet->setCellValueByColumnAndRow(9, $row_num, $array_xls['KOL_SVOB_OSTATOK']);
                $sheet->setCellValueByColumnAndRow(10, $row_num, $array_xls['PRICE_OPT']);
                $sheet->setCellValueByColumnAndRow(11, $row_num, $array_xls['ZAKAZ']);
                $sheet->getStyle('A' . $row_num . ':L' . $row_num)->applyFromArray($style_cell);
            }

            if($this->type=='s')
            {
                $sheet->setCellValueByColumnAndRow(0, $row_num, $array_xls['COD']);
                $sheet->setCellValueByColumnAndRow(1, $row_num, $array_xls['KOD_PROIZVODITELYA']);
                $sheet->setCellValueByColumnAndRow(2, $row_num, $array_xls['RAZMER_SHTRIKH_KOD']);
                $sheet->setCellValueByColumnAndRow(3, $row_num, $array_xls['YA_SEZONNOST']);
                $sheet->setCellValueByColumnAndRow(4, $row_num, $array_xls['PROIZVODITEL']);
                $sheet->setCellValueByColumnAndRow(5, $row_num, $array_xls['NOMENKL']);
                $sheet->setCellValueByColumnAndRow(6, $row_num, $array_xls['KOL_OSTATOK']);
                $sheet->setCellValueByColumnAndRow(7, $row_num, $array_xls['']);
                $sheet->getStyle('A' . $row_num . ':L' . $row_num)->applyFromArray($style_cell);
            }

            // Применяем выравнивание

            //$sheet->getStyleByColumnAndRow($col_num , $row_num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $row_num++;
        }

        // Выводим HTTP-заголовки
        header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
        header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Cache-Control: no-cache, must-revalidate" );
        header ( "Pragma: no-cache" );
        header ( "Content-type: application/vnd.ms-excel" );
        header ( "Content-Disposition: attachment; filename=matrix.xls" );

        // Выводим содержимое файла
        $objWriter = new PHPExcel_Writer_Excel5($xls);
        $objWriter->save('php://output');
    }
}



$action=isset($_GET['action'])?$_GET['action']:'default';
switch ($action){
    case 'default':
        echo '<a href="/load/create_xls.php?action=s">Шины</a><br/>';
        echo '<a href="/load/create_xls.php?action=d">Диски</a>';
        break;
    case 's': //шины
        $xls=new getXLS('s');
        $xls->generateXls();
        break;
    case 'd': // диски
        $xls=new getXLS('d');
        $xls->generateXls();
        break;
}


