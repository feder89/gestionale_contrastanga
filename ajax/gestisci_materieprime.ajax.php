<?php
	require_once '../include/core.inc.php';

	if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();

		$nome = mysqli_real_escape_string($link, $_POST['nome']); //si fa sempre nelle variabili che metti in una query per sicurezza
  							
	  	$query = "DELETE FROM MateriePrime WHERE nome_materia='$nome'";

	  	if(esegui_query($link, $query)) echo "Materia prima \"$nome\" cancellata correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} else if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['genere']) && strlen($_POST['genere'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome']);
		$genere = mysqli_real_escape_string($link, $_POST['genere']);

		$query = "INSERT INTO MateriePrime (nome_materia, genere) VALUES ('$nome', '$genere')";

		if(!esegui_query($link, $query)){

			if(($present = esegui_query($link, "SELECT * FROM MateriePrime WHERE nome_materia = '$nome'")) && mysqli_num_rows($present)>=1) echo '#error#Materia prima già presente';
			else echo '#error#Errore durante l\'operazione';
		} 
		else echo "Materia prima \"$nome\" inserita correttamente!";

		disconnetti_mysql($link, NULL);

	}else if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['genere']) && strlen($_POST['genere'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='aggiorna'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome']);
		$genere = mysqli_real_escape_string($link, $_POST['genere']);

		$query = "UPDATE MateriePrime SET genere='$genere' WHERE nome_materia='$nome'";

		if(!esegui_query($link, $query)) echo '#error#Errore durante l\'operazione';
		else echo "MateriePrima \"$nome\" aggiornata correttamente!";

		disconnetti_mysql($link, NULL);
	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}
	
?>