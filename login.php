<?php
	require_once 'include/core.inc.php';

	//se già loggato, rimandalo indietro
	if(is_logged()){
    	if(isset($_SESSION['pagina_precedente']) && !empty($_SESSION['pagina_precedente'])){
			header("Location: ".$_SESSION['pagina_precedente']);
			die();
		} else {
			header("Location: index.php");
			die();
		}
  	} else {
  		//controlla se sono settati i coockie (ricordami)
  		if(isset($_COOKIE['username']) && isset($_COOKIE['password']) && isset($_COOKIE['quintana'])){
  			//loggati con i cookie
  			if(login($_COOKIE['username'], $_COOKIE['password'], $_COOKIE['quintana'], false, true)){
  				//redirect
  				if(isset($_SESSION['pagina_precedente']) && !empty($_SESSION['pagina_precedente'])){
  					header("Location: ".$_SESSION['pagina_precedente']);
    				die();
  				} else {
  					header("Location: index.php");
    				die();
  				}
  			}
  		}
  	}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Manuel Paccoia e Federico Quaglia">
    <link rel="shortcut icon" href="img/favicon.html">

    <title>Login Gestionale Rione Contrastanga</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style-responsive.css" rel="stylesheet" />

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 tooltipss and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
</head>

  <body class="login-body">
  	<?php
  		$logged = false;
  		$wrong_data = false;
  		if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])){
  			//effettuo login
  			$remember = false;
  			if(isset($_POST['ricordami']) && $_POST['ricordami']=='ricordami') $remember = true;
			if(isset($_POST['quintana']) && $_POST['quintana'] != 'Scegli'){
				$_SESSION['quintana']=$_POST['quintana'];
				if(login($_POST['username'], $_POST['password'], $_POST['quintana'], $remember)){
					$logged=true;
					//redirect
					if(isset($_SESSION['pagina_precedente']) && !empty($_SESSION['pagina_precedente'])){
						header("Location: ".$_SESSION['pagina_precedente']);
						die();
					} else {
						header("Location: index.php");
						die();
					}
				} else {
					$wrong_data = true;
				}
			} else{
				$message = "Nessuna Quintana selezionata, scegline una.";
				echo "<script type='text/javascript'>alert('$message');</script>";
			}
  		}

  		if(!$logged){
  	?>
		    <div class="container">
		      	<form class="form-signin" action="login.php" method="POST">
		        	<h2 class="form-signin-heading">esegui l'accesso</h2>
		        	<div class="login-wrap">
		        		<?php
		        		if($wrong_data) echo '<div class="alert alert-danger" role="alert">Combinazione Username/Password errata!</div>';
		        		?>
			            <input type="text" class="form-control" name="username" placeholder="Username" autofocus required>
			            <input type="password" class="form-control" name="password" placeholder="Password" required>
						<?php
							isset($_POST["quintana"]) ? $quintana = $_POST["quintana"] : $quintana="Scegli";
						?>
	<?php

     	$link = connetti_mysql_info_schema();

      	$query = "SELECT * FROM SCHEMATA WHERE SCHEMA_NAME LIKE 'gestionale_%_%'";
      	$res = esegui_query($link, $query);
		echo '<select class="form-control" id="quintana" name="quintana">
				<option ';
				if ($quintana == "Scegli" ){ echo ' selected ';} 
				echo 'value="Scegli">Scegli Quintana</option>';
      	while ( $row = mysqli_fetch_assoc($res)){
      		echo '<option ';
      		if ($quintana == str_replace("gestionale_", "", $row["SCHEMA_NAME"])){echo ' selected ';} 
      			echo' value='.str_replace("gestionale_", "", $row["SCHEMA_NAME"]).'>';
	      	if (strpos($row["SCHEMA_NAME"],"giu")){
	      		echo 'Giugno '.substr($row['SCHEMA_NAME'], -4);
	  		}
	  		elseif (strpos($row["SCHEMA_NAME"],"sett")){
	  			echo 'Settembre '.substr($row['SCHEMA_NAME'], -4);
	  		}
	      		echo '</option>';
     	}
          echo '</select>';

          disconnetti_mysql($link,$res);
  	?> 
						
			            <label class="checkbox">
			                <input type="checkbox" name="ricordami" value="ricordami">Ricordami
			                <span class="pull-right">
			                    <a data-toggle="modal" href="#myModal"> Non ricordi la password?</a>

			                </span>
			            </label>
			            <button class="btn btn-lg btn-login btn-block" type="submit">Accedi</button>
		        	</div>
		        </form>

	          	<!-- Modal -->
	          	<div aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
	              	<div class="modal-dialog">
	                  	<div class="modal-content">
	                      	<div class="modal-header">
	                          	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	                          	<h4 class="modal-title">Non ricordi la password?</h4>
	                      	</div>
	                      	<div class="modal-body">
	                          	<p>Richiedi una nuova password inserendo il tuo username</p>
	                          	<input type="text" name="username" placeholder="Username" autocomplete="off" class="form-control placeholder-no-fix" required>

	                      	</div>
	                      	<div class="modal-footer">
	                          	<button data-dismiss="modal" class="btn btn-default" type="button">Annulla</button>
	                          	<button class="btn btn-success" type="button">Invia Richiesta</button>
	                      	</div>
	                  	</div>
	              	</div>
	          	</div>
	          	<!-- modal -->

		    </div>
	<?php
		}
	?>


    <!-- js placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>
