</section>
      <!--main content end-->
      <!-- Right Slidebar start -->
      <div class="sb-slidebar sb-right sb-style-overlay">
          <h5 class="side-title"> Info Magazzino</h5>
          <ul class="p-task tasks-bar">
              <li>
                  <a href="#">
                      <div class="task-info">
                          <div class="desc">Esempio</div>
                          <div class="percent">40%</div>
                      </div>
                      <div class="progress progress-striped">
                          <div style="width: 40%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="40" role="progressbar" class="progress-bar progress-bar-success">
                              <span class="sr-only">40% Rimasto</span>
                          </div>
                      </div>
                  </a>
              </li>
              <!--
              <li>
                  <a href="#">
                      <div class="task-info">
                          <div class="desc">Database Update</div>
                          <div class="percent">60%</div>
                      </div>
                      <div class="progress progress-striped">
                          <div style="width: 60%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="60" role="progressbar" class="progress-bar progress-bar-warning">
                              <span class="sr-only">60% Complete (warning)</span>
                          </div>
                      </div>
                  </a>
              </li>
              <li>
                  <a href="#">
                      <div class="task-info">
                          <div class="desc">Iphone Development</div>
                          <div class="percent">87%</div>
                      </div>
                      <div class="progress progress-striped">
                          <div style="width: 87%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="20" role="progressbar" class="progress-bar progress-bar-info">
                              <span class="sr-only">87% Complete</span>
                          </div>
                      </div>
                  </a>
              </li>
              <li>
                  <a href="#">
                      <div class="task-info">
                          <div class="desc">Mobile App</div>
                          <div class="percent">33%</div>
                      </div>
                      <div class="progress progress-striped">
                          <div style="width: 33%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="80" role="progressbar" class="progress-bar progress-bar-danger">
                              <span class="sr-only">33% Complete (danger)</span>
                          </div>
                      </div>
                  </a>
              </li>
              <li>
                  <a href="#">
                      <div class="task-info">
                          <div class="desc">Dashboard v1.3</div>
                          <div class="percent">45%</div>
                      </div>
                      <div class="progress progress-striped active">
                          <div style="width: 45%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="45" role="progressbar" class="progress-bar">
                              <span class="sr-only">45% Complete</span>
                          </div>
                      </div>

                  </a>
              </li>-->
          </ul>
      </div>
      <!-- Right Slidebar end -->
      <!--footer start-->
      <footer class="site-footer">
          <div class="text-center">
              2016 &copy; Manuel &amp; Federico.
              <a href="#" class="go-top">
                  <i class="fa fa-angle-up"></i>
              </a>
          </div>
      </footer>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="js/jquery-ui.js"></script>
    <script src="js/jquery-migrate-1.2.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script class="include" type="text/javascript" src="js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="js/jquery.scrollTo.min.js"></script>
    <script src="js/jquery.nicescroll.js" type="text/javascript"></script>
    <script src="js/respond.min.js" ></script>
    <script src="assets/bootstrap-notify-master/bootstrap-notify.min.js"></script>
    <script src="js/custom-notify.js"></script>
    <script src="assets/bootstrap-select/js/bootstrap-select.min.js"></script>
    <script src="js/alert-notify-system.js"></script>
    <script type="text/javascript">
      //chiudi serata
      $(document).ready(function(){
        $('#termina-serata-butt').on('click', function(){
          if (confirm("Sei sicuro di voler terminare la serata?") == false) {
            return false;
          }
          $.ajax({
              type: 'POST',
              url: "ajax/chiudi_serata.ajax.php",
              dataType: "text",
              timeout: 10000,
              data : {},
              beforeSend: function(){
              },
              success: function(result){
                  var errore = false;
                  if(stringStartsWith(result, '#error#')) errore=true;

                  if(errore) notify_top(result, 'Chiudi Serata'); 
                  else window.location.href = 'index.php';
              },
              error: function( jqXHR, textStatus, errorThrown ){
                  notify_top("#error#Errore durante l'operazione", 'Chiudi Serata'); 
              }
          });
        });
      });
    </script>
    

    <!--right slidebar-->
    <script src="js/slidebars.min.js"></script>

    <!--common script for all pages-->
    <script src="js/common-scripts.js"></script>

    <?php
    if($page_name == 'gestione_menu.php'){
      echo '<!--script for this page only-->
          <script type="text/javascript" src="assets/data-tables/jquery.dataTables.js"></script>
          <script type="text/javascript" src="assets/data-tables/DT_bootstrap.js"></script>
          <script src="js/editable-table-menu.js"></script>
          <script src="js/editable-table-menu-modal.js"></script>

          <script>
              $(document).ready(function() {
                  EditableTable.init();
                  //EditableTableModal.init();
              });
          </script>

          <script src="js/modal-menu.js"></script>
          ';

    } 
    else if($page_name == 'gestione_serate.php'){
      echo '<!--script for this page only-->
          <script type="text/javascript" src="assets/data-tables/jquery.dataTables.js"></script>
          <script type="text/javascript" src="assets/data-tables/DT_bootstrap.js"></script>
          <script src="assets/jquery-ui-datepicker/jquery-ui.js"></script>
          <script src="js/editable-table-serate.js"></script>
          <script src="js/editable-table-serate-modal.js"></script>

          <script>
              $(document).ready(function() {
                  EditableTable.init();
                  EditableTableModal.init();
              });
          </script>

          <script src="js/modal-serate.js"></script>
          ';

    }
    else if($page_name == 'gestione_portate.php'){
      echo '<!--script for this page only-->
          <script type="text/javascript" src="assets/data-tables/jquery.dataTables.js"></script>
          <script type="text/javascript" src="assets/data-tables/DT_bootstrap.js"></script>
          <script src="js/editable-table-portate.js"></script>
          <script src="js/editable-table-portate-modal.js"></script>

          <script>
              $(document).ready(function() {
                  EditableTable.init();
                  EditableTableModal.init();
              });
          </script>

          <script src="js/modal-portate.js"></script>
          ';

    }
    else if($page_name == 'gestione_magazzino.php'){
      echo '<!--script for this page only-->
          <script type="text/javascript" src="assets/data-tables/jquery.dataTables.js"></script>
          <script type="text/javascript" src="assets/data-tables/DT_bootstrap.js"></script>
          <script src="js/editable-table-magazzino.js"></script>

          <script>
              $(document).ready(function() {
                  EditableTable.init();
              });
          </script>
          ';

    }
    else if($page_name == 'gestione_responsabili.php'){
      echo '<!--script for this page only-->
          <script type="text/javascript" src="assets/data-tables/jquery.dataTables.js"></script>
          <script type="text/javascript" src="assets/data-tables/DT_bootstrap.js"></script>
          <script src="js/editable-table-responsabili.js"></script>

          <script>
              $(document).ready(function() {
                  EditableTable.init();
              });
          </script>
          ';

    }
    else if($page_name == 'gestione_piatti_serata.php'){
      echo '<!--script for this page only-->
          <script type="text/javascript" src="assets/data-tables/jquery.dataTables.js"></script>
          <script type="text/javascript" src="assets/data-tables/DT_bootstrap.js"></script>
          <script src="js/editable-table-piatti-serata.js"></script>

          <script>
              $(document).ready(function() {
                  EditableTable.init();
              });
          </script>
          ';

    }
    else if($page_name == 'gestione_zone.php'){
      echo '<!--script for this page only-->
          <script type="text/javascript" src="assets/data-tables/jquery.dataTables.js"></script>
          <script type="text/javascript" src="assets/data-tables/DT_bootstrap.js"></script>
          <script src="js/editable-table-zone.js"></script>

          <script>
              $(document).ready(function() {
                  EditableTable.init();
              });
          </script>
          ';

    }
    else if($page_name == 'gestione_tavoli.php'){
      echo '<!--script for this page only-->
          <script type="text/javascript" src="assets/data-tables/jquery.dataTables.js"></script>
          <script type="text/javascript" src="assets/data-tables/DT_bootstrap.js"></script>
          <script src="js/editable-table-tavoli.js"></script>

          <script>
              $(document).ready(function() {
                  EditableTable.init();
              });
          </script>
          ';

    }
    else if($page_name == 'responsabili_serata.php'){
      echo '<!--script for this page only-->
            <script src="js/responsabili-serata.js"></script>';

    }
    else if($page_name == 'responsabili_serata__.php'){
      echo '<!--script for this page only-->
            <script src="js/responsabili-zone-serata.js"></script>';

    }
    else if($page_name == 'gestione_comande.php'){
      echo '<!--script for this page only-->
            <script src="js/gestione_comande.js"></script>';
      if(isset($_GET['tavolo']) && is_numeric($_GET['tavolo']) && isset($_GET['indice']) && is_numeric($_GET['indice']) && $_GET['indice']>=1){
        
          echo '<script type="text/javascript">
            $(document).ready(function(){
              modificaComanda('.$_GET['tavolo'].', '.$_GET['indice'].');
            });
          </script>';

      }
    }
    else if($page_name == 'index.php'){
      echo '<script src="charts/Chart.min.js"></script>
            <script src="js/Chart.HorizontalBar.js"></script>
            <script src="js/index-ajax-update.js"></script>
            <script src="js/salva-prenotazioni.js"></script>';
    }



    //salva la pagina precedente (serve per il login)
    setPagePrevUrlSession();
    ob_end_flush();
    ?>


  </body>
</html>
