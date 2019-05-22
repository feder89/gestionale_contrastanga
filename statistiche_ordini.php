<?php
  require_once 'include/header.inc.php';
  $serata_attuale = ottieni_data_serata_attuale();
  $serata_attuale = date_format(date_create($serata_attuale), "d/m/Y");
?>
<section class="wrapper site-min-height">
  <!-- page start-->
  <section class="panel">
      <header class="panel-heading">
          Statistiche serata del <?php echo $serata_attuale; ?>
      </header>
      <div class="panel-body">
          <div class="adv-table editable-table ">
              <!--<div class="clearfix">
                  <div class="btn-group">
                      <button id="editable-sample_new" class="btn green">
                          Aggiungi <i class="fa fa-plus"></i>
                      </button>
                  </div>
                  <div class="btn-group pull-right">
                      <button class="btn dropdown-toggle" data-toggle="dropdown">Strumenti <i class="fa fa-angle-down"></i>
                      </button>
                      <ul class="dropdown-menu pull-right">
                          <li><a href="#">Stampa</a></li>
                          <li><a href="#">Salva come PDF</a></li>
                      </ul>
                  </div>
              </div>-->
              <div class="space15"></div>
                  
                  <table class="table table-striped table-hover table-bordered portate" id="editable-sample">
                    <thead>
                      <tr>
                        <th>Portata</th>
						<th>Categoria</th>
                        <th>Quantità</th>
                      </tr>
                  </thead>
                  <tbody>
                  <?php
                  $date = ottieni_data_serata_attuale();
					  $link = connetti_mysql();
					  $query="SELECT o.portata, p.categoria, SUM(o.quantita) AS num_portate, p.prezzo_finale
                      FROM Ordini o
                      INNER JOIN Portata p
                      ON p.nome_portata=o.portata
                      WHERE serata='$date'
                      GROUP BY o.portata
                      ORDER BY FIELD(p.categoria,'pane e coperto','antipasto','primo','secondo','contorno','dolce','bevanda','piadina','bruschette e crostoni'), p.nome_portata";                      
					  
                      $res2 = esegui_query($link, $query);

                      while ( $row2 = mysqli_fetch_assoc($res2)){

                         echo '<tr>
								  <td>'.$row2['portata'].'</td>
								  <td>'.$row2['categoria'].'</td>
								  <td>'.$row2['num_portate'].'</td>
							  </tr>';
                      }
					  echo '</tbody>
              </table>';
              mysqli_free_result($res2);
						$query1="SELECT * FROM Comande c
								  INNER JOIN Menu m ON c.menu=m.nome_menu
								  WHERE serata='$date'";
						$sconto_manuale=0;
						$coperti=0;
						$tot_serata=0;
						$tot_serata_non_incassato=0;
						$tot_serata_da_incassare=0;
						$attiva=0;
						$soci=0;
						$menu_fisso=0;
						$prezzo_menu_fisso;
						if(!($res=esegui_query($link,$query1))){
							//echo '#error#Errore durante la stampa2';
							disconnetti_mysql($link);
							die();
						}elseif (mysqli_num_rows($res)>=1) {
							while($row=mysqli_fetch_assoc($res)){
								$totale=0;
								$soci = $row['numero_soci'];
								$note = $row['annotazioni'];
								$attiva=$row['attiva'];
								$sconto_manuale = $row['sconto_manuale'];
								$menu_fisso = $row['fisso'];
								$pagata = $row['pagata'];
								$menu_name= $row['nome_menu'];
								$prezzo_menu_fisso=$row['prezzo_fisso'];
								$query_ordini="SELECT * FROM Ordini o
									INNER JOIN Portata p
									ON p.nome_portata=o.portata
									WHERE tavolo=".$row['tavolo']." AND indice=".$row['indice']." AND serata='$date'
									ORDER BY FIELD(p.categoria, 'pane e coperto', 'antipasto', 'bruschette e crostoni', 'primo', 'secondo', 'piadina', 'contorno', 'dolce', 'bevanda')";
								if(!($res1=esegui_query($link,$query_ordini))){
									//echo '#error#Errore durante la stampa2';
									disconnetti_mysql($link);
									die();
								}elseif (mysqli_num_rows($res1)>=1) {
									$tot_acqua_da_pagare=0;
									$tot_vino_da_pagare=0;
									if($menu_fisso==0){
									  while($row1=mysqli_fetch_assoc($res1)){
											$portata=$row1['portata'];
											$quantita=$row1['quantita'];
											$prezzo=number_format($row1['prezzo_finale'], 2, '.', ' ');
											$prezzoquantita=$row1['prezzo_finale']*$row1['quantita'];
											$prezzoquantita=number_format($prezzoquantita, 2, '.', ' ');
											$totale = $totale + ($row1['prezzo_finale']*$row1['quantita']);
											//$totale=number_format($totale, 2, ',', ' ');
										  if($row1['portata'] == "Pane e Coperto"){
											$coperti=$row1['quantita'];
										  }        
										}                
									}elseif($menu_fisso==1){
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
										if(!empty($acqua)){
											foreach ($acqua as $key => $value) {
												$tot_acqua_da_pagare=+$tot_acqua_da_pagare+$value['prezzo_fin'];
											}
										}
										if(!empty($vino)){
											foreach ($vino as $key => $value) {
												$tot_vino_da_pagare=$tot_vino_da_pagare+$value['prezzo_fin'];
											}
										}
									}
									$totale=$totale+$tot_acqua_da_pagare+$tot_vino_da_pagare;
									//$totale=number_format($totale, 2, '.', ' ');
									$totale_sconto_soci=($totale/$coperti)*(0.1*$soci);
									$totale_fin=$totale-$totale_sconto_soci-$sconto_manuale;
									if($pagata == 1 && $attiva == 0){
										$tot_serata += $totale_fin;
									}elseif($pagata == 0 && $attiva == 0){
										$tot_serata_non_incassato +=$totale_fin;
									}elseif(($pagata == 1 && $attiva == 1) || ($pagata == 0 && $attiva == 1)){
										$tot_serata_da_incassare +=$totale_fin;
									}
									 
									if($totale_fin<0){
										$totale_fin=0;
									}
									$totale_fin = round($totale_fin * 2, 0)/2;
									$tot_persona=$totale_fin/$coperti;
									$totale_fin=number_format($totale_fin, 2, '.', ' ');
									$totale=number_format($totale, 2, '.', ' ');
									mysqli_free_result($res1);
								}
							}
						}
						$tot_serata = number_format($tot_serata, 2, '.', ' ');
						$tot_serata_non_incassato = number_format($tot_serata_non_incassato, 2, '.', ' ');
						$tot_serata_da_incassare = number_format($tot_serata_da_incassare, 2, '.', ' ');
						echo '<br/><br/> Totale incassato: € '.$tot_serata.'<br/>
								Totale da incassare: € '.$tot_serata_da_incassare.'<br/>
								Totale non pagato: € ' .$tot_serata_non_incassato;

						$query_old = "SELECT * FROM prenotazioni WHERE serata = '$date'";
						$tavoli=0;
						$coperti=0;
					    if(!($res_p =esegui_query($link, $query_old))){
					        echo '#error#Errore durante l\'operazione';
					        mysqli_rollback($link);
					        disconnetti_mysql($link, NULL);
					        die();
					    }else{
					        if(mysqli_num_rows($res_p)>0){
					        	$exixst=true;
					            //there is already an entry
					            $row_p = mysqli_fetch_assoc($res_p);
					            //controlla che il resposabile non sia lo stesso
					            $tavoli=$row_p['tavoli'];
					            $coperti=$row_p['coperti'];
					        }
						}	
						echo '<br/><br/> Numero tavoli preontati: '.$tavoli.'<br/>
								Numero coperti preontati: '.$coperti;
                      disconnetti_mysql($link);
                  ?> 
                  
          </div>
      </div>
  </section>
  <!-- page end-->
</section>

<?php
    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
  require_once 'include/footer.inc.php';
?>
