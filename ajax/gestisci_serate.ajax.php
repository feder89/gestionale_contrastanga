<?php
	require_once '../include/core.inc.php';
	if( isset($_POST['data']) && strlen($_POST['data'])>0 && isset($_POST['nome']) && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();

		$data = mysqli_real_escape_string($link, $_POST['data']); //si fa sempre nelle variabili che metti in una query per sicurezza
  		$data = explode('/', $data);
		$data = $data[2].'-'.$data[1].'-'.$data[0];

	  	$query = "DELETE FROM Serata WHERE data='${data}'";

	  	if(esegui_query($link, $query)) echo "Serata \"${_POST['nome']}\" in data ${_POST['data']} cancellata correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} else if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['data']) && strlen($_POST['data'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome']);
		$data = mysqli_real_escape_string($link, $_POST['data']);

		$data = explode('/', $data);
		$data = $data[2].'-'.$data[1].'-'.$data[0];

		$query = "INSERT INTO Serata (descrizione, data) VALUES ('${nome}', '${data}')";
		$query_programmazioni="TRUNCATE TABLE programmazioneordini";
		if(!esegui_query($link, $query)){

			if(($present = esegui_query($link, "SELECT * FROM Serata WHERE data = '${data}'")) && mysqli_num_rows($present)>=1) echo "#error#C'è già una serata il ${_POST['data']}";
			else echo '#error#Errore durante l\'operazione';
		}elseif(!mysqli_query($link, $query_programmazioni)){
            echo '#error#Errore durante l\'operazione';
        } 
		else echo "Serata \"$nome\" inserita correttamente!";

		disconnetti_mysql($link, NULL);

	}else if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['data']) && strlen($_POST['data'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='aggiorna'){

		$link = connetti_mysql();

		$nome = mysqli_real_escape_string($link, $_POST['nome']);
		$data = mysqli_real_escape_string($link, $_POST['data']);

		$data = explode('/', $data);
		$data = $data[2].'-'.$data[1].'-'.$data[0];

		$query = "UPDATE Serata SET descrizione='${nome}' WHERE data='${data}'";

		if(!esegui_query($link, $query)) echo '#error#Errore durante l\'operazione';
		else echo "Serata \"$nome\" in data ${_POST['data']} aggiornata correttamente!";

		disconnetti_mysql($link, NULL);
	}
	else if( isset($_POST['data_oggi']) && strlen($_POST['data_oggi'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='controlla-serata'){

		$link = connetti_mysql();

		$data = mysqli_real_escape_string($link, $_POST['data_oggi']);

		$query = "SELECT * FROM Serata WHERE data='${data}'";

		if(! ($res = esegui_query($link, $query))) echo '#error#Errore durante l\'operazione';
		else{
			if(mysqli_num_rows($res)>0) echo 'presente';
			else echo '#error#Non è stata creata alcuna serata per la data odierna';
		}

		disconnetti_mysql($link, NULL);
	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}
	
?>