<?php
	require_once '../include/core.inc.php';
	if( isset($_POST['tavolo']) && !empty($_POST['tavolo']) && isset($_POST['indice']) && !empty($_POST['indice'])){
		$date=ottieni_data_serata_attuale();
        if($date <=0){
            echo "#error#Errore durante l'acquisizione della data";
        }
        $link=connetti_mysql();
		$query_check="SELECT * FROM Ricevutefiscali WHERE serata='".$date."' AND tavolo=".$_POST['tavolo']." AND indice=".$_POST['indice'];
		$res_check=mysqli_query($link,$query_check) or die("#error#".mysqli_error($link));
		if(mysqli_num_rows($res_check) < 1){

		    $query="INSERT INTO Ricevutefiscali (serata, tavolo,indice) VALUES ('".$date."',".$_POST['tavolo'].",".$_POST['indice'].")";
			

			mysqli_query($link,$query) or die("#error#".mysqli_error($link));
			echo "Ricevuta Creata con successo";

		}else{
			echo "Ricevuta già presente non salvata";
		}

		
		disconnetti_mysql($link);
	}
?>