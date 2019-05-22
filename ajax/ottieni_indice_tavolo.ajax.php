<?php
	require_once '../include/core.inc.php';
    if(isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])){
        $link = connetti_mysql();
        //mysqli_autocommit($link, FALSE);
        $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
        $date=ottieni_data_serata_attuale();
            if($date <=0){
                echo "#error#Errore durante l'acquisizione della data";
            }
            else{
                $query_check = "SELECT MAX(indice) AS indice_max FROM Comande WHERE serata = '$date' AND tavolo = $tavolo GROUP BY serata, tavolo";
                if(!($res = mysqli_query($link, $query_check))){
                    echo '#error#Errore durante l\'operazione';
                    //mysqli_rollback($link);
                    disconnetti_mysql($link);
                    die();
                }
                //imposta indice
                if(mysqli_num_rows($res)>=1){
                    $row = mysqli_fetch_assoc($res);
                    $nuovo_indice = $row['indice_max']+1;
                    //$str='#indice#'.$nuovo_indice;
                    echo $nuovo_indice;
                    //console.log($nuovo_indice);
                }
                else{
                    $nuovo_indice=1;
                    //$str='#indice#'.$nuovo_indice;
                    echo $nuovo_indice;
                    //console.log($nuovo_indice);
            }
            //mysqli_free_result($res);
            disconnetti_mysql($link);
        }
    }
?>