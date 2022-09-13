<?php
require '../assets/php-export-data-master/php-export-data.class.php';
require_once '../include/core.inc.php';

$date = ottieni_data_serata_attuale();
//$date="2016-05-27";

$nome_file = 'esporta_serata_'.date_format(date_create($date),"d-m-Y").'.xls';
$exporter = new ExportDataExcel('browser', $nome_file);
$exporter->initialize();
$query="SELECT * FROM Comande c
	      INNER JOIN Menu m ON c.menu=m.nome_menu
	      WHERE serata='$date'";
$link=connetti_mysql();
$sconto_manuale=0;
$coperti=0;
$soci=0;
$menu_fisso=0;
$prezzo_menu_fisso;
if(!($res=esegui_query($link,$query))){
    //echo '#error#Errore durante la stampa2';
    disconnetti_mysql($link);
    die();
}elseif (mysqli_num_rows($res)>=1) {
	while($row=mysqli_fetch_assoc($res)){
		$totale=0;
		$soci = $row['numero_soci'];
		$note = $row['annotazioni'];
		$sconto_manuale = $row['sconto_manuale'];
		$menu_fisso = $row['fisso'];
		$pagata = $row['pagata'];
		$menu_name= $row['nome_menu'];
		$prezzo_menu_fisso=$row['prezzo_fisso'];
		$query_ordini="SELECT * FROM Ordini o
            INNER JOIN Portata p
            ON p.nome_portata=o.portata
            WHERE tavolo=".$row['tavolo']." AND indice=".$row['indice']." AND serata='$date'
            ORDER BY FIELD(p.categoria, 'pane e coperto', 'antipasto', 'bruschette e crostoni', 'primo', 'secondo', 'piadina', 'contorno', 'dolce', 'bevanda', 'cantinetta')";
		$exporter->addRow(array("Serata: ".date_format(date_create($date),"d-m-Y"), (!is_null($row['num_comanda']) ? "Num. Comanda: ".$row['num_comanda'] : "Num. Comanda: -"), "comanda: ".$row['tavolo']."/".$row['indice'], "Menu: ".$row['menu']));  
		if(!($res1=esegui_query($link,$query_ordini))){
		    //echo '#error#Errore durante la stampa2';
		    disconnetti_mysql($link);
		    die();
		}elseif (mysqli_num_rows($res1)>=1) {
			$tot_acqua_da_pagare=0;
            $tot_vino_da_pagare=0;
            if($menu_fisso==0){
            	$exporter->addRow(array("Portata", "Categoria", "Quantità", "Prezzo cad.", "Prezzo totale"));
              while($row1=mysqli_fetch_assoc($res1)){
                    $portata=$row1['portata'];
                    $quantita=$row1['quantita'];
                    $prezzo=number_format($row1['prezzo_finale'], 2, '.', ' ');
                    $prezzoquantita=$row1['prezzo_finale']*$row1['quantita'];
                    $prezzoquantita=number_format($prezzoquantita, 2, '.', ' ');
                    $totale = $totale + ($row1['prezzo_finale']*$row1['quantita']);
                    $exporter->addRow(array($row1['nome_portata'], $row1['categoria'], $row1['quantita'], "€ ".$row1['prezzo_finale'], "€ ".$prezzoquantita));
                    //$totale=number_format($totale, 2, ',', ' ');
                  if($row1['portata'] == "Pane e Coperto"){
                    $coperti=$row1['quantita'];
                  }        
                }                
            }elseif($menu_fisso==1){
            	$exporter->addRow(array("Menu", "Quantità", "Prezzo cad.", "Prezzo totale"));
                $acqua_ordinata=0;
				//$acqua_da_pagare=0;
				$vino_ordinato=0;
				//$vino_da_pagare=0;
				$acqua=array();
				$vino=array();
				while($row1=mysqli_fetch_assoc($res1)){  
					$quant = $row1['quantita'];
					if($row1['portata'] == "Pane e Coperto"){
						$coperti=$quant;
					}
					if(startsWith(strtolower($row1['portata']), 'acqua')){
						$acqua_compresa=ceil($coperti/2);
						if($acqua_ordinata==0){
						  //primo tipo di acqua, paga tutta quella in piu rispetto a quella compresa
						  $acqua_ordinata+=$quant;
						  $acqua_da_pagare=$acqua_ordinata-$acqua_compresa;
						  if( $acqua_da_pagare >= 1 ){
							  $tot_tmp = $row1['prezzo_finale']*$acqua_da_pagare;
							  $prezzo_ac=number_format($row1['prezzo_finale'], 2, '.', ' ');
							  $acqua[]=array('tipo' => $row1['portata'], 'num' => $acqua_da_pagare, 'prezzo' => $prezzo_ac, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
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
								$tot_tmp = $row1['prezzo_finale']*$acqua_da_pagare;
								$prezzo_ac=number_format($row1['prezzo_finale'], 2, '.', ' ');
								$acqua[]=array('tipo' => $row1['portata'], 'num' => $acqua_da_pagare, 'prezzo' => $prezzo_ac, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
							}
							$acqua_ordinata+=$quant;
						  }
						  else {
							$tot_tmp = $row1['prezzo_finale']*$quant;
							$prezzo_ac=number_format($row1['prezzo_finale'], 2, '.', ' ');
							$acqua[]=array('tipo' => $row1['portata'], 'num' => $quant, 'prezzo' => $prezzo_ac, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
						  }
						}
					}
					if(startsWith(strtolower($row1['portata']), 'vino')){
					  $vino_compreso=round($coperti/3);
					  if($vino_ordinato==0){
						//primo tipo di vino, paga tutta quella in piu rispetto a quella compreso
						$vino_ordinato+=$quant;
						$vino_da_pagare=$vino_ordinato-$vino_compreso;
						if( $vino_da_pagare >= 1 ){
							$tot_tmp = $row1['prezzo_finale']*$vino_da_pagare;
							$prezzo_vi=number_format($row1['prezzo_finale'], 2, '.', ' ');
							//$prezzoquantita_vi=$row['prezzo_finale']*($row['quantita']+$vino_da_pagare);
							//$prezzoquantita_vi=number_format($prezzoquantita_vi, 2, '.', ' ');
							$vino[]=array('tipo' => $row1['portata'], 'num' => $vino_da_pagare, 'prezzo' => $prezzo_vi, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
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
							$tot_tmp = $row1['prezzo_finale']*$vino_da_pagare;
							$prezzo_vi=number_format($row1['prezzo_finale'], 2, '.', ' ');
							$vino[]=array('tipo' => $row1['portata'], 'num' => $vino_da_pagare, 'prezzo' => $prezzo_vi, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
						  }
						  $vino_ordinato+=$quant;
						}
						else {
							$tot_tmp = $row1['prezzo_finale']*$quant;
							$prezzo_ac=number_format($row1['prezzo_finale'], 2, '.', ' ');
							$acqua[]=array('tipo' => $row1['portata'], 'num' => $quant, 'prezzo' => $prezzo_ac, 'prezzo_fin' => number_format($tot_tmp, 2, '.', ' '));
						  }
					  }
					}
					//$prezzo_menu_fisso=number_format($prezzo_menu_fisso, 2, ',', ' ');
					
					//$totale=number_format($totale, 2, ',', ' ');
				}
				$totale = $prezzo_menu_fisso*$coperti;
                $exporter->addRow(array("Menù Fisso ".$menu_name, $coperti, "€ ".$prezzo_menu_fisso,"€ ".($prezzo_menu_fisso*$coperti)));
                if(!empty($acqua)){
                    foreach ($acqua as $key => $value) {
                        $exporter->addRow(array($value['tipo'], $value['num'],"€ ".$value['prezzo'], "€ ".$value['prezzo_fin']));
                        $tot_acqua_da_pagare=+$tot_acqua_da_pagare+$value['prezzo_fin'];
                    }
                }
                if(!empty($vino)){
                    foreach ($vino as $key => $value) {
                        $exporter->addRow(array($value['tipo'], $value['num'], "€ ".$value['prezzo'], "€ ".$value['prezzo_fin']));
                        $tot_vino_da_pagare=$tot_vino_da_pagare+$value['prezzo_fin'];
                    }
                }
            }
            $totale=$totale+$tot_acqua_da_pagare+$tot_vino_da_pagare;
            $totale=number_format($totale, 2, '.', ' ');
            $totale_sconto_soci=($totale/$coperti)*(0.1*$soci);
            $totale_fin=$totale-$totale_sconto_soci-$sconto_manuale;
            if($totale_fin<0){
                $totale_fin=0;
            }
            $tot_persona=$totale_fin/$coperti;
            $totale_fin=number_format($totale_fin, 2, '.', ' ');
            mysqli_free_result($res1);
		}
		$exporter->addRow(array("","","","", "Totale Parziale: € ".$totale));
		$exporter->addRow(array("","","","","Numero Soci: ".$row['numero_soci']));
		if($sconto_manuale>0){
			$exporter->addRow(array("","","","","Sconto: € ".$row['sconto_manuale']));
		}
		$exporter->addRow(array("","","","",($pagata == 1 ? "Pagata: SI" : "Pagata: NO"), "NOTE: ".$note));
		$exporter->addRow(array("","","","", "Totale: € ".$totale_fin));
		$exporter->addRow("");
	}
}
$exporter->finalize();
exit();
function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

?>
				
