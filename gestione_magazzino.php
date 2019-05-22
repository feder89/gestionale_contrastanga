<?php
  require_once 'include/header.inc.php';
?>

<section class="wrapper site-min-height">
  <!-- page start-->
  <section class="panel">
      <header class="panel-heading">
          Gestione Magazzino
      </header>
      <div class="panel-body">
          <div class="adv-table editable-table ">
              <div class="clearfix">
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
              </div>
              <div class="space15"></div>
                  
                  <table class="table table-striped table-hover table-bordered portate" id="editable-sample">
                    <thead>
                      <tr>
                        <th>Nome Materia</th>
                        <th>Genere</th>
                        <th>Data creazione</th>
                        <th>Ultimo aggiornamento</th>
                        <th>Modifica</th>
                        <th>Cancella</th>
                      </tr>
                  </thead>
                  <tbody>

                  <?php

                      $link = connetti_mysql();
            
                      $query = "SELECT * FROM MateriePrime WHERE 1";
                      $res = esegui_query($link, $query);

                      while ( $row = mysqli_fetch_assoc($res)){

                         echo '<tr>
                              <td>'.$row['nome_materia'].'</td>
                              <td>'.$row['genere'].'</td>
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