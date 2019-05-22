<?php

require_once '../include/core.inc.php';
if(isset($_POST['tavolo']) && is_numeric($_POST['tavolo']) && isset($_POST['indice']) && is_numeric($_POST['indice']) && $_POST['indice']>=1){
    $date=ottieni_data_serata_attuale();
    if($date <=0){
        echo('#error#Errore durante l\'acquisizione della data');
    }
    else{

        $link = connetti_mysql();
    
        $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
        $indice = mysqli_real_escape_string($link, $_POST['indice']);
        
        $key_off="SET foreign_key_checks = 0;";
        $key_on="SET foreign_key_checks = 1;";
        $query_delete_comanda = "DELETE FROM Comande WHERE tavolo=".$tavolo." AND indice=".$indice." AND serata='".$date."'";
        $query_delete_ordini = "DELETE FROM Ordini WHERE tavolo=".$tavolo." AND indice=".$indice." AND serata='".$date."'";
        if(esegui_query($link, $query_delete_ordini) && esegui_query($link, $key_off)){
            if(esegui_query($link, $query_delete_comanda) && esegui_query($link, $key_on)){
                echo "Comanda eliminata correttamente";
            }else{
                echo "#error#Errore durante l\'eliminazione";
                //echo "<br/>".$query_delete_comanda;
            }
        }else{
            echo "#error#Errore durante l\'eliminazione";
            //echo "<br/>".$query_delete_comanda;
        }
        disconnetti_mysql($link);
    }
}

?>