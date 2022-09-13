<?php
	require_once '../include/core.inc.php';
	
	$comanda=array();

  if(isset($_POST['tavolo']) && is_numeric($_POST['tavolo']) && isset($_POST['indice']) && is_numeric($_POST['indice']) && $_POST['indice']>=1){

    $date=ottieni_data_serata_attuale();
    if($date <=0){
        $comanda=array('error' => '#error#Errore durante l\'acquisizione della data');
    }
    else{

      $link = connetti_mysql();

      $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
      $indice = mysqli_real_escape_string($link, $_POST['indice']);

      $query = "SELECT * 
                FROM Comande c 
                INNER JOIN Tavoli t ON c.tavolo = t.numero_tavolo 
                INNER JOIN Menu m ON c.menu=m.nome_menu
                WHERE c.serata = '$date' AND c.tavolo = $tavolo AND c.indice = $indice";

      $query_ordini = "SELECT * 
                       FROM Ordini o
                       INNER JOIN Portata p ON p.nome_portata = o.portata
                       WHERE o.serata='$date' AND o.tavolo = $tavolo AND o.indice = $indice
                       ORDER BY FIELD(p.categoria, 'pane e coperto', 'antipasto', 'bruschette e crostoni', 'primo', 'secondo', 'piadina', 'contorno', 'dolce', 'bevanda', 'cantinetta')";
    				
    	if( ($result = esegui_query($link, $query)) && ($res_ord = esegui_query($link, $query_ordini))){ 
        if($row_com = mysqli_fetch_assoc($result)){
            $ordini=array();

            //calcola il totale
            $totale=0;
            $sconto_manuale=( isset($row_com['sconto_manuale']) ? $row_com['sconto_manuale'] : 0);
            $soci = $row_com['numero_soci'];
            $menu_fisso = $row_com['fisso'];
            if($menu_fisso==1) $prezzo_menu_fisso=$row_com['prezzo_fisso'];
            $coperti=0;
            $tot_acqua_da_pagare=0;
            $tot_vino_da_pagare=0;

            if($menu_fisso==0){
              while($row=mysqli_fetch_assoc($res_ord)){
                    $portata=$row['portata'];
                    $quantita=$row['quantita'];
                    $prezzo=$row['prezzo_finale'];
                    $prezzoquantita=$row['prezzo_finale']*$row['quantita'];
                    $prezzo=number_format($prezzo, 2, ',', ' ');
                    $prezzoquantita=number_format($prezzoquantita, 2, ',', ' ');
                    $ordini[]= array('portata' => $portata, 'quantita' => $quantita, 'prezzo' => $prezzo, 'prezzo_quant' => $prezzoquantita);
                    
                    $totale = $totale + ($row['prezzo_finale']*$row['quantita']);
                    if(strtolower($row['portata']) == "pane e coperto"){
                        $coperti=$row['quantita'];
                    } 
                }
                
            }
            elseif($menu_fisso==1){
                $acqua_ordinata=0;
                //$acqua_da_pagare=0;
                $vino_ordinato=0;
                //$vino_da_pagare=0;
                $acqua=array();
                $vino=array();
                while($row=mysqli_fetch_assoc($res_ord)){  
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
                $ordini[]= array('portata' => 'Men&ugrave; Fisso', 'quantita' => $coperti, 'prezzo' => number_format($prezzo_menu_fisso, 2, ',', ' '), 'prezzo_quant' => number_format($totale, 2, ',', ' '));

                if(!empty($acqua)){
                    foreach ($acqua as $key => $value) {
                        $tot_acqua_da_pagare=$tot_acqua_da_pagare+$value['prezzo_fin'];
                        $ordini[]= array('portata' => $value['tipo'], 'quantita' => $value['num'], 'prezzo' => number_format($value['prezzo'], 2, ',', ' '), 'prezzo_quant' => number_format($value['prezzo_fin'], 2, ',', ' '));
                    }
                }
                if(!empty($vino)){
                    foreach ($vino as $key => $value) {
                        $tot_vino_da_pagare=$tot_vino_da_pagare+$value['prezzo_fin'];
                        $ordini[]= array('portata' => $value['tipo'], 'quantita' => $value['num'], 'prezzo' => number_format($value['prezzo'], 2, ',', ' '), 'prezzo_quant' => number_format($value['prezzo_fin'], 2, ',', ' '));
                    }
                }
                
            }

            if($coperti == 0){
                $comanda=array('error' => '#error#La comanda non ha nessun coperto');
            }
            else {

                $totale=$totale+$tot_acqua_da_pagare+$tot_vino_da_pagare;
                $totale_sconto_soci=($totale/$coperti)*(0.1*$soci);

                $totale_fin=$totale-$totale_sconto_soci-$sconto_manuale;
                $totale_fin = round($totale_fin * 2, 0)/2;
                if($totale_fin<0) $totale_fin=0;
                $tot_persona=$totale_fin/$coperti;
                        
                $totale=number_format($totale, 2, ',', ' ');
                $totale_fin=number_format($totale_fin, 2, ',', ' ');

                $comanda=array('serata' => $row_com['serata'], 'tavolo' => $row_com['tavolo'], 'indice' => $row_com['indice'], 'menu' => $row_com['menu'], 'numero_soci' => $row_com['numero_soci'],    'attiva' => $row_com['attiva'], 'zona' => $row_com['zona'], 'responsabile' => $row_com['responsabile'], 'pagata' => $row_com['pagata'], 'sconto_manuale'=> $row_com['sconto_manuale'], 'annotazioni'=> $row_com['annotazioni'], 'ordini' => $ordini, 'totale' => $totale, 'totale_scontato' => $totale_fin);
            }
        }
      }
      else{
        /* error */
        $comanda=array('error' => '#error#Errore durante l\'acquisizione dei dati');
      }

      disconnetti_mysql($link);
    }
  }
  else{
    /* error */
    $comanda=array('error' => '#error#Parametri non riconosciuti');
  }

  echo json_encode($comanda);


  function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
?>