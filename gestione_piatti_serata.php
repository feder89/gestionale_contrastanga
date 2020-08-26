<?php
  if(!isset($_GET['procedura']) || $_GET['procedura'] != 'init'){
    header('Location: index.php');
    die();
  }

  require_once 'include/header.inc.php';
?>

<section class="wrapper site-min-height">
  <!-- page start-->
  <section class="panel">
      <header class="panel-heading">
          Gestione Quantità Piatti della Serata
      </header>
      <div class="panel-body">
          <div class="adv-table editable-table ">
              <div class="clearfix">
                  <div class="btn-group">
                      <button id="editable-sample_new" style="margin-top:-4px;" class="btn green">
                          Aggiungi <i class="fa fa-plus"></i>
                      </button>
                  </div>
                  <input id="quantita-default" step="1" min="0" style="display: inline-block; width:200px; margin-left:15px;" type="number" class="form-control small num"  placeholder="Quantità predefinita">
                  <?php
                    if(isset($_GET['tasto']) && $_GET['tasto']=='dashboard')
                      echo '<a id="bottone-torna-dashboard" style="margin-top:-4px; margin-left:15px;" href="index.php" class="btn btn-success" role="button">Torna alla Dashboard<span style="margin-left:7px;" class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>';
                    else 
                      echo '<a id="bottone-continua" style="margin-top:-4px; margin-left:15px;" href="#" class="btn btn-success" role="button">Continua<span style="margin-left:7px;" class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>';

                  ?>
                  <div class="btn-group pull-right">
                      <button class="btn dropdown-toggle" data-toggle="dropdown">Strumenti <i class="fa fa-angle-down"></i>
                      </button>
                      <ul class="dropdown-menu pull-right">
                          <li><a href="#">Stampa</a></li>
                          <li><a href="#">Salva come PDF</a></li>
                      </ul>
                  </div>
              </div>
              <div class="space15"></div>
                  
                  <table class="table table-striped table-hover table-bordered" id="editable-sample">
                    <thead>
                      <tr>
                        <th>Piatto</th>
                        <th>Quantità</th>
                        <th>Data creazione</th>
                        <th>Ultimo aggiornamento</th>
                        <th>Modifica</th>
                        <th>Cancella</th>
                      </tr>
                  </thead>
                  <tbody>
                  <?php

                      $link = connetti_mysql();

                      $now=time();
                      $date=date('Y-m-d', $now);
            
                      $query = "SELECT * FROM QuantitaPiattiSerata WHERE serata='$date'";
                      $res = esegui_query($link, $query);

                      while ( $row = mysqli_fetch_assoc($res)){
                         echo '<tr>
                              <td>'.$row['piatto'].'</td>
                              <td>'.$row['quantita'].'</td>
                              <td>'.date( 'd/m/Y H:i:s', strtotime($row['data_creazione']) ).'</td>
                              <td>'.date( 'd/m/Y H:i:s', strtotime($row['data_aggiornamento']) ).'</td>
                              <td><a class="edit" href="javascript:;">Modifica</a></td>
                              <td><a class="delete" href="javascript:;">Cancella</a></td>
                            </tr>';
                      }

                      disconnetti_mysql($link,$res);
                  ?> 
                  
                  </tbody>
              </table>
          </div>
      </div>
  </section>
  <!-- page end-->
</section>


<?php
  require_once 'include/footer.inc.php';
?>