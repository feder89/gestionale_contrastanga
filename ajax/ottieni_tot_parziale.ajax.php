<?php
	require_once '../include/core.inc.php';

	  $date=ottieni_data_serata_attuale();
    if($date <=0){
        echo '#error#Errore durante l\'acquisizione della data';
    }
    else{
		    $link = connetti_mysql();

        if(isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])
          && isset($_POST['num_soci']) && is_numeric($_POST['num_soci']) && $_POST['num_soci']>=0
          && isset($_POST['num_coperti']) && is_numeric($_POST['num_coperti']) && $_POST['num_coperti']>0
          && isset($_POST['menu']) && strlen($_POST['menu'])>0
          && isset($_POST['sconto_manuale']) && is_numeric($_POST['sconto_manuale']) && $_POST['sconto_manuale']>=0
          && isset($_POST['nuovi_ordini']) && is_array($_POST['nuovi_ordini'])
          && isset($_POST['nuovo']) && $_POST['nuovo']==1){

          $nome_menu = mysqli_real_escape_string($link, $_POST['menu']);
          $query_menu = "SELECT * FROM Menu WHERE nome_menu='$nome_menu'";  
          if(($res = mysqli_query($link, $query_menu)) && ($row = mysqli_fetch_assoc($res))){
            $menu_fisso = $row['fisso'];
            if($menu_fisso==1) $prezzo_menu_fisso=$row['prezzo_fisso'];
            $totale=0;
            $sconto_manuale = $_POST['sconto_manuale'];
            $soci = $_POST['num_soci'];
            $coperti = $_POST['num_coperti'];
            $tot_acqua_da_pagare=0;
            $tot_vino_da_pagare=0;
            if($menu_fisso==0){
                //NON FISSO
                foreach ($_POST['nuovi_ordini'] as $key => $ordine){
                    $nome_portata = $ordine[0];
                    $quant = $ordine[1];
                    $query_portata = "SELECT * FROM Portata WHERE nome_portata = '$nome_portata'";
                    if(($res2 = mysqli_query($link, $query_portata)) && ($row2 = mysqli_fetch_assoc($res2))){
                      $totale += $row2['prezzo_finale']*$quant; 
                    }
                    else{
                      echo '#error#Errore durante l\'operazione';
                      disconnetti_mysql($link);
                    }
                         
                }                       
            }
            else if($menu_fisso==1){
              //FISSO
              $acqua_ordinata=0;
              $vino_ordinato=0;

              $totale=$prezzo_menu_fisso*$coperti;

              foreach ($_POST['nuovi_ordini'] as $key => $ordine){
                  $nome_portata = $ordine[0];
                  $quant = $ordine[1];
                  $query_portata = "SELECT * FROM Portata WHERE nome_portata = '$nome_portata'";
                  if(($res2 = mysqli_query($link, $query_portata)) && ($row2 = mysqli_fetch_assoc($res2))){
                    if(startsWith(strtolower($nome_portata), 'acqua')){
                        $acqua_compresa=ceil($coperti/2);
                        if($acqua_ordinata==0){
                          //primo tipo di acqua, paga tutta quella in piu rispetto a quella compresa
                          $acqua_ordinata+=$quant;
                          $acqua_da_pagare=$acqua_ordinata-$acqua_compresa;
                          if( $acqua_da_pagare >= 1 ) $tot_acqua_da_pagare += $row2['prezzo_finale']*$acqua_da_pagare;
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
                            if( $acqua_da_pagare >= 1 ) $tot_acqua_da_pagare += $row2['prezzo_finale']*$acqua_da_pagare;
                            $acqua_ordinata+=$quant;
                          }
                          else {
                            //acqua compresa saturata, tutto da pagare
                            $tot_acqua_da_pagare += $row2['prezzo_finale']*$quant;
                          }
                        }
                    }
                    if(startsWith(strtolower($nome_portata), 'vino')){
                        $vino_compreso=round($coperti/3);
                        if($vino_ordinato==0){
                          //primo tipo di vino, paga tutta quello in piu rispetto a quella compresa
                          $vino_ordinato+=$quant;
                          $vino_da_pagare=$vino_ordinato-$vino_compreso;
                          if( $vino_da_pagare >= 1 ) $tot_vino_da_pagare += $row2['prezzo_finale']*$vino_da_pagare;
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
                            if( $vino_da_pagare >= 1 ) $tot_vino_da_pagare += $row2['prezzo_finale']*$vino_da_pagare;
                            $vino_ordinato+=$quant;
                          }
                          else {
                            //acqua compresa saturata, tutto da pagare
                            $tot_vino_da_pagare += $row2['prezzo_finale']*$quant;
                          }
                        }
                    }
                  }
                  else{
                    echo '#error#Errore durante l\'operazione';
                    disconnetti_mysql($link);
                  }
              }
            }


            $totale += $tot_acqua_da_pagare+$tot_vino_da_pagare;
            $totale_sconto_soci=($totale/$coperti)*(0.1*$soci);

            $totale_fin=$totale-$totale_sconto_soci-$sconto_manuale;
            $totale_fin = round($totale_fin * 2, 0)/2;
            if($totale_fin<0) $totale_fin=0;
            $tot_persona=$totale_fin/$coperti;

            echo number_format($totale_fin, 2, '.', ' ').'/'.number_format($tot_persona, 2, '.', ' ');
          } 
          else {
            echo '#error#Errore durante l\'operazione';
          }     
        }
        else if(isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])
          && isset($_POST['indice']) && is_numeric($_POST['indice']) && $_POST['indice']>=1
          && isset($_POST['num_soci']) && is_numeric($_POST['num_soci']) && $_POST['num_soci']>=0
          && isset($_POST['num_coperti']) && is_numeric($_POST['num_coperti'])
          && isset($_POST['sconto_manuale']) && is_numeric($_POST['sconto_manuale']) && $_POST['sconto_manuale']>=0
          && isset($_POST['nuovo']) && $_POST['nuovo']==0){

          $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
          $indice = mysqli_real_escape_string($link, $_POST['indice']);

          $query_comanda = "SELECT * 
                               FROM Comande c
                               INNER JOIN Menu m ON c.menu=m.nome_menu
                               WHERE c.serata='$date' AND c.tavolo = $tavolo AND c.indice = $indice";

          if(!($res3 = mysqli_query($link, $query_comanda)) || !($row3 = mysqli_fetch_assoc($res3)) ){
            echo '#error#Errore durante l\'operazione';
            disconnetti_mysql($link);
            die();
          }

          $nome_menu = $row3['menu'];
          $menu_fisso = $row3['fisso'];
          if($menu_fisso==1) $prezzo_menu_fisso=$row3['prezzo_fisso'];
          $totale=0;

          $sconto_manuale = $_POST['sconto_manuale'];
          $soci = $_POST['num_soci'];

          //cose nuove
          $coperti_nuovi = $_POST['num_coperti'];
          $coperti = 0;

          $tot_acqua_da_pagare=0;
          $tot_vino_da_pagare=0;

          $query_ordini="SELECT * 
                          FROM Ordini o
                          INNER JOIN Portata p ON p.nome_portata=o.portata
                          WHERE tavolo=$tavolo AND indice=$indice AND serata='$date'
                          ORDER BY FIELD(p.categoria, 'pane e coperto', 'antipasto', 'bruschette e crostoni', 'primo', 'secondo', 'piadina', 'contorno', 'dolce', 'bevanda')";

          if($menu_fisso==0){
              //NON FISSO
              //ORDINI PRECEDENTI
              if(($res_ord = mysqli_query($link, $query_ordini))){
                while($row_ord=mysqli_fetch_assoc($res_ord)){
                    $totale += $row_ord['prezzo_finale']*$row_ord['quantita'];
                    if(strtolower($row_ord['portata']) == "pane e coperto"){
                        $coperti=$row_ord['quantita'];
                    }        
                }

                //aggiungi i coperti
                $coperti += $coperti_nuovi;  

                //NUOVI ORDINI
                if(isset($_POST['nuovi_ordini']) && is_array($_POST['nuovi_ordini'])){
                  foreach ($_POST['nuovi_ordini'] as $key => $ordine){
                      $nome_portata = $ordine[0];
                      $quant = $ordine[1];
                      $query_portata = "SELECT * FROM Portata WHERE nome_portata = '$nome_portata'";
                      if(($res2 = mysqli_query($link, $query_portata)) && ($row2 = mysqli_fetch_assoc($res2))){
                        if($quant<0){
                          //controlla di quanto sta decrementando
                          $query_check = "SELECT * FROM Ordini WHERE portata = '$nome_portata' AND tavolo=$tavolo AND indice=$indice AND serata='$date'";
                          if(!($res_check = mysqli_query($link, $query_check))){
                            echo '#error#Errore durante l\'operazione';
                            disconnetti_mysql($link);
                            die();
                          } 
                          if(mysqli_num_rows($res_check)>=1){
                            //la portata c'è, decrementa
                            $row_check = mysqli_fetch_assoc($res_check);
                            $quant_attuale = $row_check['quantita'];
                            if(abs($quant) > $quant_attuale) $quant = (-1)*$quant_attuale;
                            $totale += $row2['prezzo_finale']*$quant; 
                          }
                          else {
                            //non c'è la portata, niente da decrementare
                            continue;
                          }
                        }
                        else {
                          $totale += $row2['prezzo_finale']*$quant; 
                        }
                      }
                      else{
                        echo '#error#Errore durante l\'operazione';
                        disconnetti_mysql($link);
                      }     
                  }
                }
              }
              else{
                echo '#error#Errore durante l\'operazione';
                disconnetti_mysql($link);
                die();
              }                       
          }
          else if($menu_fisso==1){
            //FISSO
            $acqua_ordinata=0;
            $vino_ordinato=0;

            if(($res_ord = mysqli_query($link, $query_ordini))){

              //ordini vecchi
              while($row_ord=mysqli_fetch_assoc($res_ord)){  
                  $quant = $row_ord['quantita'];
                  if(strtolower($row_ord['portata']) == "pane e coperto"){
                      $coperti=$row_ord['quantita'];
                  }
                  if(startsWith(strtolower($row_ord['portata']), 'acqua')){
                      $acqua_compresa=ceil($coperti/2);

                      //cerca nell'array ordini se c'è questa acqua convalore negativo
                      if(isset($_POST['nuovi_ordini']) && is_array($_POST['nuovi_ordini']) && ( $pos = _search_val_arr($row_ord['portata'], $_POST['nuovi_ordini'])) >= 0 && $_POST['nuovi_ordini'][$pos][1] < 0){
                        //rimuovi le acque
                        $quant -= abs($_POST['nuovi_ordini'][$pos][1]);
                        if($quant <= 0) continue;
                      }

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

                      //cerca nell'array ordini se c'è questa acqua convalore negativo
                      if(isset($_POST['nuovi_ordini']) && is_array($_POST['nuovi_ordini']) && ( $pos = _search_val_arr($row_ord['portata'], $_POST['nuovi_ordini'])) >= 0 && $_POST['nuovi_ordini'][$pos][1] < 0){
                        //rimuovi le acque
                        $quant -= abs($_POST['nuovi_ordini'][$pos][1]);
                        if($quant <= 0) continue;
                      }

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

              //aggiungi i coperti
              $coperti += $coperti_nuovi;

              //nuovi ordini
              if(isset($_POST['nuovi_ordini']) && is_array($_POST['nuovi_ordini'])){
                foreach ($_POST['nuovi_ordini'] as $key => $ordine){

                    $nome_portata = $ordine[0];
                    $quant = $ordine[1];
                    if($quant <= 0) continue; //salta i negativi, sono calcolati prima

                    $query_portata = "SELECT * FROM Portata WHERE nome_portata = '$nome_portata'";
                    if(($res2 = mysqli_query($link, $query_portata)) && ($row2 = mysqli_fetch_assoc($res2))){
                      if(startsWith(strtolower($nome_portata), 'acqua')){
                          $acqua_compresa=ceil($coperti/2);
                          if($acqua_ordinata==0){
                            //primo tipo di acqua, paga tutta quella in piu rispetto a quella compresa
                            $acqua_ordinata+=$quant;
                            $acqua_da_pagare=$acqua_ordinata-$acqua_compresa;
                            if( $acqua_da_pagare >= 1 ) $tot_acqua_da_pagare += $row2['prezzo_finale']*$acqua_da_pagare;
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
                              if( $acqua_da_pagare >= 1 ) $tot_acqua_da_pagare += $row2['prezzo_finale']*$acqua_da_pagare;
                              $acqua_ordinata+=$quant;
                            }
                            else {
                              //acqua compresa saturata, tutto da pagare
                              $tot_acqua_da_pagare += $row2['prezzo_finale']*$quant;
                            }
                          }
                      }
                      if(startsWith(strtolower($nome_portata), 'vino')){
                          $vino_compreso=round($coperti/3);
                          if($vino_ordinato==0){
                            //primo tipo di vino, paga tutta quello in piu rispetto a quella compresa
                            $vino_ordinato+=$quant;
                            $vino_da_pagare=$vino_ordinato-$vino_compreso;
                            if( $vino_da_pagare >= 1 ) $tot_vino_da_pagare += $row2['prezzo_finale']*$vino_da_pagare;
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
                              if( $vino_da_pagare >= 1 ) $tot_vino_da_pagare += $row2['prezzo_finale']*$vino_da_pagare;
                              $vino_ordinato+=$quant;
                            }
                            else {
                              //acqua compresa saturata, tutto da pagare
                              $tot_vino_da_pagare += $row2['prezzo_finale']*$quant;
                            }
                          }
                      }
                    }
                    else{
                      echo '#error#Errore durante l\'operazione';
                      disconnetti_mysql($link);
                    }
                }
              }

              $totale=$prezzo_menu_fisso*$coperti;
            }
            else{
              echo '#error#Errore durante l\'operazione';
              disconnetti_mysql($link);
              die();
            }  
          }

          if($coperti == 0) echo '#error#La comanda non ha nessun coperto';
          else {
            $totale += $tot_acqua_da_pagare+$tot_vino_da_pagare;
            $totale_sconto_soci=($totale/$coperti)*(0.1*$soci);

            $totale_fin=$totale-$totale_sconto_soci-$sconto_manuale;
            $totale=number_format($totale, 2, '.', ' ');
            if($totale_fin<0) $totale_fin=0;
            $tot_persona=$totale_fin/$coperti;

            echo number_format($totale_fin, 2, '.', ' ').'/'.number_format($tot_persona, 2, '.', ' ');
          }
         
        }
        else {

          echo '#error#Errore operazione non riconosciuta';
        }
		
        disconnetti_mysql($link);
        die();
	}

    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    function _search_val_arr($value, $_2darr){
      foreach ($_2darr as $key => $portata_arr) {
        if($portata_arr[0] == $value) return $key;
      }
      return -1;
    }








	
?>