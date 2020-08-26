<?php
	require_once '../include/core.inc.php';
	if(isset($_POST['serata']) && strlen($_POST['serata'])>0 && isset($_POST['portata']) && strlen($_POST['portata'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();

		$portata = mysqli_real_escape_string($link, $_POST['portata']);
		$serata = mysqli_real_escape_string($link, $_POST['serata']);
  							
	  	$query = "DELETE FROM QuantitaPiattiSerata WHERE piatto = '$portata' AND serata = '$serata'";

	  	if(esegui_query($link, $query)) echo "Portata \"$portata\" cancellata correttamente dalla serata odierna!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} else if( isset($_POST['serata']) && strlen($_POST['serata'])>0 && isset($_POST['portata']) && strlen($_POST['portata'])>0 && isset($_POST['quantita']) && is_numeric($_POST['quantita']) && $_POST['quantita']>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$portata = mysqli_real_escape_string($link, $_POST['portata']);
		$quantita = mysqli_real_escape_string($link, $_POST['quantita']);
		$serata = mysqli_real_escape_string($link, $_POST['serata']);

		$query = "INSERT INTO QuantitaPiattiSerata (serata, piatto, quantita) VALUES ('$serata', '$portata', $quantita)";

		if(!esegui_query($link, $query)){

			if(($present = esegui_query($link, "SELECT * FROM QuantitaPiattiSerata WHERE piatto = '$portata' AND serata = '$serata'")) && mysqli_num_rows($present)>=1) echo "#error#Piatto '$portata' già impostato per la serata odierna";
			else echo '#error#Errore durante l\'operazione'.mysqli_error($link);
		} 
		else echo "Piatto '$portata' impostato correttamente!";

		disconnetti_mysql($link, NULL);

	}else if( isset($_POST['serata']) && strlen($_POST['serata'])>0 && isset($_POST['portata']) && strlen($_POST['portata'])>0 && isset($_POST['quantita']) && is_numeric($_POST['quantita']) && $_POST['quantita']>0 && isset($_POST['operazione']) && $_POST['operazione']=='aggiorna'){

		$link = connetti_mysql();
		$portata = mysqli_real_escape_string($link, $_POST['portata']);
		$quantita = mysqli_real_escape_string($link, $_POST['quantita']);
		$serata = mysqli_real_escape_string($link, $_POST['serata']);

		$query = "UPDATE QuantitaPiattiSerata SET quantita='$quantita' WHERE piatto = '$portata' AND serata = '$serata'";

		if(!esegui_query($link, $query)) echo '#error#Errore durante l\'operazione'.mysqli_error($link);
		else echo "Piatto '$portata' aggiornato correttamente!";

		disconnetti_mysql($link, NULL);
	}
	else if( isset($_POST['data_oggi']) && strlen($_POST['data_oggi'])>0 && isset($_POST['operazione']) && isset($_POST['default']) && is_numeric($_POST['default']) && $_POST['default']>=0 && $_POST['operazione']=='imposta-default-e-controlla'){

		$link = connetti_mysql();
		$serata = mysqli_real_escape_string($link, $_POST['data_oggi']);
		$default = mysqli_real_escape_string($link, $_POST['default']);

		//ottieni lista delle portate che non hanno valore
		$query = "SELECT DISTINCT p.*
				  FROM Portata p
				  INNER JOIN ComposizioneMenu cm ON cm.portata = p.nome_portata
              	  INNER JOIN Menu m ON m.nome_menu = cm.menu
              	  INNER JOIN MenuSerata ms ON ms.menu = m.nome_menu
				  WHERE ms.serata = '".$serata."' AND p.nome_portata NOT IN(
						SELECT p2.nome_portata
				  		FROM Portata p2
				  		INNER JOIN QuantitaPiattiSerata q ON q.piatto = p2.nome_portata 
				  		WHERE q.serata = '".$serata."')";

		if(!($res=esegui_query($link, $query))) echo '#error#Errore durante l\'operazione'.mysqli_error($link);
		else {
			if(mysqli_num_rows($res) > 0){ 
				$insert_q = "INSERT INTO QuantitaPiattiSerata(`serata`, `piatto`, `quantita`) VALUES ";
				$c = 0;
				while($row = mysqli_fetch_assoc($res)){
					if($c==0) $insert_q .= "('$serata','".$row['nome_portata']."', $default)";
						else  $insert_q .= ",('$serata','".$row['nome_portata']."', $default)";
					$c++;
				}
				mysqli_free_result($res);

				if(!esegui_query($link, $insert_q)){ 
					echo '#error#Errore durante l\'operazione'.mysqli_error($link).mysqli_error($link);
					disconnetti_mysql($link, NULL);
					die();
				}
			}

			//check
			if(!(esegui_query($link, "UPDATE Serata SET inizializzata=1 WHERE data='$serata'"))) echo '#error#Errore durante l\'operazione di inizializzazione. Ricaricare la pagina e riprovare';
			else echo 'ok';
			
		}

		disconnetti_mysql($link, NULL);
	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}
	
?>