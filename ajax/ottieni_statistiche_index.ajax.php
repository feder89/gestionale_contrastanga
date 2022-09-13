<?php
	require_once '../include/core.inc.php';

	  $date=ottieni_data_serata_attuale();
    if($date <=0){
        echo '#error#Errore durante l\'acquisizione della data';
    }
    else{
		    $link = connetti_mysql();
		

        $query_comande = "SELECT COUNT(*) AS num_comande FROM Comande WHERE serata='$date'";
        $query_coperti = "SELECT SUM(o.quantita) AS num_coperti
                          FROM Comande c
                          INNER JOIN Ordini o ON o.serata = c.serata AND o.tavolo = c.tavolo AND o.indice = c.indice
                          WHERE c.serata='$date' AND LOWER(o.portata) = 'pane e coperto'";

        $query_comande_list = "SELECT * 
                               FROM Comande c
                               INNER JOIN Menu m ON c.menu=m.nome_menu
                               WHERE c.attiva=0 AND c.serata='$date'";

        if(($res = mysqli_query($link, $query_comande)) && ($res2 = mysqli_query($link, $query_coperti)) && ($res3 = mysqli_query($link, $query_comande_list))){
          //calcolo num comande e coperti
          $row = mysqli_fetch_assoc($res);
          $row2 = mysqli_fetch_assoc($res2);
          $num_comande = $row['num_comande'];
          $num_coperti = $row2['num_coperti'];
          if(strlen($num_coperti)<=0) $num_coperti=0;

          //calcolo incasso
          $totale=0;
          while(($row3 = mysqli_fetch_assoc($res3))){
            $sconto_manuale=( isset($row3['sconto_manuale']) ? $row3['sconto_manuale'] : 0);
            if($row3['pagata'] == 1){
                $tavolo = $row3['tavolo'];
                $indice = $row3['indice'];
                $soci = $row3['numero_soci'];
                $menu_fisso = $row3['fisso'];
                if($menu_fisso==1) $prezzo_menu_fisso=$row3['prezzo_fisso'];
                $coperti=0;
                $tot_acqua_da_pagare=0;
                $tot_vino_da_pagare=0;
                $query_ordini="SELECT * 
                              FROM Ordini o
                              INNER JOIN Portata p ON p.nome_portata=o.portata
                              WHERE tavolo=$tavolo AND indice=$indice AND serata='$date'
                              ORDER BY FIELD(p.categoria, 'pane e coperto', 'antipasto', 'bruschette e crostoni', 'primo', 'secondo', 'piadina', 'contorno', 'dolce', 'bevanda', 'cantinetta')";

                if(($res_ord = mysqli_query($link, $query_ordini))){
                    $totale_tmp = 0;
                    if($menu_fisso==0){
                        //NON FISSO
                        while($row_ord=mysqli_fetch_assoc($res_ord)){
                            $totale_tmp += $row_ord['prezzo_finale']*$row_ord['quantita'];
                            if(strtolower($row_ord['portata']) == "pane e coperto"){
                                $coperti=$row_ord['quantita'];
                            }        
                        }                        
                    }
                    else if($menu_fisso==1){
                        //FISSO
                        $acqua_ordinata=0;
                        $vino_ordinato=0;
                        while($row_ord=mysqli_fetch_assoc($res_ord)){  
                            $quant = $row_ord['quantita'];
                            if(strtolower($row_ord['portata']) == "pane e coperto"){
                                $coperti=$row_ord['quantita'];
                            }
                            if(startsWith(strtolower($row_ord['portata']), 'acqua')){
                                $acqua_compresa=ceil($coperti/2);
                                if($acqua_ordinata==0){
                                  //primo tipo di acqua, paga tutta quella in piu rispetto a quella compresa
                                  $acqua_ordinata+=$quant;
                                  $acqua_da_pagare=$acqua_ordinata-$acqua_compresa;
                                  if( $acqua_da_pagare >= 1 ) $tot_acqua_da_pagare += $row_ord['prezzo_finale']*$acqua_da_pagare;
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
                                    if( $acqua_da_pagare >= 1 ) $tot_acqua_da_pagare += $row_ord['prezzo_finale']*$acqua_da_pagare;
                                    $acqua_ordinata+=$quant;
                                  }
                                  else {
                                    //acqua compresa saturata, tutto da pagare
                                    $tot_acqua_da_pagare += $row_ord['prezzo_finale']*$quant;
                                  }
                                }
                            }
                            if(startsWith(strtolower($row_ord['portata']), 'vino')){
                                $vino_compreso=round($coperti/3);
                                if($vino_ordinato==0){
                                  //primo tipo di vino, paga tutta quello in piu rispetto a quella compresa
                                  $vino_ordinato+=$quant;
                                  $vino_da_pagare=$vino_ordinato-$vino_compreso;
                                  if( $vino_da_pagare >= 1 ) $tot_vino_da_pagare += $row_ord['prezzo_finale']*$vino_da_pagare;
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
                                    if( $vino_da_pagare >= 1 ) $tot_vino_da_pagare += $row_ord['prezzo_finale']*$vino_da_pagare;
                                    $vino_ordinato+=$quant;
                                  }
                                  else {
                                    //acqua compresa saturata, tutto da pagare
                                    $tot_vino_da_pagare += $row_ord['prezzo_finale']*$quant;
                                  }
                                }
                            }
                            
                        }
                        $totale_tmp+=$prezzo_menu_fisso*$coperti;
                    }

                    if($coperti == 0) continue;
                    $totale_tmp += $tot_acqua_da_pagare+$tot_vino_da_pagare;
                    $totale_sconto_soci=($totale_tmp/$coperti)*(0.1*$soci);

                    $totale_fin=$totale_tmp-$totale_sconto_soci-$sconto_manuale;
                    if($totale_fin<0) $totale_fin=0;
                    $tot_persona=$totale_fin/$coperti;

                    $totale+=$totale_fin;
                }
            }
          }

          $totale=number_format($totale, 2, ',', ' ');

          echo $num_comande.'/'.$totale.'/'.$num_coperti;
          
        } 
        else echo '#error#Errore durante l\'operazione';

	  	disconnetti_mysql($link);
	}

    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
	
?>