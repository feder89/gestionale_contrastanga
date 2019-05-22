<?php
    require_once 'include/header.inc.php';
?>
  <section class="wrapper site-min-height">

      <?php 

        $link = connetti_mysql();
        $now=time();
        $date=date('Y-m-d', $now);

        $serata_attuale = ottieni_data_serata_attuale();
        if($serata_attuale == 0){
          echo 'Errore, ricarica la pagina';
        }
        else{

            if($serata_attuale == -1){     
                echo '<a href="gestione_serate.php?procedura=init" class="btn btn-success" role="button">Inizializza serata del '.date('d/m/Y', $now).'<span style="margin-left:7px;" class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>';

                ?>
                  <select id="select_serata" data-live-search="true">
                    <option value=""></option>
                    <?php

                      if( ( $res=mysqli_query($link, "SELECT * FROM Serata WHERE inizializzata = 2 ORDER BY data DESC"))){
                        while($row=mysqli_fetch_assoc($res)){
                            echo '<option value="'.$row['data'].'">'.date_format(date_create($row['data']),"d/m/Y").'</option>';
                        }

                      }
                      switch ($_SESSION['quintana']) {
                        case "sett2016";
                            $quintana = "Settembre 2016";                      	
                      	break;
                        case "giu2016";
                            $quintana = "Giugno 2016";
                        break;
                        case "giu2017";
                            $quintana = "Giugno 2017";
                        break;
                      }
                    ?>
              </select>
			  <span style="float: right; margin-right: 15px;"><a href="excel_export/esporta_quintana.php" class="btn btn-warning" role="button">Esporta Staistiche Quintana <?php echo $quintana; ?></a>
              <script>
                $(document).ready(function(){
                  $('#select_serata').selectpicker({
                    noneSelectedText:'Riapri una serata',
                  });

                  $('#select_serata').on('change', function(){
                    var serata_riapri = $(this).find("option:selected").val();
                    $.ajax({
                      type: 'POST',
                      url: "ajax/riapri_serata.ajax.php",
                      dataType: "text",
                      timeout: 20000,
                      data : {
                          data: serata_riapri
                      },
                      beforeSend: function(){
                      
                      },
                      success: function(result){

                        var errore = false;
                        if(stringStartsWith(result, '#error#')) errore=true;

                          if(errore){
                            notify_top(result, 'Riapri Serata'); 
                          }
                          else{
                            //ricarica pagina
                            location.reload();
                          }
                      },
                      error: function( jqXHR, textStatus, errorThrown ){
                        //attiva bottone salva
                    
                        notify_top("#error#Errore durante l'operazione", 'Riapri Serata'); 
                      }   

                });
                  });
                });

              </script>

                <?php
            }
            else {
          ?>
          <!--state overview start-->
          <div class="row state-overview">
              <div class="col-lg-4 col-sm-6">
                  <section class="panel">
                      <div class="symbol terques">
                          <i class="fa fa-user"></i>
                      </div>
                      <div class="value">
                          <h1 id="num-coperti-stat" class="count">
                              0
                          </h1>
                          <p>Coperti Totali</p>
                      </div>
                  </section>
              </div>
              <!--
              <div class="col-lg-3 col-sm-6">
                  <section class="panel">
                      <div class="symbol red">
                          <i class="fa fa-eur" aria-hidden="true"></i>
                      </div>
                      <div style="padding-top: 26px;" class="value">
                          <h1 style="font-size:27px;"  id="incasso-stat" class=" count2">
                              0
                          </h1>
                          <p>Incasso</p>
                      </div>
                  </section>
              </div>
              -->
              <div class="col-lg-4 col-sm-6">
                  <section class="panel">
                      <div class="symbol yellow">
                          <i class="fa fa-list-alt" aria-hidden="true"></i>
                      </div>
                      <div class="value">
                          <h1 id="num-comande-stat" class=" count3">
                              0
                          </h1>
                          <p>Numero Comande</p>
                      </div>
                  </section>
              </div>
              <div class="col-lg-4 col-sm-6">
                  <section class="panel">
                      <div class="symbol blue">
                          <i class="fa fa-bar-chart-o"></i>
                      </div>
                      <div class="value">
                          <h1 id="posti-iberi-stat" class=" count4">
                              0
                          </h1>
                          <p>Posti Liberi</p>
                      </div>
                  </section>
              </div>
          </div>
          <!--state overview end-->

          <div class="row" id="bottoni-dashboard">
            <a href="gestione_comande.php" class="btn btn-success" role="button"><i class="fa fa-list-alt" aria-hidden="true"></i> Gestione Comande</a>
            <a href="responsabili_serata.php?procedura=modifica" class="btn btn-danger" role="button"><i class="fa fa-user" aria-hidden="true"></i> Gestione Responsabili</a>
            <a href="gestione_tavoli.php" class="btn btn-primary" role="button"><i class="fa fa-cutlery" aria-hidden="true"></i> Gestione Tavoli</a>
            <a href="gestione_zone.php" class="btn btn-info" role="button"><i class="fa fa-location-arrow" aria-hidden="true"></i> Gestione Zone</a>
            <a href="gestione_piatti_serata.php?procedura=init&tasto=dashboard" class="btn btn-info" role="button"><i class="fa fa-balance-scale" aria-hidden="true"></i> Gestione Quantità</a>
          </div>
          <div class="row" id="prenotati" style="padding-bottom: 1em;">
            <div class="col-lg-2" style="text-align: right; font-size: 1.5em;">
              <span>Tavoli prenotati: </span>
            </div>
            <div class="col-lg-1"><input id="tavoli-preonotati" min="0" step="any" type="number" class="form-control small num"  placeholder="0"></div>
            <div class="col-lg-2" style="text-align: right; font-size: 1.5em;"><span>Coperti prenotati: </span></div>
            <div class="col-lg-1"><input id="coperti-preonotati" min="0" step="any" type="number" class="form-control small num"  placeholder="0"></div>
            <div class="col-lg-2">
                <button id="salva-prenotati" class="btn" style="background-color: #78CD51; color: white;"><i class="fa fa-save"></i> Salva preontazioni</button>
            </div>
            <div class="col-lg-3"></div>
          </div>

          <div class="row">
              <div class="col-lg-8">
                  <!--custom chart start-->
                  <div class="border-head">
                      <h3>Piatti in esaurimento</h3>
                  </div>
                  <div id="chart" class="custom-bar-chart">
                    <canvas id="canvas" height="300" width="650"></canvas>
                    <script>
                            <?php
                            $query="SELECT * FROM QuantitàPiattiSerata WHERE LOWER(piatto) != 'pane e coperto' AND serata='$serata_attuale' AND quantità <= 60 ORDER BY quantità ASC LIMIT 10";
                            $piatti=array();
                            if(($res=esegui_query($link,$query))){
                              while(($row=mysqli_fetch_assoc($res))){
                                $piatti[]=array($row['piatto'],$row['quantità']);
                              }
                              $piatti=array_reverse($piatti);
                            }

                            ?>
                            var barChartData = {
                              labels : [<?php foreach ($piatti as $key => $piatto) {
                                                  if($key!=0){ echo ',';}
                                                  echo '"'.$piatto[0].'"';
                                              } ?>
                                        ],
                              datasets : [
                                {
                                  fillColor : "rgba(65, 202, 192, 0.7)",
                                  strokeColor : "rgba(65, 202, 192, 0.8)",
                                  highlightFill : "rgba(75, 171, 207, 0.75)",
                                  highlightStroke : "rgba(75, 171, 207, 0.75)",
                                  data : [<?php foreach ($piatti as $key => $piatto) {
                                                    if($key!=0){ echo ',';}
                                                    echo '"'.$piatto[1].'"';
                                                } ?>
                                          ]
                                }
                              ]
                            };
                            window.onload = function(){
                              var ctx = document.getElementById("canvas").getContext("2d");
                              
                              var chart = new Chart(ctx).HorizontalBar(barChartData, {
                                responsive: true,
                                barShowStroke: false,
                                scaleFontFamily:'Open Sans',
                                scaleFontColor: "#797979",
                                scaleFontSize: 14,
                                tooltipFontFamily:'Open Sans',
                                responsive:false
                              });
                            };
                     </script>

                  </div>
                  <!--custom chart end-->
              </div>
               <div class="col-lg-4">
                  <div class="border-head">
                      <h3>Comande attive</h3>
                  </div>
                  <div id="table-wrapper">
                    <table style="margin-top:0px !important;" id="lista-ordini-home" class="scroll table table-striped table-bordered table-hover table-selected table-home">
                        <thead>
                            <tr>
                                <th>Cameriere</th>
                                <th>Comanda</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                            $tav=0;
                            $index=null;
                            $query1 = "SELECT * 
                                          FROM Comande c 
                                          WHERE c.serata='$serata_attuale' AND c.attiva = 1
                                          ORDER BY c.attiva DESC, c.tavolo ASC, c.indice ASC";


                            if ($result=esegui_query($link,$query1)) {
                              while ($row1=mysqli_fetch_assoc($result)) {
                                echo '<tr class="rowlink" data-tavolo="'.$row1['tavolo'].'" data-indice="'.$row1['indice'].'">
                                              <td>'.$row1['responsabile'].'</td>
                                              <td>'.$row1['tavolo'].'/'.$row1['indice'].'</td>
                                      </tr>';
                              }
                            }
                        ?>
                        <script type="text/javascript">
                          $(document).ready(function(){
                            $('.rowlink').on('click', function(){
                                //var tavolo=$(this).find('td:nth-child(2)').text();
                                var t=$(this).data('tavolo');
                                var i=$(this).data('indice');
                                window.location.href = 'gestione_comande.php?tavolo='+t+'&indice='+i;
                            });
                          });
                        </script>
                        </tbody>
                    </table>
                </div>
               </div>
          </div>
          <?php
          
          }
        }
        disconnetti_mysql($link);
      ?>

  </section>

<?php
    require_once 'include/footer.inc.php';
?>