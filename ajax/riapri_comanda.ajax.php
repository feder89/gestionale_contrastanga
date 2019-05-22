<?php
	require_once '../include/core.inc.php';
	$date=ottieni_data_serata_attuale();
    if($date <=0){
        echo "#error#Errore durante l'acquisizione della data";
        die();
    }

    if (isset($_POST['tavolo']) &&  strlen($_POST['tavolo'])>0 && is_numeric($_POST['tavolo']) && isset($_POST['indice']) &&  strlen($_POST['indice'])>0 && is_numeric($_POST['indice'])) {
    	$link = connetti_mysql();
    	$tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
    	$indice = mysqli_real_escape_string($link, $_POST['indice']);
    	$query = "UPDATE Comande SET attiva = 1 WHERE tavolo=$tavolo AND indice=$indice AND serata='$date'";

    	if(esegui_query($link, $query)) echo "Comanda $tavolo/$indice riaperta correttamente!";
	  	else echo "#error#Errore durante l'operazione";

	  	disconnetti_mysql($link, NULL); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo
	}
    
?>