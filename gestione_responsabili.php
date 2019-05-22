<?php
  require_once 'include/header.inc.php';

?>

<section class="wrapper site-min-height">
  <!-- page start-->
  <section class="panel">
      <header class="panel-heading">
          Gestione Responsabili
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
                  
                  <table class="table table-striped table-hover table-bordered" id="editable-sample">
                    <thead>
                      <tr>
                        <th>Nome</th>
                        <th>Cognome</th>
                        <th>Data creazione</th>
                        <th>Ultimo aggiornamento</th>
                        <th>Modifica</th>
                        <th>Cancella</th>
                      </tr>
                  </thead>
                  <tbody>
                  <?php

                      $link = connetti_mysql();
            
                      $query = "SELECT * FROM Responsabili WHERE 1";
                      $res = esegui_query($link, $query);

                      while ( $row = mysqli_fetch_assoc($res)){
                         $nome = explode(' ', $row['nome'],2);
                         echo '<tr>
                              <td>'.$nome[0].'</td>
                              <td>'.$nome[1].'</td>
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

<!-- Modal -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="modal-gest" class="modal fade" style="display: none;">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title" id="modal-titolo"></h4>
          </div>
          <div class="modal-body">
              <div class="adv-table editable-table">
                <div class="clearfix">
                    <div class="btn-group">
                        <button id="modal-gest-table_new" class="btn green">
                            Aggiungi <i class="fa fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="space15"></div>
                    
                <table class="table table-striped table-hover table-bordered" id="modal-gest-table">
                  <thead>
                    <tr>
                      <th>ID utente</th>
                      <th>Nome</th>
                      <th>Cognome</th>
                      <th>Modifica</th>
                      <th>Cancella</th>
                    </tr>
                  </thead>
                  <tbody>               
                  </tbody>
                </table>
            </div>

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