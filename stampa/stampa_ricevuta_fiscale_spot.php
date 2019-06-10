<?php
	require_once '../include/core.inc.php';
  ini_set('max_execution_time', 70);

	// instantiate and use the dompdf class
    $serata_attuale = ottieni_data_serata_attuale();
    $quantita=0;
    $prezzo=0;
    $totale=0;
    if(isset($_POST['quantita']) && is_numeric($_POST['quantita']) && isset($_POST['prezzo']) && is_numeric($_POST['prezzo'])
      && isset($_POST['pasto']) && !empty($_POST['pasto'])){
        $quantita=$_POST['quantita'];
        $prezzo=$_POST['prezzo'];
        $pasto=$_POST['pasto'];
        $totale=$_POST['quantita']*$_POST['prezzo'];
        $totale=number_format($totale, 2, '.', ' ');
        $prezzo=number_format($prezzo, 2, '.', ' ');
    }else{
      echo '#error#Errore durante la stampa1';
      die();
    }
      

	$html='<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <style type="text/css">
    @page {margin: 0;}
    @font-face {
      font-family: \'HelveticaNeue\';
      src: url(\'../fonts/Helvetica/HelveticaNeueLt.ttf\') format(\'truetype\');
      font-weight: normal;
      font-style: normal;
    }
    html, body{
      width: 210mm;
      height: 269mm;
      font-family: HelveticaNeue;
    }
    th, h2{
        font-family: Helvetica;
        font-weight: bold;
    }
    div.contenitore{
      width: 145mm;
      font-family: Helvetica;
      height: 145mm;
      vertical-align: top;
      transform:rotate(90deg);

    }
    div.prodotti{
      float: left;
      width: 75mm;
      padding-left: 5mm;
    }
    div.info{
      width: 52mm;
      float: left;
      padding-right: 5mm;
      padding-left: 3mm;
    }
    table.product{
        font-size: 11pt;
        text-align: center;
    }
    tr.header th:first-child{
        text-align: left;
        max-width: 12cm;
    }
    tr.header th:not(:first-child){
        text-align: center;
    }
    table.product tr td{
        text-align: center;
        padding: 0mm 2mm;
        white-space: nowrap;
        word-break: break-word;
    }
     table.product tr td:first-child{
      max-width: 110mm;
        padding-right: 2mm;
        padding-left: 0mm;
        text-align: left;
        white-space: normal !important; 
    }
    table.product-sotto tr td:last-child, table.product tr td:last-child,
    table.product-sotto tr th:last-child, table.product tr th:last-child{
        text-align:right;
        padding-right:0mm;
    }
    p.totale{
        position: relative;
        font-size: 12pt;
        margin-top: 56mm;
        word-break: break-word;
    }
    p.totale span.sx{
        position: absolute;
        left: 0;
    }
    p.totale span.dx{
        position: absolute;
        right: 0;
    }

    table.product tr:not(:first-child){
        font-size: 9pt;
        line-height:3.3mm;
    }
    
   </style>
  </head>
  <body>';
    $table='<div class="contenitore">
      <div class="prodotti">
        <table class="product" border="0">
          <tr class="header">
              <th>Prodotto</th>
              <th>Q.t&agrave;</th>
              <th>Prezzo</th>
              <th>Totale</th>
          </tr>
          <tr class="header">
              <td>'.$pasto.'</td>
              <td>'.$quantita.'</td>
              <td>&#8364; '.$prezzo.'</td>
              <td>&#8364; '.$totale.'</td>
          </tr>
        </table>
      </div>
    </div>';
    $html.=$table;          
$html.=$table.'</body>
</html>
';

    echo $html;
    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
?>