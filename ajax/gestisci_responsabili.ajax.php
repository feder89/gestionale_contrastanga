<?php
	require_once '../include/core.inc.php';
	if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['cognome']) && strlen($_POST['cognome'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome']);
		$cognome = mysqli_real_escape_string($link, $_POST['cognome']);
  							
	  	$query = "DELETE FROM Responsabili WHERE nome='$nome $cognome'";

	  	if(esegui_query($link, $query)) echo "Responsabile\"$nome $cognome\" cancellato correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} else if(isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['cognome']) && strlen($_POST['cognome'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome']);
		$cognome = mysqli_real_escape_string($link, $_POST['cognome']);
		$query = "INSERT INTO Responsabili (nome) VALUES ('$nome $cognome')";

		if(!esegui_query($link, $query)){

			if(($present = esegui_query($link, "SELECT * FROM Responsabili WHERE nome='$nome $cognome'")) && mysqli_num_rows($present)>=1) echo '#error#Responsabile già presente';
			else echo '#error#Errore durante l\'operazione';
		} 
		else echo "Responsabile \"$nome $cognome\" inserito correttamente!";

		disconnetti_mysql($link, NULL);

	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}
	
?>