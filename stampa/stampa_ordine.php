<?php
	require_once '../include/core.inc.php';


	// instantiate and use the dompdf class
    $serata_attuale = ottieni_data_serata_attuale();
    $link=connetti_mysql();
    $bevande_coperti= array();
    $dolci= array();
    $secondi_contorni= array();
    $primi= array();
    $antipasti= array();
    $bruschette= array();
    $piadine= array();
    $taglieri=array();
    $nuovo_indice = 0;
    $nuova=false;
    if(isset($_POST['ordini']) && is_array($_POST['ordini']) && isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])){
        $ordini = $_POST['ordini'];
        $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);

        if($_POST['indice']==0){
          $nuova=true;
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
            if($quantita <= 0) continue;

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
        if(!$nuova){
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
    @font-face {
      font-family: \'HelveticaNeue\';
      src: url(\'../fonts/HelveticaNeueLt.ttf\') format(\'truetype\');
      font-weight: normal;
      font-style: normal;
    }
    html, body {
        width: 210mm;
        height: 297mm;
        margin-top: 3mm;
        font-family: HelveticaNeue;
    }
    div.rect{
        width: 100%;
        padding-left: 10mm;
    }
    div.bevanda{
        width: 125mm;
        height: 39mm;
        float: left;
        display: block;
        padding-top: 3mm;
        padding-right: 3mm;
    }
    div.tagliere{
        width: 66mm;
        height: 39mm;
        float: right;
        display: block;
        padding-right: 3mm;
        padding-top: 3mm;
    }
    div.rect.dolce{
        height: 48mm;
        padding-top: 3mm;
    }
    div.rect.secondo{
        height: 50mm;
        padding-top: 3mm;
    }
    div.rect.primo{
        height: 46mm;
        padding-top: 3mm;
    }
    div.rect.antipasto{
        height: 50.5mm;
        padding-top: 3mm;
    }
    div.bruschette{
        width: 99mm;
        height: 55mm;
        float: left;
        display: block;
        padding-top: 3mm;
        padding-right: 3mm;
    }
    #wrapper{
      width: 100%;
    }
    div.piadine{
        width: 102mm;
        height: 55mm;
        float: right;
        display: block;
        padding-right: 3mm;
        padding-top: 3mm;
    }
    #wrapper:after{
      content: \'\';
      display: block;
      clear: both;
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
    <div id="wrapper" class="rect">
    <div class="bevanda">';
    if(!empty($bevande_coperti)){
      $html.='<div style="font-size: 12pt;">[Bevande Coperti] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
        <table class="ordini">
          <tr>
            <th>Prodotto</th>
            <th>Quantit&agrave;</th>
          </tr>';
          foreach ($bevande_coperti as $key=>$value) {
          $new_portata = preg_replace('/ FISSO$/', '', $value['p']);
            $html.='<tr>
                      <td>'.$new_portata.'</td>
                      <td>'.$value['q'].'</td>
                    </tr>';          	
          }
        $html.='</table>';
    }

    $html.='</div>
      <div class="tagliere">';
      if(!empty($piadine)){
        $html.='<div style="font-size: 10pt;">[Crepes] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
        <table class="ordini">
          <tr>
            <th>Prodotto</th>
            <th>Quantit&agrave;</th>
          </tr>';
        foreach ($piadine as $key=>$value) {
        $new_portata = preg_replace('/ FISSO$/', '', $value['p']);
          $html.='<tr>
                    <td>'.$new_portata.'</td>
                    <td>'.$value['q'].'</td>
                  </tr>';           
        }
        $html.='</table>';
      }
        
    $html.='</div></div>
    <div class="rect dolce">';
    if(!empty($dolci)){
      $html.='<div style="font-size: 12pt;">[Dolci] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
        <table class="ordini">
          <tr>
            <th>Prodotto</th>
            <th>Quantit&agrave;</th>
          </tr>';
          foreach ($dolci as $key=>$value) {
            $new_portata = preg_replace('/ FISSO$/', '', $value['p']);
            $html.='<tr>
                      <td>'.$new_portata.'</td>
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
            $new_portata = preg_replace('/ FISSO$/', '', $value['p']);
              $html.='<tr>
                        <td>'.$new_portata.'</td>
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
                $new_portata = preg_replace('/ FISSO$/', '', $value['p']);
              $html.='<tr>
                        <td>'.$new_portata.'</td>
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
          $new_portata = preg_replace('/ FISSO$/', '', $value['p']);
            $html.='<tr>
                      <td>'.$new_portata.'</td>
                      <td>'.$value['q'].'</td>
                    </tr>';          	
          }
        $html.='</table>';  
      }
      
    $html.='</div>
      <div id="wrapper" class="rect">
      <div class="bruschette">';
      if(!empty($bruschette)){
        $html.='<div style="font-size: 12pt;">[Bruschette e Crostoni] '.date_format(date_create($serata_attuale),"d/m/Y").' Tav. '.$tavolo.' Comanda '.$tavolo.'/'.$nuovo_indice.' '.$responsabile.'</div>
        <table class="ordini">
          <tr>
            <th>Prodotto</th>
            <th>Quantit&agrave;</th>
          </tr>';
        foreach ($bruschette as $key=>$value) {
        $new_portata = preg_replace('/ FISSO$/', '', $value['p']);
          $html.='<tr>
                    <td>'.$new_portata.'</td>
                    <td>'.$value['q'].'</td>
                  </tr>';          	
        }
        $html.='</table>';  
      }
        
      $html.='</div>
      <div class="piadine">';
      
        
      $html.='</div>
      </div>
  </body>
</html>
';
    
echo $html;


  }
     

	
?>