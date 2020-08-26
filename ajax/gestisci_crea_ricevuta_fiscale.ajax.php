<?php
	require_once '../include/core.inc.php';
	if( isset($_POST['quantita']) && !empty($_POST['quantita']) && isset($_POST['prezzo']) && !empty($_POST['prezzo'])){
		$date=ottieni_data_serata_attuale();
		$totale=$_POST['quantita']*$_POST['prezzo'];
		$totale=number_format($totale, 2, '.', ' ');
        if($date <=0){
            echo "#error#Errore durante l'acquisizione della data";
        }
		$query="INSERT INTO Ricevutefiscali (serata, tavolo,indice,totale) VALUES ('".$date."',0,0,".$totale.")";
		$link=connetti_mysql();

		mysqli_query($link,$query) or die("#error#".mysqli_error($link));
		disconnetti_mysql($link);
	}
?>