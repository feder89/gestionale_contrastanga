<?php
	require_once '../include/core.inc.php';
	$categorie = [ 'bevanda', 'piadina', 'bruschette e crostoni', 'pane e coperto', 'antipasto', 'primo', 'secondo', 'contorno', 'dolce'];

	if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();

		$nome = mysqli_real_escape_string($link, $_POST['nome']); //si fa sempre nelle variabili che metti in una query per sicurezza
  							
	  	$query = "DELETE FROM Portata WHERE nome_portata='$nome'";

	  	if(esegui_query($link, $query)) echo "Portata \"$nome\" cancellata correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} else if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['categoria']) && strlen($_POST['categoria'])>0 && in_array($_POST['categoria'], $categorie) &&
			   isset($_POST['costo']) && is_numeric($_POST['costo']) && isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome']);
		$categoria = mysqli_real_escape_string($link, $_POST['categoria']);
		$costo = mysqli_real_escape_string($link, $_POST['costo']);
		$id = mysqli_real_escape_string($link, $_POST['id']);

		$query = "INSERT INTO Portata (nome_portata, categoria, prezzo_finale, id) VALUES ('$nome', '$categoria', $costo, $id)";

		if(!esegui_query($link, $query)){

			if(($present = esegui_query($link, "SELECT * FROM Portata WHERE nome_portata = '${nome}'")) && mysqli_num_rows($present)>=1) echo '#error#Portata già presente';
			else echo '#error#Errore durante l\'operazione';
		} 
		else echo "Portata \"$nome\" inserita correttamente!";

		disconnetti_mysql($link, NULL);

	}else if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['nome_prec']) && strlen($_POST['nome_prec'])>0  && isset($_POST['categoria']) && strlen($_POST['categoria'])>0 && in_array($_POST['categoria'], $categorie) &&
			   isset($_POST['costo']) && is_numeric($_POST['costo']) && isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['operazione']) && $_POST['operazione']=='aggiorna'){

		$link = connetti_mysql();
		$nome_prec = mysqli_real_escape_string($link, $_POST['nome_prec']);
		$nome = mysqli_real_escape_string($link, $_POST['nome']);
		$categoria = mysqli_real_escape_string($link, $_POST['categoria']);
		$costo = mysqli_real_escape_string($link, $_POST['costo']);
        $id = mysqli_real_escape_string($link, $_POST['id']);

		$query = "UPDATE Portata SET categoria='$categoria', prezzo_finale=$costo, nome_portata='$nome', id=$id WHERE nome_portata='$nome_prec'";

		if(!esegui_query($link, $query)) echo '#error#Errore durante l\'operazione';
		else echo "Portata \"$nome\" aggiornata correttamente!";

		disconnetti_mysql($link, NULL);
	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}
	
?>