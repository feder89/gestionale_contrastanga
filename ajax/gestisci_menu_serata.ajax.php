<?php
	require_once '../include/core.inc.php';
	if( isset($_POST['nome_menu']) && strlen($_POST['nome_menu'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

	  	if( isset($_POST['data_serata']) && strlen($_POST['data_serata'])>0 && preg_match("/^([0-2][0-9]|3[0-1])\/(1[0-2]|0[1-9])\/\d\d\d\d$/", $_POST['data_serata'])){
			$link = connetti_mysql();

			$tmp_data = $_POST['data_serata'];

			$nome = mysqli_real_escape_string($link, $_POST['nome_menu']); //si fa sempre nelle variabili che metti in una query per sicurezza
	  		$_POST['data_serata'] = explode('/', $_POST['data_serata']);
		    $data_serata = $_POST['data_serata'][2].'-'.$_POST['data_serata'][1].'-'.$_POST['data_serata'][0];
		    $data_serata = mysqli_real_escape_string($link, $data_serata);
					
		  	$query = "DELETE FROM MenuSerata WHERE menu='${nome}' AND serata='${data_serata}'";

		  	if(esegui_query($link, $query)) echo "Menu \"$nome\" della serata $tmp_data cancellato correttamente!";
		  	else echo "#error#Errore durante l'operazione";

		  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo
		}
		else {
			echo '#error#La data non è impostata o non è nel formato corretto dd/mm/yyy';
		}

	} 
	else if( isset($_POST['nome_menu']) && strlen($_POST['nome_menu'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		if( isset($_POST['data_serata']) && strlen($_POST['data_serata'])>0 && preg_match("/^([0-2][0-9]|3[0-1])\/(1[0-2]|0[1-9])\/\d\d\d\d$/", $_POST['data_serata'])){

			$tmp_data = $_POST['data_serata'];

			$link = connetti_mysql();
			$nome = mysqli_real_escape_string($link, $_POST['nome_menu']);
			$_POST['data_serata'] = explode('/', $_POST['data_serata']);
		    $data_serata = $_POST['data_serata'][2].'-'.$_POST['data_serata'][1].'-'.$_POST['data_serata'][0];
		    $data_serata = mysqli_real_escape_string($link, $data_serata);

			$query = "INSERT INTO MenuSerata (menu, serata) VALUES ('$nome', '$data_serata')";

			if(!esegui_query($link, $query)){
				if(($present = esegui_query($link, "SELECT * FROM MenuSerata WHERE menu = '${nome}' AND serata = '${data_serata}' ")) && mysqli_num_rows($present)>=1) echo "#error#Menù \"${nome}\" già presente nella serata del $tmp_data";
				else echo '#error#Errore durante l\'operazione';
			} 
			else echo "Menu \"$nome\" inserito correttamente per la serata del ${tmp_data}!";

			disconnetti_mysql($link, NULL);
		}
		else {
			echo '#error#La data non è impostata o non è nel formato corretto dd/mm/yyy';
		}

	}
	else if( isset($_POST['nome_menu']) && strlen($_POST['nome_menu'])>0 &&  isset($_POST['nome_menu_prec']) && strlen($_POST['nome_menu_prec'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='aggiorna'){

		if( isset($_POST['data_serata']) && strlen($_POST['data_serata'])>0 && preg_match("/^([0-2][0-9]|3[0-1])\/(1[0-2]|0[1-9])\/\d\d\d\d$/", $_POST['data_serata'])){

			$tmp_data = $_POST['data_serata'];
			
			$link = connetti_mysql();
			$nome = mysqli_real_escape_string($link, $_POST['nome_menu']);
			$nome_prec = mysqli_real_escape_string($link, $_POST['nome_menu_prec']);
			$_POST['data_serata'] = explode('/', $_POST['data_serata']);
		    $data_serata = $_POST['data_serata'][2].'-'.$_POST['data_serata'][1].'-'.$_POST['data_serata'][0];
		    $data_serata = mysqli_real_escape_string($link, $data_serata);

		    $query = "SELECT * FROM MenuSerata WHERE menu='${nome}' AND serata='${data_serata}'";
		    if(!($res = esegui_query($link, $query))) echo '#error#Errore durante l\'operazione';
			else{
				if(mysqli_num_rows($res)>=1){
					echo "#error#Il Menù \"${nome}\" già esiste per la serata del $tmp_data";
					die();
				}
			}

			$query = "UPDATE MenuSerata SET menu='${nome}' WHERE menu='${nome_prec}' AND serata='${data_serata}'";
			
			if(!esegui_query($link, $query)) echo '#error#Errore durante l\'operazione';
			else echo "Menù \"$nome\" della serata del $tmp_data aggiornato correttamente!";

			disconnetti_mysql($link, NULL);
		}
		else {
			echo '#error#La data non è impostata o non è nel formato corretto dd/mm/yyy';
		}		
	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}

	
?>