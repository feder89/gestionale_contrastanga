<?php
  require_once 'include/header.inc.php';
?>

<section class="wrapper site-min-height">
  <!-- page start-->
  <section class="panel">
      <header class="panel-heading">
          Gestione Tavoli
      </header>
      <div class="panel-body">
          <div class="adv-table editable-table ">
              <div class="clearfix">
                  <div class="btn-group">
                      <button id="editable-sample_new" style="margin-top:-4px;" class="btn green">
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
                        <th>Tavolo N.ro</th>
                        <th>Zona</th>
                        <th>Posti</th>
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
                      $res1=esegui_query($link,"SELECT * FROM Zone WHERE 1");
                      $place=array();
                      while($riga=mysqli_fetch_assoc($res1)){
                        $place[]=$riga['zona'];
                      }
                      mysqli_free_result($res1);
                      $query = "SELECT * FROM Tavoli WHERE 1";
                      $res = esegui_query($link, $query);
                      while ( $row = mysqli_fetch_assoc($res)){
                          echo '<tr>
                              <td>'.$row['numero_tavolo'].'</td>
                              <td>'.$row['zona'].'</td>
                              <td>'.$row['posti'].'</td>
                              <td>'.date( 'd/m/Y H:i:s', strtotime($row['data_creazione']) ).'</td>
                              <td>'.date( 'd/m/Y H:i:s', strtotime($row['data_aggiornamento']) ).'</td>
                              <td><a class="edit" href="javascript:;">Modifica</a></td>
                              <td><a class="delete" href="javascript:;">Cancella</a></td>
                            </tr>';
                      }
                      
                      disconnetti_mysql($link,$res);
                  ?> 
                  <script type="text/javascript">
                      var place_js = <?php echo json_encode($place); ?>;
    
                  </script>
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