<?php
	require_once '../include/core.inc.php';
	include_once '../assets/dompdf-master/autoload.inc.php';
	use Dompdf\Dompdf;

	// instantiate and use the dompdf class
    $serata_attuale = ottieni_data_serata_attuale();
	  $dompdf = new Dompdf();
    $link=connetti_mysql();
    $bevande_coperti= array();
    $dolci= array();
    $secondi_contorni= array();
    $primi= array();
    $antipasti= array();
    $bruschette= array();
    $piadine= array();
    $nuovo_indice = 0;
    if(isset($_POST['ordini']) && is_array($_POST['ordini']) && isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])){
        $ordini = $_POST['ordini'];
        $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);

        if(is_null($_POST['indice'])){
            $query_check = "SELECT MAX(indice) AS indice_max FROM Comande WHERE serata = '$serata_attuale' AND tavolo = $tavolo GROUP BY serata, tavolo";
            if(!($res = mysqli_query($link, $query_check))){
                echo '#error#Errore durante la stampa';
                disconnetti_mysql($link);
                die();
            }
            if(mysqli_num_rows($res)>=1){
                $row = mysqli_fetch_assoc($res);
                $nuovo_indice = $row['indice_max']+1;
            }
            else{
                $nuovo_indice = 1;
            }
        }
        else {
            $nuovo_indice = $_POST['indice'];
        }
        
        
        foreach ($ordini as $key => $ordine) {
            $portata = mysqli_real_escape_string($link, $ordine[0]);
            $quantita = mysqli_real_escape_string($link, $ordine[1]);

            $query_cat="SELECT * FROM Portata WHERE nome_portata='$portata'";

            if(!($res1 = mysqli_query($link, $query_cat))){
                echo '#error#Errore durante la stampa';
                disconnetti_mysql($link);
                die();
            }elseif (mysqli_num_rows($res1)>=1) {
                $row1 = mysqli_fetch_assoc($res1);
                switch ($row1['categoria']) {
                    case 'pane e coperto':
                    case 'bevanda':
                        $bevande_coperti[]= array("p" => $portata, "q" => $quantita);
                        break;
                    case 'dolce':
                        $dolci[]=array("p" => $portata, "q" => $quantita);
                        break;
                    case 'secondo':
                    case 'contorno':
                        $secondi_contorni[]=array("p" => $portata, "q" => $quantita);
                        break;
                    case 'primo':
                        $primi[]=array("p" => $portata, "q" => $quantita);
                        break;
                    case 'antipasto':
                        $antipasti[]=array("p" => $portata, "q" => $quantita);
                        break;
                    case 'bruschette e crostoni':
                        $bruschette[]=array("p" => $portata, "q" => $quantita);
                        break;
                    case 'piadina':
                        $piadine[]=array("p" => $portata, "q" => $quantita);
                        break;
                }
            }
        }

        $query_resp=null;
        if($nuovo_indice>1){
          $query_resp = "SELECT * FROM Comande WHERE serata = '$serata_attuale' AND tavolo = $tavolo AND indice=$nuovo_indice";
        }else{
          $query_resp="SELECT * FROM ResponsabiliSerata rs
                        WHERE rs.tavolo=$tavolo AND rs.serata='$serata_attuale' AND rs.numero_progressivo = (
                                  SELECT MAX(rs2.numero_progressivo)
                                  FROM ResponsabiliSerata rs2
                                  WHERE rs2.serata='$serata_attuale' AND rs2.tavolo=$tavolo
                                  GROUP BY rs2.serata, rs2.tavolo)";
        }
        
        if(!($res2 = mysqli_query($link, $query_resp))){
                echo '#error#Errore durante la stampa';
                disconnetti_mysql($link);
                die();
            }
            if(mysqli_num_rows($res2)>=1){
                $row2 = mysqli_fetch_assoc($res2);
                $responsabile = $row2['responsabile'];
            }
            else{
              $responsabile = '-';
            }
        $html='<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <title></title>
  <style type="text/css">
    @page {size: 210mm 297mm; margin: 0;}
    html, body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        font-family: Helvetica;
    }
    div.rect{
        width: 100%;
        padding-left: 10mm;
    }
    div.rect.bevanda{
        height: 42mm;
    }
    div.rect.dolce{
        height: 48mm;
    }
    div.rect.secondo{
        height: 51mm;
    }
    div.rect.primo{
        height: 45mm;
    }
    div.rect.antipasto{
        height: 50.5mm;
    }
    div.rect.crostoni-dessert{
        position: relative;
        height: 55mm;
    }
    div.bruschette{
        width:50%;
        position: absolute;
        left: 0;
    }
    div.piadine{
        width: 50%;
        position: absolute;
        right: 0;
    }
    table{
      text-align: center;
      font-size: 10pt;
      padding-top: 0;
      margin-top: 0;
    }
    table tr td:first-child{
      width: 70mm;
      text-align: left;
    }
    table tr th:first-child{
      text-align: left;
    }
    table.ordini tr{
      line-height:3mm;
    }
    table.ordini tr:first-child{
      line-height:4mm;
    }
  </style>
  </head>
  <body>
    <div class="rect bevanda">';
    if(!empty($bevande_coperti)){
      $html.='<div style="font-size: 12pt;">[Bevande Coperti] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
        <table class="ordini">
          <tr>
            <th>Prodotto</th>
            <th>Quantit&agrave;</th>
          </tr>';
          foreach ($bevande_coperti as $key=>$value) {
            $html.='<tr>
                      <td>'.$value['p'].'</td>
                      <td>'.$value['q'].'</td>
                    </tr>';          	
          }
        $html.='</table>';
    }
        
    $html.='</div>
    <div class="rect dolce">';
    if(!empty($dolci)){
      $html.='<div style="font-size: 12pt;">[Dolci] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
        <table class="ordini">
          <tr>
            <th>Prodotto</th>
            <th>Quantit&agrave;</th>
          </tr>';
          foreach ($dolci as $key=>$value) {
            $html.='<tr>
                      <td>'.$value['p'].'</td>
                      <td>'.$value['q'].'</td>
                    </tr>';          	
          }
        $html.='</table>';
    }
      
    $html.='</div>
    <div class="rect secondo">';
    if(!empty($secondi_contorni)){
      $html.='<div style="font-size: 12pt;">[Secondi Contorni] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
          <table class="ordini">
            <tr>
              <th>Prodotto</th>
              <th>Quantit&agrave;</th>
            </tr>';
            foreach ($secondi_contorni as $key=>$value) {
              $html.='<tr>
                        <td>'.$value['p'].'</td>
                        <td>'.$value['q'].'</td>
                      </tr>';          	
            }
        $html.='</table>';
    }
      
    $html.='</div>
    <div class="rect primo">';
    if(!empty($primi)){
      $html.='<div style="font-size: 12pt;">[Primi Piatti] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
          <table class="ordini">
            <tr>
              <th>Prodotto</th>
              <th>Quantit&agrave;</th>
            </tr>';
            foreach ($primi as $key=>$value) {
              $html.='<tr>
                        <td>'.$value['p'].'</td>
                        <td>'.$value['q'].'</td>
                      </tr>';          	
            }
        $html.='</table>';
    }
      
    $html.='</div>
    <div class="rect antipasto">';
      if(!empty($antipasti)){
        $html.='<div style="font-size: 12pt;">[Antipasti] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
        <table class="ordini">
          <tr>
            <th>Prodotto</th>
            <th>Quantit&agrave;</th>
          </tr>';
          foreach ($antipasti as $key=>$value) {
            $html.='<tr>
                      <td>'.$value['p'].'</td>
                      <td>'.$value['q'].'</td>
                    </tr>';          	
          }
        $html.='</table>';  
      }
      
    $html.='</div>
    <div class="rect crostoni-dessert">
      <div class="bruschette">';
      if(!empty($bruschette)){
        $html.='<div style="font-size: 12pt;">[Bruschette e Crostoni] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
        <table class="ordini">
          <tr>
            <th>Prodotto</th>
            <th>Quantit&agrave;</th>
          </tr>';
        foreach ($bruschette as $key=>$value) {
          $html.='<tr>
                    <td>'.$value['p'].'</td>
                    <td>'.$value['q'].'</td>
                  </tr>';          	
        }
        $html.='</table>';  
      }
        
      $html.='</div>
      <div class="piadine">';
      if(!empty($piadine)){
        $html.='<div style="font-size: 12pt;">[Piadine] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
        <table class="ordini">
          <tr>
            <th>Prodotto</th>
            <th>Quantit&agrave;</th>
          </tr>';
        foreach ($piadine as $key=>$value) {
          $html.='<tr>
                    <td>'.$value['p'].'</td>
                    <td>'.$value['q'].'</td>
                  </tr>';          	
        }
        $html.='</table>';
      }
        
      $html.='</div>
    </div>

  </body>
</html>
';
    
  $dompdf->loadHtml($html);

  // (Optional) Setup the paper size and orientation
  $dompdf->setPaper('A4', 'potrait');

  // Render the HTML as PDF
  $dompdf->render();
    //$pdf = $dompdf->output();
  //$dompdf->stream('test.pdf', array("Attachment" => false));
  $file_to_save = 'test.pdf';
  file_put_contents($file_to_save, $dompdf->output());

  // Output the generated PDF to Browser
  //
    /*$handle = printer_open();
    
    printer_start_doc($handle, $pdf);
    printer_start_page($handle);

    printer_end_page($handle);
    printer_end_doc($handle);
    printer_close($handle);*/


  }
     

	
?>