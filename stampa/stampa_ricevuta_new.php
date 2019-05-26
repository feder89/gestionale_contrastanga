<?php
	require_once '../include/core.inc.php';
  ini_set('max_execution_time', 70);

	// instantiate and use the dompdf class
    $serata_attuale = ottieni_data_serata_attuale();
    $tavolo=0;
    $indice=0;
    $num_comanda=0;
    if(isset($_POST['tavolo']) && is_numeric($_POST['tavolo']) && isset($_POST['indice']) && is_numeric($_POST['indice'])){
        $tavolo=$_POST['tavolo'];
        $indice=$_POST['indice'];
    }else{
      echo '#error#Errore durante la stampa1';
      die();
    }
    $query="SELECT * FROM Ordini o
            INNER JOIN Portata p
            ON p.nome_portata=o.portata
            WHERE tavolo=$tavolo AND indice=$indice AND serata='$serata_attuale'
            ORDER BY FIELD(p.categoria, 'pane e coperto', 'antipasto', 'bruschette e crostoni', 'primo', 'secondo', 'piadina', 'contorno', 'dolce', 'bevanda')";
    
    $link=connetti_mysql();
    if(!($res=esegui_query($link,$query))){
        echo '#error#Errore durante la stampa2';
        disconnetti_mysql($link);
        die();
    }
    $row_cnt = mysqli_num_rows($res);
    $totale=0;
    $sconto_manuale=0;
    $coperti=0;
    $soci=0;
    $menu_fisso=0;
    $prezzo_menu_fisso;
    $query_soci = "SELECT * FROM Comande c
                  INNER JOIN Menu m ON c.menu=m.nome_menu
                  WHERE tavolo=$tavolo AND indice=$indice AND serata='$serata_attuale'";
                  
    if(!($res2 = mysqli_query($link, $query_soci))){
        echo '#error#Errore durante la stampa3';
        disconnetti_mysql($link);
        die();
    }
    elseif(mysqli_num_rows($res2)>=1){
        $row2 = mysqli_fetch_assoc($res2);
        $soci = $row2['numero_soci'];
        $sconto_manuale = $row2['sconto_manuale'];
        switch($row2['fisso']){
          case 0:
            $menu_fisso=0;
            break;
          case 1:
            $menu_fisso=1;
            $prezzo_menu_fisso=$row2['prezzo_fisso'];
            break;
        }
        if(!is_null($row2['num_comanda'])){
            $num_comanda = $row2['num_comanda'];
        }else{
            $query_num_comanda="SELECT MAX(num_comanda) AS max_num_com FROM Comande WHERE serata='$serata_attuale'";
            if(!($res3 = mysqli_query($link, $query_num_comanda))){
                echo '#error#Errore durante la stampa3';
                disconnetti_mysql($link);
                die();
            }else{
                $row3 = mysqli_fetch_assoc($res3);
                if(!is_null($row3['max_num_com'])){
                    $num_comanda = $row3['max_num_com']+1;
                }else{
                    $num_comanda = 1;
                }
            }
            $query_update_num_comanda="UPDATE Comande SET num_comanda=$num_comanda WHERE serata='$serata_attuale' AND tavolo=$tavolo AND indice=$indice";
            if(!mysqli_query($link, $query_update_num_comanda)){
                echo '#error#Errore durante la stampa3';
                disconnetti_mysql($link);
                die();
            }
        }
    }
    else{
    	echo '#error#Errore durante la stampa';
        disconnetti_mysql($link);
        die();
    }
    
    if ($row_cnt <=20){    

	$html='<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <style type="text/css">
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
        max-width: 8cm;
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
      max-width: 72mm;
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
  <body>
    <div class="contenitore">
      <div class="prodotti">
        <table class="product" border="0">
          <tr class="header">
              <th>Prodotto</th>
              <th>Q.t&agrave;</th>
              <th>Prezzo</th>
              <th>Totale</th>
          </tr>';
    }elseif ($row_cnt <=30) {
       $html='<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <style type="text/css">
    @font-face {
      font-family: \'HelveticaNeue\';
      src: url(\'../fonts/Helvetica/HelveticaNeueLt.ttf\') format(\'truetype\');
      font-weight: normal;
      font-style: normal;
    }
    html, body{
      width: 210mm;
      height: 280mm;
      font-family: HelveticaNeue;
    }
    th, h2{
        font-family: Helvetica;
        font-weight: bold;
    }
    div.contenitore{
      width: 150mm;
      font-family: Helvetica;
      height: 150mm;
      vertical-align: top;
      transform:rotate(90deg);

    }
    div.prodotti{
      float: left;
      width: 75mm;
      padding-top: 37mm;
      padding-left: 5mm;
    }
    div.info{
      width: 52mm;
      float: left;
      padding-top: 37mm;
      padding-right: 5mm;
      padding-left: 3mm;
    }
    table.product{
        font-size: 9pt;
        text-align: center;
    }
    tr.header th:first-child{
        text-align: left;
        max-width: 8cm;
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
      max-width: 72mm;
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
    table.product tr:first-child{
        line-height: 3mm;
    }
    table.product tr:not(:first-child){
        font-size: 7pt;
        line-height:2.2mm;
    }
    
   </style>
  </head>
  <body>
    <div class="contenitore">
      <div class="prodotti">
        <table class="product" border="0">
          <tr class="header">
              <th>Prodotto</th>
              <th>Q.t&agrave;</th>
              <th>Prezzo</th>
              <th>Totale</th>
          </tr>'; 
    }else {
       $html='<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <style type="text/css">
    @font-face {
      font-family: \'HelveticaNeue\';
      src: url(\'../fonts/Helvetica/HelveticaNeueLt.ttf\') format(\'truetype\');
      font-weight: normal;
      font-style: normal;
    }
    html, body{
      width: 210mm;
      height: 280mm;
      font-family: HelveticaNeue;
    }
    th, h2{
        font-family: Helvetica;
        font-weight: bold;
    }
    div.contenitore{
      width: 150mm;
      font-family: Helvetica;
      height: 150mm;
      vertical-align: top;
      transform:rotate(90deg);

    }
    div.prodotti{
      float: left;
      width: 75mm;
      padding-top: 37mm;
      padding-left: 5mm;
    }
    div.info{
      width: 52mm;
      float: left;
      padding-top: 37mm;
      padding-right: 5mm;
      padding-left: 3mm;
    }
    table.product{
        font-size: 9pt;
        text-align: center;
    }
    tr.header th:first-child{
        text-align: left;
        max-width: 8cm;
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
      max-width: 72mm;
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
    table.product tr:first-child{
        line-height: 3mm;
    }
    table.product tr:not(:first-child){
        font-size: 6pt;
        line-height:1.8mm;
    }
    
    </style>
  </head>
  <body>
    <div class="contenitore">
      <div class="prodotti">
        <table class="product" border="0">
          <tr class="header">
              <th>Prodotto</th>
              <th>Q.t&agrave;</th>
              <th>Prezzo</th>
              <th>Totale</th>
          </tr>';
    }
                        $html_backup = '';
                        $html_backup_tot = '';
                        $tot_acqua_da_pagare=0;
                        $tot_vino_da_pagare=0;
                        if($menu_fisso==0){
                          while($row=mysqli_fetch_assoc($res)){
                                $portata=$row['portata'];
                                $quantita=$row['quantita'];
                                $prezzo=number_format($row['prezzo_finale'], 2, '.', ' ');
                                $prezzoquantita=$row['prezzo_finale']*$row['quantita'];
                                $prezzoquantita=number_format($prezzoquantita, 2, '.', ' ');
                                
                                $html_backup.='<tr><td>'.$portata.'</td>';
                                $html_backup.='<td>' .$quantita.'</td>';
                                $html_backup.='<td>&#8364; ' .$prezzo.'</td>';
                                $html_backup.='<td>&#8364; ' .$prezzoquantita.'</td></tr>';
                                $totale = $totale + ($row['prezzo_finale']*$row['quantita']);
                                $totale=number_format($totale, 2, '.', ' ');
                              if($row['portata'] == "Pane e Coperto"){
                                $coperti=$row['quantita'];
                              }        
                            }
                            $html .= $html_backup;
                            
                        }elseif($menu_fisso==1){
                            $acqua_ordinata=0;
                            //$acqua_da_pagare=0;
                            $vino_ordinato=0;
                            //$vino_da_pagare=0;
                            $acqua=array();
                            $vino=array();
                            while($row=mysqli_fetch_assoc($res)){  
                                $quant = $row['quantita'];
                                if($row['portata'] == "Pane e Coperto"){
                                    $coperti=$quant;
                                }
                                if(startsWith(strtolower($row['portata']), 'acqua')){
                                    $acqua_compresa=ceil($coperti/2);
                                    if($acqua_ordinata==0){
                                      //primo tipo di acqua, paga tutta quella in piu rispetto a quella compresa
                                      $acqua_ordinata+=$quant;
                                      $acqua_da_pagare=$acqua_ordinata-$acqua_compresa;
                                      if( $acqua_da_pagare >= 1 ){
                                          $tot_tmp = $row['prezzo_finale']*$acqua_da_pagare;
                                          $prezzo_ac=number_format($row['prezzo_finale'], 2, '.', ' ');
                                          $acqua[]=array('tipo' => $row['portata'], 'num' => $acqua_da_pagare, 'prezzo' => $prezzo_ac, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
                                      } 
                                    }                 
                                    else {

                                      if( $acqua_ordinata < $acqua_compresa){
                                        //forse c'è qualcosa da pagare
                                        $i=1;
                                        $acqua_da_pagare=0;
                                        while( $i <= $quant ){
                                          if($i + $acqua_ordinata > $acqua_compresa) $acqua_da_pagare++;
                                          $i++;
                                        }
                                        if( $acqua_da_pagare >= 1 ){
                                            $tot_tmp = $row['prezzo_finale']*$acqua_da_pagare;
                                            $prezzo_ac=number_format($row['prezzo_finale'], 2, '.', ' ');
                                            $acqua[]=array('tipo' => $row['portata'], 'num' => $acqua_da_pagare, 'prezzo' => $prezzo_ac, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
                                        }
                                        $acqua_ordinata+=$quant;
                                      }
                                      else {
                                        $tot_tmp = $row['prezzo_finale']*$quant;
                                        $prezzo_ac=number_format($row['prezzo_finale'], 2, '.', ' ');
                                        $acqua[]=array('tipo' => $row['portata'], 'num' => $quant, 'prezzo' => $prezzo_ac, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
                                      }
                                    }
                                }
                                if(startsWith(strtolower($row['portata']), 'vino')){
                                  $vino_compreso=round($coperti/3);
                                  if($vino_ordinato==0){
                                    //primo tipo di vino, paga tutta quella in piu rispetto a quella compreso
                                    $vino_ordinato+=$quant;
                                    $vino_da_pagare=$vino_ordinato-$vino_compreso;
                                    if( $vino_da_pagare >= 1 ){
                                        $tot_tmp = $row['prezzo_finale']*$vino_da_pagare;
                                        $prezzo_vi=number_format($row['prezzo_finale'], 2, '.', ' ');
                                        //$prezzoquantita_vi=$row['prezzo_finale']*($row['quantita']+$vino_da_pagare);
                                        //$prezzoquantita_vi=number_format($prezzoquantita_vi, 2, '.', ' ');
                                        $vino[]=array('tipo' => $row['portata'], 'num' => $vino_da_pagare, 'prezzo' => $prezzo_vi, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
                                    } 
                                  }                 
                                  else {
                                    if( $vino_ordinato < $vino_compreso){
                                      //forse c'è qualcosa da pagare
                                      $i=1;
                                      $vino_da_pagare=0;
                                      while( $i <= $quant ){
                                        if($i + $vino_ordinato > $vino_compreso) $vino_da_pagare++;
                                        $i++;
                                      }
                                      if( $vino_da_pagare >= 1 ){
                                        $tot_tmp = $row['prezzo_finale']*$vino_da_pagare;
                                        $prezzo_vi=number_format($row['prezzo_finale'], 2, '.', ' ');
                                        $vino[]=array('tipo' => $row['portata'], 'num' => $vino_da_pagare, 'prezzo' => $prezzo_vi, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
                                      }
                                      $vino_ordinato+=$quant;
                                    }
                                    else {
                                        $tot_tmp = $row['prezzo_finale']*$quant;
                                        $prezzo_ac=number_format($row['prezzo_finale'], 2, '.', ' ');
                                        $acqua[]=array('tipo' => $row['portata'], 'num' => $quant, 'prezzo' => $prezzo_ac, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
                                      }
                                  }
                                }
                                //$prezzo_menu_fisso=number_format($prezzo_menu_fisso, 2, ',', ' ');
                                
                                //$totale=number_format($totale, 2, ',', ' ');
                            }

                            //aggiorna il totale
                            $totale=$prezzo_menu_fisso*$coperti;

                            $html_backup.='<tr><td>Men&ugrave; Fisso</td>';
                            $html_backup.='<td>' .$coperti.'</td>';
                            $html_backup.='<td>&#8364; ' .number_format($prezzo_menu_fisso, 2, '.', ' ').'</td>';
                            $html_backup.='<td>&#8364; ' .number_format($totale, 2, '.', ' ').'</td></tr>';
                            if(!empty($acqua)){
                                foreach ($acqua as $key => $value) {
                                    $html_backup.='<tr><td>'.$value['tipo'].'</td>';
                                    $html_backup.='<td>' .$value['num'].'</td>';
                                    $html_backup.='<td>&#8364; ' .$value['prezzo'].'</td>';
                                    $html_backup.='<td>&#8364; ' .$value['prezzo_fin'].'</td></tr>';
                                    $tot_acqua_da_pagare=$tot_acqua_da_pagare+$value['prezzo_fin'];
                                }
                            }
                            if(!empty($vino)){
                                foreach ($vino as $key => $value) {
                                    $html_backup.='<tr><td>'.$value['tipo'].'</td>';
                                    $html_backup.='<td>' .$value['num'].'</td>';
                                    $html_backup.='<td>&#8364; ' .$value['prezzo'].'</td>';
                                    $html_backup.='<td>&#8364; ' .$value['prezzo_fin'].'</td></tr>';
                                    $tot_vino_da_pagare=$tot_vino_da_pagare+$value['prezzo_fin'];
                                }
                            }
                            $html .= $html_backup;
                            
                        }
                        $totale=$totale+$tot_acqua_da_pagare+$tot_vino_da_pagare;
                        //$totale=number_format($totale, 2, '.', ' ');
                        $totale_sconto_soci=($totale/$coperti)*(0.1*$soci);
                        $totale_fin=$totale-$totale_sconto_soci-$sconto_manuale;
                        if($totale_fin<0){
                            $totale_fin=0;
                        }
                        $totale_fin = round($totale_fin * 2, 0)/2;
                        $tot_persona=$totale_fin/$coperti;
                        $totale_fin=number_format($totale_fin, 2, '.', ' ');
                        $totale=number_format($totale, 2, '.', ' ');
                        mysqli_free_result($res);
                    $html.='</table>
                    </div>
                      <div class="info">
                        <!--<h2 class="comanda">Rione Contrastanga<br />Taverna Sette Selle</h2>-->
                        <!--<text class="info">Comanda n. '.$num_comanda.' Tavolo: '.$tavolo.'/'.$indice.'</text><br />
                        <text class="standard">del giorno '.date_format(date_create($serata_attuale),"d/m/Y").'</text>-->';
                        $html_backup_tot.='<p class="totale"><span class="sx">Totale prodotti </span><span class="dx">&#8364; '.$totale.'</span><br/>';
                        $html_backup_tot.='<span class="sx">Numero soci '.$soci.'<br /> soci con sconto 10%</span><br />';
                        if ($sconto_manuale>0) {
                            $html_backup_tot.='<span class="sx">Sconto &#8364; '.$sconto_manuale.'</span><br />';
                        }
                        $html_backup_tot.='<br /><span class="sx">Totale Comanda </span><span class="dx">&#8364; '.$totale_fin.'</span>';
                        $html.=$html_backup_tot.'</p>
                      </div>
                    </div>
                    <div class="contenitore">
                    <div class="prodotti">
                      <table class="product" border="0">
                          <tr class="header">
                              <th>Prodotto</th>
                              <th>Q.t&agrave;</th>
                              <th>Prezzo</th>
                              <th>Totale</th>
                          </tr>
                        '.$html_backup.'
                      </table>
                </div>
                      <div class="info">
                        <!--<h2 class="comanda">Rione Contrastanga<br />Taverna Sette Selle</h2>-->
                        <!--<text class="info">Comanda n. '.$num_comanda.' Tavolo: '.$tavolo.'/'.$indice.'</text><br />
                        <text class="standard">del giorno '.date_format(date_create($serata_attuale),"d/m/Y").'</text>-->'
                        .$html_backup_tot.'</p>            
</body>
</html>
';

    echo $html;
    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
?>