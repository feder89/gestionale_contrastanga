<?php
	require_once '../include/core.inc.php';

	if( isset($_POST['zona']) && strlen($_POST['zona'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();

		$zona = mysqli_real_escape_string($link, $_POST['zona']); //si fa sempre nelle variabili che metti in una query per sicurezza
  							
	  	$query = "DELETE FROM Zone WHERE zona='$zona'";

	  	if(esegui_query($link, $query)) echo "Zona \"$zona\" cancellata correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} else if( isset($_POST['zona']) && strlen($_POST['zona'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$zona = mysqli_real_escape_string($link, $_POST['zona']);

		$query = "INSERT INTO Zone (zona) VALUES ('$zona')";

		if(!esegui_query($link, $query)){

			if(($present = esegui_query($link, "SELECT * FROM Zone WHERE zona = '$zona'")) && mysqli_num_rows($present)>=1) echo '#error#Zona già presente';
			else echo '#error#Errore durante l\'operazione';
		} 
		else echo "Zona \"$zona\" inserita correttamente!";

		disconnetti_mysql($link, NULL);

	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}
	
?>