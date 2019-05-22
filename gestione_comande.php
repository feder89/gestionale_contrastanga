<?php
  require_once 'include/header.inc.php';
?>

<section class="wrapper site-min-height">
  <!-- page start-->
  <section class="panel">
        <header class="panel-heading">
            Gestione Comande
        </header>
        <div class="panel-body">
            <div id="wrapper-left">
                <div id="buttons-wrapper">
                  <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Tutte</a></li>
                    <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Aperte</a></li>
                    <li role="presentation"><a href="#messages" aria-controls="messages" role="tab" data-toggle="tab">Chiuse</a></li>
                  </ul>
                </div>
                <div id="table-wrapper">
                    <table style="margin-bottom:0px !important;" class="scroll table table-striped table-bordered table-hover table-selected" id="lista-ordini">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Cameriere</th>
                                <th>Com.</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $link = connetti_mysql();

                            $serata_attuale = ottieni_data_serata_attuale();
                            if($serata_attuale <= 0){
                              echo '<tr><td colspan="3">Errore, ricarica la pagina</td></tr>';
                            }
                            else{

                                $query = "SELECT * 
                                          FROM Comande c 
                                          WHERE c.serata='$serata_attuale'
                                          ORDER BY c.attiva DESC, c.tavolo ASC, c.indice ASC";

                                if (($result=esegui_query($link,$query))) {
                                    while ($row1=mysqli_fetch_assoc($result)) {
                                        echo '<tr data-attiva="'.$row1['attiva'].'" data-tavolo="'.$row1['tavolo'].'" data-indice="'.$row1['indice'].'">
                                                  <td><i style="position:relative;" class="fa fa-list-alt '.( $row1['attiva'] == 1 ? 'attiva' : '').'" aria-hidden="true">'.
                                                      ( $row1['attiva'] == 1 && $row1['conto_inviato'] == 1 ? '<i class="fa fa-paper-plane absolute-icon" aria-hidden="true"></i>' : '').
                                                      ( $row1['attiva'] == 0 ? ( $row1['pagata'] == 1 ? '<i class="fa fa-eur absolute-icon eur" aria-hidden="true"></i>' : '<i class="fa fa-times absolute-icon times" aria-hidden="true"></i>') : '')

                                                  .'</i></td>
                                                  <td>'.$row1['responsabile'].'</td>
                                                  <td>'.$row1['tavolo'].'/'.$row1['indice'].'</td>
                                                  <td>'.$row1['num_comanda'].'</td>
                                          </tr>';
                                    }
                                }
                            }
                            disconnetti_mysql($link);
                        ?>
                        </tbody>
                    </table>
                </div>
                <div id="conteggio-posti">
                    <?php
                      $serata_attuale = ottieni_data_serata_attuale();
                      if($serata_attuale <= 0){
                        echo 'Errore, ricarica la pagina';
                      }
                      else {
                        $link = connetti_mysql();

                        $nome_portata_posti = 'pane e coperto';

                        $query_posti = "SELECT DISTINCT t.*
                                              FROM Tavoli t
                                              INNER JOIN ResponsabiliSerata rs ON rs.tavolo = t.numero_tavolo
                                              AND rs.serata = '$serata_attuale'";

                        //vedi solo le comande attive!!
                        $query_posti_occupati = "SELECT c.*, o.quantita AS numero_persone
                                                 FROM Comande c
                                                 INNER JOIN Ordini o ON o.serata = c.serata AND o.tavolo = c.tavolo AND o.indice = c.indice
                                                 WHERE c.serata = '$serata_attuale' AND c.attiva=1 AND LOWER(o.portata) = '$nome_portata_posti'";

                        if(($res = mysqli_query($link, $query_posti)) && ($res2 = mysqli_query($link, $query_posti_occupati))){
                          $posti_totali = 0;
                          $posti_occupati = 0;
                          while(($row = mysqli_fetch_assoc($res))){
                            $posti_totali+= $row['posti'];
                          }
                          while(($row = mysqli_fetch_assoc($res2))){
                            $posti_occupati+= $row['numero_persone'];
                          }
                          echo '<p class="pocc">Posti Occupati<span>'.$posti_occupati.'</span></p>';
                          echo '<p class="plib">Posti Liberi<span>'.( ($posti_totali-$posti_occupati) < 0 ? 0 : $posti_totali-$posti_occupati).'</span></p>';
                          echo '<p style="margin-bottom: 0;" class="ptot">Posti Totali<span>'.$posti_totali.'</span></p>';
                        } 
                        else echo 'Errore, ricarica la pagina';
                      }
                    ?>
                </div>
            </div>
            <div id="wrapper-right">
                <div id="selezione-tavoli">
                    <?php
                        
                        $serata_attuale = ottieni_data_serata_attuale();
                        if($serata_attuale <= 0){
                          echo 'Errore, ricarica la pagina';
                        }
                        else{
                            $link = connetti_mysql();

                            $query_tav = "SELECT t.numero_tavolo, t.zona, rs.responsabile
                                                  FROM Tavoli t
                                                  INNER JOIN ResponsabiliSerata rs ON rs.tavolo = t.numero_tavolo
                                                  AND rs.serata = '$serata_attuale'
                                                  WHERE rs.numero_progressivo = (
                                                      SELECT MAX(rs2.numero_progressivo)
                                                      FROM ResponsabiliSerata rs2
                                                      WHERE rs2.serata = '$serata_attuale' AND rs2.tavolo = t.numero_tavolo
                                                      GROUP BY rs2.serata, rs2.tavolo
                                                  )
                                                  ORDER BY t.numero_tavolo, t.zona ASC";

                            $tavoli = array();

                            if(($res = mysqli_query($link, $query_tav))){
                                while(($row = mysqli_fetch_assoc($res))){
                                    $tavoli[$row['zona']][] = array('tavolo' => $row['numero_tavolo'], 'responsabile' => $row['responsabile']);
                                }
                            }

                            foreach ($tavoli as $zona => $tavoli_zona) {
                                echo '<div class="box-group">';
                                foreach ($tavoli_zona as $key => $info_tavolo) {
                                    echo '<button type="button" data-zona="'.$zona.'" data-tavolo="'.$info_tavolo['tavolo'].'" data-responsabile="'.$info_tavolo['responsabile'].'" class="btn btn-default nuova_comanda">'.$info_tavolo['tavolo'].'</button>';
                                }
                                echo '</div>';
                            }

                            disconnetti_mysql($link);
                        }

                    ?>         
                </div>
                <div id="nuova_comanda" style="display: none;">
                    <div id="bottonigetcom">
                    </div>
                    <select data-live-search="true" class="selectpicker" data-size="10" id="menu_name" name="menu_name">
                        <option value=""></option>
                        <?php
                            $fiss=0;
                            $serata_attuale = ottieni_data_serata_attuale();
                            if($serata_attuale <= 0){
                              echo '<option value="">Errore, ricarica la pagina</option>';
                            }
                            else{
                                $link = connetti_mysql();

                                $date = $serata_attuale;

                                $res = null;
                                if(($res = mysqli_query($link, "SELECT * FROM MenuSerata WHERE serata = '$date'"))){
                                    while(($row = mysqli_fetch_assoc($res))){
                                        echo '<option value="'.$row['menu'].'">'.$row['menu'].'</option>';
                                        if($row['fisso']==1){
                                          $fiss=1;
                                        }
                                    }
                                }

                                disconnetti_mysql($link, $res);
                            }
                        ?>
                    </select>
                    <button type="button" class="btn btn-default info-tavolo"></button>
                    <button type="button" class="btn btn-default info-responsabile">Responsabile: <span></span></button>
                    <!--<button type="button" class="btn btn-default info-zona">Zona: <span></span></button>-->
                    <div id="bottoniright">
                    </div>
                    <div id="wrapper-soci">
                      <div id="wrapper-soci-left">
                        <div id="soci-int">
                          <div id="numero-soci-wrapper">
                            <input id="numero-soci" min="0" step="1" type="number" class="form-control small num"  placeholder="0"> <span>Numero Soci</span>
                          </div>
                          <div id="sconto-manuale-wrapper">
                            <input id="sconto-manuale" min="0" step="any" type="number" class="form-control small num"  placeholder="0"> <span>Sconto (in euro)</span>
                          </div>
                          <?php
                            if($fiss==1)
                              echo '<div id="bott-persona-wrapper">
                            <input id="bott-persona" min="0" step="1" type="number" class="form-control small num"  placeholder="0"> <span>Num. bott.</span>
                          </div>';
                          ?>
                        </div>
                        <input style="margin-top:5px;" id="annotazioni" type="text" class="form-control"  placeholder="Annotazioni">
                      </div>
                      <div id="totale-parziale-wrapper">
                        <div style="margin-bottom:5px;">
                          <span>Tot. parziale</span><input id="totale-parziale" disabled type="number" class="form-control small num" value="0" placeholder="0">
                        </div>
                        <div>
                          <span>Tot. persona</span><input id="totale-parziale-persona" disabled type="number" class="form-control small num" value="0" placeholder="0">
                        </div>
                      </div>
                    </div>
                    <div id="lista-piatti-menu">
                        <table style="margin-top:20px !important;" class="scroll table table-striped table-bordered table-selected" id="lista-piatti">
                        <thead>
                            <tr>
                                <th>Prezzo</th>
                                <th>Portata</th>
                                <th>Categoria</th>
                                <th>Rim.</th>
                                <th>#</th>
                                <th>Quantità</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    </div>
                    <div id="bottoni-comanda-mod">
                    </div>
                </div>
            </div>
        </div>
    </section>
  <!-- page end-->
</section>

<!-- Modal -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="modal-gest" class="modal fade" style="display: none;">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title" id="modal-titolo"></h4>
          </div>
          <div class="modal-body">
          </div>
          <div class="modal-footer">
              <button data-dismiss="modal" class="btn btn-default" type="button">Chiudi</button>
          </div>
      </div>
  </div>
</div>
<!-- Modal -->

<?php
  require_once 'include/footer.inc.php';
?>