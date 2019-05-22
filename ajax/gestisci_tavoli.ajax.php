<?php
	require_once '../include/core.inc.php';

	if( isset($_POST['numero']) && strlen($_POST['numero'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();

		$numero = mysqli_real_escape_string($link, $_POST['numero']); //si fa sempre nelle variabili che metti in una query per sicurezza
  							
	  	$query = "DELETE FROM Tavoli WHERE numero_tavolo='$numero'";

	  	if(esegui_query($link, $query)) echo "Tavolo \"$numero\" cancellato correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} else if( isset($_POST['numero']) && strlen($_POST['numero'])>0 && isset($_POST['zona']) && strlen($_POST['zona'])>0 && isset($_POST['zona']) && strlen($_POST['zona'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$numero = mysqli_real_escape_string($link, $_POST['numero']);
		$zona = mysqli_real_escape_string($link, $_POST['zona']);
		$posti = mysqli_real_escape_string($link, $_POST['posti']);

		$query = "INSERT INTO Tavoli (numero_tavolo, zona, posti) VALUES ('$numero', '$zona', '$posti')";

		if(!esegui_query($link, $query)){

			if(($present = esegui_query($link, "SELECT * FROM Tavoli WHERE numero_tavolo = '$numero'")) && mysqli_num_rows($present)>=1) echo '#error#Tavolo già presente';
			else echo '#error#Errore durante l\'operazione';
		} 
		else echo "Tavolo \"$numero\" inserito correttamente!";

		disconnetti_mysql($link, NULL);

	}else if( isset($_POST['numero']) && strlen($_POST['numero'])>0 && isset($_POST['zona']) && strlen($_POST['zona'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='aggiorna'){

		$link = connetti_mysql();
		$numero = mysqli_real_escape_string($link, $_POST['numero']);
		$zona = mysqli_real_escape_string($link, $_POST['zona']);
		$posti = mysqli_real_escape_string($link, $_POST['posti']);

		$query = "UPDATE Tavoli SET zona='$zona', posti='$posti'  WHERE numero_tavolo='$numero'";

		if(!esegui_query($link, $query)) echo '#error#Errore durante l\'operazione';
		else echo "Tavolo \"$numero\" aggiornato correttamente!";

		disconnetti_mysql($link, NULL);
	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}
	
?>