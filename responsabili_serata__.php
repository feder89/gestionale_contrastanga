<?php
  if(!isset($_GET['procedura']) || ( $_GET['procedura']!='init' && $_GET['procedura']!='modifica')){
    header("location: index.php");
    exit;
  }
  require_once 'include/header.inc.php';

?>

<section class="wrapper site-min-height">
  <!-- page start-->
  <section class="panel">
    <header class="panel-heading">
        Gestione Responsabili Serata
    </header>
    <div class="panel-body">
      <div class="adv-table editable-table">
          <div class="clearfix">
              <select class="selectpicker" id="select_responsabile" data-live-search="true">
                <option value=""></option>
                <?php
                  $link = connetti_mysql();

                  if( ( $res=mysqli_query($link, "SELECT nome FROM Responsabili ORDER BY nome ASC"))){
                    while($row=mysqli_fetch_assoc($res)){
                        echo '<option value="'.$row['nome'].'">'.$row['nome'].'</option>';
                    }

                  }

                  disconnetti_mysql($link,$res);

                ?>
              </select>
              <?php
              if( $_GET['procedura']=='init'){
              ?> 
                <div class="btn-group">
                    <button style="margin-left:5px;" id="bottone_associa" class="btn">
                        Associa <i class="fa fa-angle-double-right"></i>
                    </button>
                </div>
                <div class="btn-group">
                    <button style="margin-left:5px;" id="bottone_disassocia" class="btn green">
                        Rimuovi <i class="fa fa-angle-double-left"></i>
                    </button>
                </div>
              <?php
              }
              else{
              ?> 
                <div class="btn-group">
                    <button style="margin-left:5px;" id="bottone_associa_mod" class="btn">
                        Associa <i class="fa fa-angle-double-right"></i>
                    </button>
                </div>
                <div class="btn-group">
                    <button style="margin-left:5px;" id="bottone_disassocia_mod" class="btn green">
                        Rimuovi <i class="fa fa-angle-double-left"></i>
                    </button>
                </div>
              <?php
              }
              ?> 
              <?php
              if( $_GET['procedura']=='init'){
              ?>  
                <a id="bottone-continua" style="margin-left:20px;" href="#" class="btn btn-success continua" role="button">
                    Continua<span style="margin-left:7px;" class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                </a>
              <?php
              }
              else{
              ?> 
                <a id="bottone-continua" style="margin-left:20px;" href="#" class="btn btn-success" role="button">
                    Torna alla Dashboard<span style="margin-left:7px;" class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                </a>
              <?php
              }
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
                  
          <table style="margin-top:40px;" class="table table-striped table-bordered table-selected" id="editable-sample">
            <thead>
              <tr>
                <th></th>
                <!--<th>Tavolo</th>-->
                <th>Zona</th>
                <th>Responsabile</th>
              </tr>
            </thead>
            <tbody>

            <?php
              $date=time();
              $date=date('Y-m-d', $date);
              $link = connetti_mysql();

              $serata_attuale = ottieni_data_serata_attuale();
              if($serata_attuale == 0){
                echo '<tr><td colspan="4" style="text-align:center;">Errore, ricarica la pagina</td></tr>';
              }
              else{

                if($serata_attuale == -1){ $serata_attuale = $date; }

                if( ( $res=mysqli_query($link, "SELECT t.zona, rs.responsabile
                                                FROM Tavoli t
                                                LEFT JOIN ResponsabiliSerata rs ON rs.tavolo = t.numero_tavolo
                                                AND rs.serata = '$serata_attuale'
                                                WHERE rs.numero_progressivo IS NULL OR rs.numero_progressivo = (
                                                    SELECT MAX(rs2.numero_progressivo)
                                                    FROM ResponsabiliSerata rs2
                                                    WHERE rs2.serata = '$serata_attuale' AND rs2.tavolo = t.numero_tavolo
                                                    GROUP BY rs2.serata, rs2.tavolo
                                                )
                                                ORDER BY t.numero_tavolo ASC"))){
                    $zona_prec=null;
                  while($row=mysqli_fetch_assoc($res)){
                        if($zona_prec!=$row['zona']){
                            echo "<tr ".( isset($row['responsabile']) ? 'class="selected-green"' : "").">";
                                echo '<td style="text-align:center; width:25px;"><input type="checkbox"></td>';
                                //echo "<td>". $row['numero_tavolo'] .'</td>';
                                echo "<td>". $row['zona'] .'</td>';
                                echo "<td>". ( isset($row['responsabile']) ? $row['responsabile'] : "") .'</td>';
                            echo "</tr>";
                        }
                        $zona_prec=$row['zona'];
                  }
                }

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
