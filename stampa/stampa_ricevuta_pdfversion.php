<?php
	require_once '../include/core.inc.php';
	include_once '../assets/dompdf-master/autoload.inc.php';
	use Dompdf\Dompdf;

	// instantiate and use the dompdf class
    $serata_attuale = ottieni_data_serata_attuale();
	$dompdf = new Dompdf();
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
    
      

	$html='<html>
<head>
<style>
    
    body{
        font-family: DejaVuSerif;
    }
    table.contenitore{
        width: 100%;
        height: 132mm;
    }
    table.contenitore tr td.conto{
        vertical-align: top;
        height: 130mm;
        width: 10cm;
        padding-top: 0;
    }
    table.product{
        margin-top: 32mm;
        font-size: 11pt;
        margin-left: 5mm;
        max-width: 9cm;
        text-align: center;
    }
    table.product-sotto{
        margin-top: 47mm;
        font-size: 11pt;
        margin-left: 5mm;
        max-width: 9cm;
        text-align: center;
    }
    tr.header th:first-child{
        text-align: left;
        max-width: 8cm;
    }
    tr.header th:not(:first-child){
        text-align: center;
    }
    table.product td{
        text-align: center;
        padding: 0mm 2mm;
        white-space: nowrap;
    }
    table.product-sotto td{
        text-align: center;
        padding: 0mm 2mm;
        white-space: nowrap;
    }
    table.product tr td:first-child{
        max-width: 8cm;
        padding-right: 2mm;
        padding-left: 0mm;
        text-align: left;
    }
    table.product-sotto tr td:first-child{
        max-width: 8cm;
        padding-right: 2mm;
        padding-left: 0mm;
        text-align: left;
    }
    table.product-sotto tr td:last-child, table.product tr td:last-child,
    table.product-sotto tr th:last-child, table.product tr th:last-child{
        text-align:right;
        padding-right:0mm;
    }


    /*.container{
        width: 100%;
        height: 50%;
        border: 1px solid red;
        display: block;
    }*/
    .logo{
        margin: 8mm 0 0 8mm;
    }
    td.info{
        padding-left: 10mm;
        padding-top: 0;
        text-align:left;
    }
    td.info-info{
        margin-top: 12mm;
        padding-left: 10mm;
        padding-top: 14mm;
        text-align:left;
        vertical-align:top;
    }
    p.totale{
        position: relative;
    }
    p.totale span.sx{
        position: absolute;
        left: 0;
    }
    p.totale span.dx{
        position: absolute;
        right: 0;
    }


    table.product tr{
        line-height:4mm;
    }
    table.product tr, table.product-sotto tr{
        line-height:4mm;
    }
    
</style>
</head>

<body>
    <!--<div class="container">-->
        <table class="contenitore" border="0">
            <tr>
                <td rowspan="2" class="conto">
                    <table class="product" border="0">
                        <tr class="header">
                            <th>Prodotto</th>
                            <th>Q.t&agrave;</th>
                            <th>Prezzo</th>
                            <th>Totale</th>
                        </tr>';
                        $html_backup = '';
                        $html_backup_tot = '';
                        $tot_acqua_da_pagare=0;
                        $tot_vino_da_pagare=0;
                        if($menu_fisso==0){
                          while($row=mysqli_fetch_assoc($res)){
                                $portata=$row['portata'];
                                $quantita=$row['quantita'];
                                $prezzo=number_format($row['prezzo_finale'], 2, ',', ' ');
                                $prezzoquantita=$row['prezzo_finale']*$row['quantita'];
                                $prezzoquantita=number_format($prezzoquantita, 2, ',', ' ');
                                
                                $html_backup.='<tr><td>'.$portata.'</td>';
                                $html_backup.='<td>' .$quantita.'</td>';
                                $html_backup.='<td>€ ' .$prezzo.'</td>';
                                $html_backup.='<td>€ ' .$prezzoquantita.'</td></tr>';
                                $totale = $totale + ($row['prezzo_finale']*$row['quantita']);
                                $totale=number_format($totale, 2, ',', ' ');
                              if($row['portata'] == "Pane e Coperto"){
                                $coperti=$row['quantita'];
                              }        
                            }
                            $html .= $html_backup;
                            
                        }elseif($menu_fisso==1){
                            $acqua_ordinata=0;
                            $acqua_da_pagare=0;
                            $vino_ordinato=0;
                            $vino_da_pagare=0;
                            $acqua=array();
                            $vino=array();
                            while($row=mysqli_fetch_assoc($res)){  
                                if($row['portata'] == "Pane e Coperto"){
                                    $coperti=$row['quantita'];
                                }
                                if(startsWith($row['portata'], 'Acqua')){
                                    $acqua_compresa=ceil($coperti/2);
                                    $acqua_ordinata=$acqua_ordinata+$row['quantita'];
                                    $acqua_da_pagare=$acqua_ordinata-$acqua_compresa;
                                    if ($acqua_da_pagare >= 1) {
                                        while($acqua_da_pagare>0){
                                            $acqua_da_pagare -= $row['quantita'];
                                            if($acqua_da_pagare<=0){
                                                $prezzo_ac=number_format($row['prezzo_finale'], 2, ',', ' ');
                                                $prezzoquantita_ac=$row['prezzo_finale']*($row['quantita']+$acqua_da_pagare);
                                                $prezzoquantita_ac=number_format($prezzoquantita_ac, 2, ',', ' ');
                                                $acqua[]=array('tipo' => $row['portata'], 'num' => ($row['quantita']+$acqua_da_pagare), 'prezzo' => $prezzo_ac, 'prezzo_fin' => $prezzoquantita_ac);
                                            }
                                        }
                                    }
                                }
                                if(startsWith($row['portata'], 'Vino')){
                                    $vino_compreso=round($coperti/3);
                                    $vino_ordinato=$vino_ordinato+$row['quantita'];
                                    $vino_da_pagare=$vino_ordinato-$vino_compreso;
                                    if ($vino_da_pagare >= 1) {
                                        while($vino_da_pagare>0){
                                            $vino_da_pagare -= $row['quantita'];
                                            if($vino_da_pagare<=0){
                                                $prezzo_vi=number_format($row['prezzo_finale'], 2, ',', ' ');
                                                $prezzoquantita_vi=$row['prezzo_finale']*($row['quantita']+$vino_da_pagare);
                                                $prezzoquantita_vi=number_format($prezzoquantita_vi, 2, ',', ' ');
                                                $vino[]=array('tipo' => $row['portata'], 'num' => ($row['quantita']+$vino_da_pagare), 'prezzo' => $prezzo_vi, 'prezzo_fin' => $prezzoquantita_vi);
                                            }
                                        }
                                        
                                    }
                                }
                                $prezzo_menu_fisso=number_format($prezzo_menu_fisso, 2, ',', ' ');
                                $totale=$prezzo_menu_fisso*$coperti;
                                $totale=number_format($totale, 2, ',', ' ');
                            }
                            $html_backup.='<tr><td>Men&ugrave; Fisso</td>';
                            $html_backup.='<td>' .$coperti.'</td>';
                            $html_backup.='<td>€ ' .$prezzo_menu_fisso.'</td>';
                            $html_backup.='<td>€ ' .$totale.'</td></tr>';
                            if(!empty($acqua)){
                                foreach ($acqua as $key => $value) {
                                    $html_backup.='<tr><td>'.$value['tipo'].'</td>';
                                    $html_backup.='<td>' .$value['num'].'</td>';
                                    $html_backup.='<td>€ ' .$value['prezzo'].'</td>';
                                    $html_backup.='<td>€ ' .$value['prezzo_fin'].'</td></tr>';
                                    $tot_acqua_da_pagare=+$tot_acqua_da_pagare+$value['prezzo_fin'];
                                }
                            }
                            if(!empty($vino)){
                                foreach ($vino as $key => $value) {
                                    $html_backup.='<tr><td>'.$value['tipo'].'</td>';
                                    $html_backup.='<td>' .$value['num'].'</td>';
                                    $html_backup.='<td>€ ' .$value['prezzo'].'</td>';
                                    $html_backup.='<td>€ ' .$value['prezzo_fin'].'</td></tr>';
                                    $tot_vino_da_pagare=$tot_vino_da_pagare+$value['prezzo_fin'];
                                }
                            }
                            $html .= $html_backup;
                        }
                        $totale=$totale+$tot_acqua_da_pagare+$tot_vino_da_pagare;
                        $totale=number_format($totale, 2, ',', ' ');
                        $totale_sconto_soci=($totale/$coperti)*(0.1*$soci);
                        $totale_fin=$totale-$totale_sconto_soci;
                        $totale_fin=number_format($totale_fin, 2, ',', ' ');
                        mysqli_free_result($res);
                    $html.='</table>
                </td>
                <td class="info" style="margin-top: 0mm; padding-top: 0; vertical-align: top;">
                    <h2 class="comanda">Rione Contrastanga<br />Taverna Sette Selle</h2>
                    <text class="info">Comanda n. '.$num_comanda.' Tavolo: '.$tavolo.'/'.$indice.'</text><br />
                    <text class="standard">del giorno '.date_format(date_create($serata_attuale),"d/m/Y").'</text>
                </td>
            </tr>
            <tr>
                <td class="info" style="padding-bottom: 15mm;">';
                    $html_backup_tot.='<p class="totale"><span class="sx">Totale prodotti </span><span class="dx">€ '.$totale.'</span><br/>';
                    $html_backup_tot.='<span class="sx">Numero soci </span><span class="dx">'.$soci.' soci con sconto 10%</span><br />';
                    $html_backup_tot.='<span class="sx">Totale Comanda </span><span class="dx">€ '.$totale_fin.'</span>';
                    $html.=$html_backup_tot.'</p>
                </td>
            </tr>
        </table>
        <table class="contenitore" border="0">
            <tr>
                <td rowspan="2" class="conto">
                    <table class="product-sotto" border="0">
                        <tr class="header">
                            <th>Prodotto</th>
                            <th>Q.t&agrave;</th>
                            <th>Prezzo</th>
                            <th>Totale</th>
                        </tr>
                        '.$html_backup.'
                </table>
                </td>
                <td class="info-info">
                    <h2 class="comanda">Rione Contrastanga<br />Taverna Sette Selle</h2>
                    <text class="info">Comanda n. '.$num_comanda.' Tavolo: '.$tavolo.'/'.$indice.'</text><br />
                    <text class="standard">del giorno '.date_format(date_create($serata_attuale),"d/m/Y").'</text>
                </td>
            </tr>
            <tr>
                <td class="info">'.$html_backup_tot.'</p>
                </td>
            </tr>
        </table>
    <!--</div>-->
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
	

      //$dompdf->stream("comanda_".rand(10,1000)."_".$serata_attuale.".pdf", array("Attachment" => false));
    /*$file_to_save = 'C:\Users\Public\Documents\file.pdf';
    file_put_contents($file_to_save, $dompdf->output());
    /*
      shall_exec(print /d:port $file_to_save);//windows
    "C:\Program Files (x86)\Adobe\Reader 11.0\Reader\AcroRd32.exe" /t "C:\Folder\File.pdf" "Brother MFC-7820N USB Printer" "Brother MFC-7820N USB Printer" "IP_192.168.10.110"

    */
    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
?>