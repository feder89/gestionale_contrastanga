<?php
	require_once '../include/core.inc.php';
	if( isset($_POST['nome_menu']) && strlen($_POST['nome_menu'])>0 && isset($_POST['nome_portata']) && strlen($_POST['nome_portata'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome_menu']);
	    $portata = mysqli_real_escape_string($link, $_POST['nome_portata']);
				
	  	$query = "DELETE FROM ComposizioneMenu WHERE menu='${nome}' AND portata='${portata}'";

	  	if(esegui_query($link, $query)) echo "Portata \"$portata\" del menù $nome cancellato correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} 
	else if( isset($_POST['nome_menu']) && strlen($_POST['nome_menu'])>0 && isset($_POST['nome_portata']) && strlen($_POST['nome_portata'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome_menu']);
	    $portata = mysqli_real_escape_string($link, $_POST['nome_portata']);

		$query = "INSERT INTO ComposizioneMenu (menu, portata) VALUES ('$nome', '$portata')";

		if(!esegui_query($link, $query)){
			if(($present = esegui_query($link, "SELECT * FROM ComposizioneMenu WHERE menu = '${nome}' AND portata = '${portata}' ")) && mysqli_num_rows($present)>=1) echo "#error#Portata \"${portata}\" già presente nel menu $nome";
			else echo '#error#Errore durante l\'operazione';
		} 
		else echo "Portata \"$portata\" inserita correttamente nel menu ${nome}!";

		disconnetti_mysql($link, NULL);

	}
	else if( isset($_POST['nome_menu']) && strlen($_POST['nome_menu'])>0 &&  isset($_POST['nome_portata_prec']) && strlen($_POST['nome_portata_prec'])>0 &&  isset($_POST['nome_portata']) && strlen($_POST['nome_portata'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='aggiorna'){
			
		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome_menu']);
		$portata_prec = mysqli_real_escape_string($link, $_POST['nome_portata_prec']);
		$portata = mysqli_real_escape_string($link, $_POST['nome_portata']);
		

	    $query = "SELECT * FROM ComposizioneMenu WHERE menu='${nome}' AND portata='${portata}'";
	    if(!($res = esegui_query($link, $query))) echo '#error#Errore durante l\'operazione';
		else{
			if(mysqli_num_rows($res)>=1){
				echo "#error#La Portata \"${portata}\" già esiste per il menù $nome";
				die();
			}
		}

		$query = "UPDATE ComposizioneMenu SET portata='${portata}' WHERE menu='${nome}' AND portata='${portata_prec}'";
		
		if(!esegui_query($link, $query)) echo '#error#Errore durante l\'operazione';
		else echo "Portata \"$portata\" del menù $nome aggiornato correttamente!";

		disconnetti_mysql($link, NULL);	
	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}

	
?>