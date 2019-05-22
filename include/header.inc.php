<?php
	require_once 'include/core.inc.php';

  //non fare la cache di queste pagine
  if($page_name=='gestione_menu.php' || $page_name=='gestione_serate.php' || $page_name=='gestione_portate.php' ){
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
  }

  if(!is_logged()){
    $_SESSION['pagina_precedente']=curPageURL();
    header("Location: login.php");
    die();
  }
    
?>

<!DOCTYPE html>
<html lang="en">
  
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Manuel Paccoia e Federico Quaglia">
    <link rel="shortcut icon" href="img/icon/favicon.ico" type="image/x-icon">

    <title>
        <?php
          if($page_name=='gestione_menu.php'){
              echo 'Gestione Menù';
          }
          else if($page_name=='gestione_serate.php'){
              echo 'Gestione Serate';
          }
          else if($page_name=='gestione_portate.php'){
              echo 'Gestione Portate';
          }
          else if($page_name=='gestione_magazzino.php'){
              echo 'Gestione Magazzino';
          }
          else if($page_name=='gestione_responsabili.php'){
              echo 'Gestione Responsabili';
          }  
          else if($page_name=='gestione_zone.php'){
              echo 'Gestione Zone';
          }
          else if($page_name=='gestione_tavoli.php'){
              echo 'Gestione Tavoli';
          }
          else if($page_name=='responsabili_serata.php'){
              echo 'Gestione Responsabili';
          }
          else if($page_name=='gestione_piatti_serata.php'){
              echo 'Gestione Piatti Serata';
          }
          else if($page_name=='statistiche_ordini.php'){
              echo 'Statistiche Serata';
          }
          else {
              echo 'Gestionale Contrastanga';
          }


        ?>
    </title>

    <!-- load jquery first -->
    <script type="text/javascript" src="js/jquery-1.12.1.js"></script>
    <script type="text/javascript">
      $(document).ready(function(){
          //set window min height
          var height_win = $(window).height(); 
          $('#main-content .site-min-height').css('min-height', (height_win-60)+'px');
          //same on window resize
          $(window).on('resize', function(){
              var height_win = $(window).height(); 
              $('#main-content .site-min-height').css('min-height', (height_win-60)+'px');
          });
      });

    </script>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="assets/bootstrap-select/css/bootstrap-select.css" rel="stylesheet">
    <link href="css/bootstrap-reset.css" rel="stylesheet">

    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/data-tables/DT_bootstrap.css">
    
    <!--right slidebar-->
    <link href="css/slidebars.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style-responsive.css" rel="stylesheet">

    <?php if($page_name == 'gestione_serate.php') echo '<link href="assets/jquery-ui-datepicker/jquery-ui.css" rel="stylesheet">'; ?>

    <link href="css/style_personalizzato.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

  <section id="container" class="">
      <!--header start-->
      <header class="header white-bg">
        <?php
        $data_att = ottieni_data_serata_attuale();
        if(!($data_att <=0 ))
          echo '<div id="serata-att" style="position:absolute; top:20px; left:50%; margin-left:-65px;">
            Serata del '.date_format(date_create($data_att),"d/m/Y").'
          </div>';
        ?>
          <div class="sidebar-toggle-box">
              <div data-original-title="Apri/Chiudi Barra di Navigazione" data-placement="right" class="fa fa-bars tooltips"></div>
          </div>
          <!--logo start-->
          <a href="index.php" class="logo" ><span class="l1">Contra</span><span class="l2">stanga</span> <!--<img src="img/png/logo.png" height="28">--></a>
          <!--logo end-->
          <div class="nav notify-row" id="top_menu">
            <!--  notification start -->
            <ul class="nav top-menu">
              <!-- notification dropdown start-->
              <li id="header_notification_bar" class="dropdown">
                  <a id="drodown-button-notify" data-toggle="dropdown" class="dropdown-toggle" href="#">

                      <i class="fa fa-bell-o"></i>
                  </a>
                  <ul id="not-list" class="dropdown-menu extended notification">
                      <div class="notify-arrow notify-arrow-yellow"></div>
                      <li class="not-remove">
                          <p id="text-top-not" class="yellow"></p>
                      </li>
                  </ul>
              </li>
              <!-- notification dropdown end -->
          </ul>
          </div>
          <div class="top-nav ">
              <ul class="nav pull-right top-menu">
                  <!--
                  <li>
                      <input type="text" class="form-control search" placeholder="Cerca">
                  </li>-->
                  <!-- user login dropdown start-->
                  <li class="dropdown">
                      <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                          <img alt="" src="img/profile-default.png" height="29" width="29">
                          <span class="username">
                          <?php
                            $link = connetti_mysql();
                            $query = "SELECT * FROM Utente WHERE username='".$_SESSION['username']."'";
                            
                            if(($res = esegui_query($link, $query)) && ($row = mysqli_fetch_assoc($res))){ echo $row['nome'].' '.$row['cognome'];}

                            disconnetti_mysql($link);
                          ?>
                          </span>
                          <b class="caret"></b>
                      </a>
                      <ul class="dropdown-menu extended logout">
                          <div class="log-arrow-up"></div>
                          <?php 
                            if(!(ottieni_data_serata_attuale() <=0 )){
                              echo '<li style="width:100%"><a id="termina-serata-butt" href="#"><i class="fa fa-flag-checkered" aria-hidden="true"></i> Termina Serata</a></li>';
                            
                              echo '<li style="width:100%"><a id="termina-serata-butt" href="excel_export/esporta_comande_serata.php"><i class="fa fa-download" aria-hidden="true"></i> Esporta Serata Excel</a></li>';
                            }
                            else
                              echo '<li style="width:100%; height:10px;"></li>';
                          ?>
                          <li><a href="logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i> Esci</a></li>
                      </ul>
                  </li>
                  <!-- user login dropdown end -->
                  <li class="sb-toggle-right">
                      <i class="fa  fa-align-right"></i>
                  </li>
              </ul>
          </div>
      </header>
      <!--header end-->
      <!--sidebar start-->
      <aside>
          <div id="sidebar"  class="nav-collapse ">
              <!-- sidebar menu start-->
              <ul class="sidebar-menu" id="nav-accordion">
                  <li >
                      <a href="index.php" <?php if($page_name == 'index.php') echo 'class="active"';?>>
                          <i class="fa fa-dashboard"></i>
                          <span>Dashboard</span>
                      </a>
                  </li>

                  <?php 
                    $pagine_gestione = array('gestione_portate.php', 'gestione_menu.php', 'gestione_serate.php', 'gestione_magazzino.php', 'gestione_responsabili.php', 'gestione_zone.php', 'gestione_tavoli.php');
                  ?>

                  <!--multi level menu start-->
                  <li class="sub-menu">
                      <a href="javascript:;" <?php if(in_array($page_name, $pagine_gestione)) echo 'class="active"';?>>
                          <i class="fa fa-file-text"></i>
                          <span>Gestione</span>
                      </a>
                      <ul class="sub">
                          <li <?php if($page_name == 'gestione_magazzino.php') echo 'class="active"';?>><a href="gestione_magazzino.php">Gestione Magazzino</a></li>
                          <li <?php if($page_name == 'gestione_portate.php') echo 'class="active"';?> ><a href="gestione_portate.php">Gestione Portate</a></li>
                          <li <?php if($page_name == 'gestione_menu.php') echo 'class="active"';?>><a href="gestione_menu.php">Gestione Menù</a></li>
                          <li <?php if($page_name == 'gestione_serate.php') echo 'class="active"';?>><a href="gestione_serate.php">Gestione Serate</a></li>
                          <li <?php if($page_name == 'gestione_responsabili.php') echo 'class="active"';?>><a href="gestione_responsabili.php">Gestione Responsabili</a></li>
                          <li <?php if($page_name == 'gestione_zone.php') echo 'class="active"';?>><a href="gestione_zone.php">Gestione Zone</a></li>              
                          <li <?php if($page_name == 'gestione_tavoli.php') echo 'class="active"';?>><a href="gestione_tavoli.php">Gestione Tavoli</a></li>
                          <li <?php if($page_name == 'statistiche_ordini.php') echo 'class="active"';?>><a href="statistiche_ordini.php">Statistiche Serata</a></li>                 
                      </ul>
                  </li>
                  <!--multi level menu end-->

              </ul>
              <!-- sidebar menu end-->
          </div>
      </aside>
      <!--sidebar end-->
      <!--main content start-->
      <section id="main-content">
