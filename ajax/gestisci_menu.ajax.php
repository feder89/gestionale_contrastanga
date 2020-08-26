<?php
	require_once '../include/core.inc.php';
	if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='cancella'){

		$link = connetti_mysql();

		$nome = mysqli_real_escape_string($link, $_POST['nome']); //si fa sempre nelle variabili che metti in una query per sicurezza
  							
	  	$query = "DELETE FROM Menu WHERE nome_menu='${nome}'";

	  	if(esegui_query($link, $query)) echo "Menu \"$nome\" cancellato correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	} else if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome']);
		$max="SELECT max(id) AS max_id FROM menu";
		$res_max=mysqli_query($link, $max) or die("#error#".mysqli_error($link));
		$row_max = mysqli_fetch_array($res_max);
		$id_max= $row_max["max_id"] + 1;

		if(isset($_POST['fix']) && $_POST['fix']==1 && is_numeric($_POST['price'])){
			$_POST['fix'] = mysqli_real_escape_string($link, $_POST['fix']);
			$_POST['price'] = mysqli_real_escape_string($link, $_POST['price']);
			$query = "INSERT INTO Menu (id, nome_menu, fisso, prezzo_fisso) VALUES (".$id_max.",'$nome', ${_POST['fix']}, ${_POST['price']})";
		} else {
			$query = "INSERT INTO Menu (id, nome_menu) VALUES (".$id_max.",'$nome')";
		}

		if(!esegui_query($link, $query)){

			if(($present = esegui_query($link, "SELECT * FROM Menu WHERE nome_menu = '${nome}'")) && mysqli_num_rows($present)>=1) echo '#error#Menù già presente';
			else echo '#error#Errore durante l\'operazione';
		} 
		else echo "Menu \"$nome\" inserito correttamente!";

		disconnetti_mysql($link, NULL);

	}else if( isset($_POST['nome']) && strlen($_POST['nome'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='aggiorna'){

		$link = connetti_mysql();
		$nome = mysqli_real_escape_string($link, $_POST['nome']);

		if(isset($_POST['fix']) && $_POST['fix']==1 && isset($_POST['price']) && is_numeric($_POST['price'])){
			$_POST['fix'] = mysqli_real_escape_string($link, $_POST['fix']);
			$_POST['price'] = mysqli_real_escape_string($link, $_POST['price']);
			$query = "UPDATE Menu SET fisso=".$_POST['fix'].", prezzo_fisso=".$_POST['price']." WHERE nome_menu='${nome}'";
		} else if(isset($_POST['fix']) && $_POST['fix']==0 ){
			if(isset($_POST['price']) && is_numeric($_POST['price'])){
				echo "#error#Non è possibile inserire un prezzo fisso se il menù non è fisso";
				disconnetti_mysql($link, NULL);
				die();
			}
			$_POST['fix'] = mysqli_real_escape_string($link, $_POST['fix']);
			$query = "UPDATE Menu SET fisso=".$_POST['fix'].", prezzo_fisso=NULL WHERE nome_menu='${nome}'";
		}else {
			echo "#error#Errore durante l\'operazione";
			disconnetti_mysql($link, NULL);
			die();
		}

		if(!esegui_query($link, $query)) echo '#error#Errore durante l\'operazione';
		else echo "Menu \"$nome\" aggiornato correttamente!";

		disconnetti_mysql($link, NULL);
	}
	else{
		echo '#error#Operazione non riconosciuta o parametri mancanti';
	}
	
?>