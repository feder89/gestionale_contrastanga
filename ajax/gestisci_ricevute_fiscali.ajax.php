<?php
	require_once '../include/core.inc.php';
	if( isset($_POST['tavolo']) && !empty($_POST['tavolo']) && isset($_POST['indice']) && !empty($_POST['indice'])){
		$date=ottieni_data_serata_attuale();
        if($date <=0){
            echo "#error#Errore durante l'acquisizione della data";
        }
		$query="INSERT INTO ricevutefiscali (serata, tavolo,indice) VALUES ('".$date."',".$_POST['tavolo'].",".$_POST['indice'].")";
		$link=connetti_mysql();

		mysqli_query($link,$query) or die("#error#".mysqli_error($link));
		disconnetti_mysql($link);
	}
?>