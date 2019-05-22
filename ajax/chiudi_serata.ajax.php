<?php
	require_once '../include/core.inc.php';


    $date=ottieni_data_serata_attuale();
    if($date <=0){
        echo "#error#Errore durante l'acquisizione della data";
    }
    else{

        $link = connetti_mysql();

        //controlla ultima comanda (ultimo indice)
        $query = "UPDATE Serata SET inizializzata=2 WHERE data = '$date'";
        if(!mysqli_query($link, $query)){
            echo '#error#Errore durante l\'operazione';
        }
        else{
            echo 'Serata chiusa correttamente!';
        }

        disconnetti_mysql($link);

    }
	
?>