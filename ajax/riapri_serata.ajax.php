<?php
	require_once '../include/core.inc.php';

    if(isset($_POST['data']) && strlen($_POST['data'])>0){

        $link = connetti_mysql();
        $date = mysqli_real_escape_string($link, $_POST['data']);

        //controlla ultima comanda (ultimo indice)
        $query1 = "UPDATE Serata SET inizializzata=2 WHERE inizializzata=1";
        $query2 = "UPDATE Serata SET inizializzata=1 WHERE data = '$date'";
        if( mysqli_query($link, $query1) && mysqli_query($link, $query2)){
            echo 'Serata riaperta correttamente!';
            
        }
        else{
            echo '#error#Errore durante l\'operazione';
        }

        disconnetti_mysql($link);

    }
	
?>