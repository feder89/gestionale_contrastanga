<?php
	require_once '../include/core.inc.php';
	if( isset($_POST['nome_materia']) && strlen($_POST['nome_materia'])>0 && isset($_POST['nome_portata']) && strlen($_POST['nome_portata'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();
		$materia = mysqli_real_escape_string($link, $_POST['nome_materia']);
	    $portata = mysqli_real_escape_string($link, $_POST['nome_portata']);
				
	  	$query = "DELETE FROM ComposizionePortata WHERE materia_prima='$materia' AND portata='$portata'";

	  	if(esegui_query($link, $query)) echo "Materia prima \"$materia\" della portata $portata cancellata correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} 
	else if( isset($_POST['nome_portata']) && strlen($_POST['nome_portata'])>0 && isset($_POST['nome_materia']) && strlen($_POST['nome_materia'])>0 && isset($_POST['operazione']) && isset($_POST['peso']) && is_numeric($_POST['peso']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$materia = mysqli_real_escape_string($link, $_POST['nome_materia']);
	    $portata = mysqli_real_escape_string($link, $_POST['nome_portata']);
	    $peso = mysqli_real_escape_string($link, $_POST['peso']);

		$query = "INSERT INTO ComposizionePortata (portata, materia_prima, peso) VALUES ('$portata', '$materia', $peso)";

		if(!esegui_query($link, $query)){
			if(($present = esegui_query($link, "SELECT * FROM ComposizionePortata WHERE materia_prima = '$materia' AND portata = '$portata' ")) && mysqli_num_rows($present)>=1) echo "#error#Materia prima \"${materia}\" già presente nella portata $portata";
			else echo '#error#Errore durante l\'operazione';
		} 
		else echo "Materia prima \"$materia\" inserita correttamente alla portata ${portata}!";

		disconnetti_mysql($link, NULL);

	}
	else if( isset($_POST['nome_materia']) && strlen($_POST['nome_materia'])>0 &&  isset($_POST['nome_materia_prec']) && strlen($_POST['nome_materia_prec'])>0 &&  isset($_POST['nome_portata']) && strlen($_POST['nome_portata'])>0 && isset($_POST['peso']) && is_numeric($_POST['peso']) && isset($_POST['operazione']) && $_POST['operazione']=='aggiorna'){
			
		$link = connetti_mysql();
		$nome_materia = mysqli_real_escape_string($link, $_POST['nome_materia']);
		$nome_materia_prec = mysqli_real_escape_string($link, $_POST['nome_materia_prec']);
		$portata = mysqli_real_escape_string($link, $_POST['nome_portata']);
		$peso = mysqli_real_escape_string($link, $_POST['peso']);
		

	    $query = "SELECT * FROM ComposizionePortata WHERE materia_prima='$nome_materia' AND portata='$portata'";
	    if($nome_materia != $nome_materia_prec){
		    if(!($res = esegui_query($link, $query))) echo '#error#Errore durante l\'operazione';
			else{
				if(mysqli_num_rows($res)>=1){
					echo "#error#La materia prima \"${nome_materia}\" già esiste nella portata $portata";
					die();
				}
			}
		}
		$query = "UPDATE ComposizionePortata SET materia_prima='${nome_materia}', peso=$peso WHERE portata='${portata}' AND materia_prima='${nome_materia_prec}'";
		
		if(!esegui_query($link, $query)) echo '#error#Errore durante l\'operazione';
		else echo "Materia Prima \"$nome_materia\" della portata $portata aggiornata correttamente!";

		disconnetti_mysql($link, NULL);	
	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}

?>

