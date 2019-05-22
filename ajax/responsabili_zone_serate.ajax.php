<?php
	require_once '../include/core.inc.php';
    

    if( isset($_POST['zone']) && is_array($_POST['zone']) && isset($_POST['responsabile']) && strlen($_POST['responsabile'])>0 
        && isset($_POST['operazione']) && $_POST['operazione']=='inserisci-init'){

        $link = connetti_mysql();
        $zone = $_POST['zone'];
        $tavoli=array();
        foreach ($zone as $key=>$z) {
            $res= esegui_query($link, "SELECT numero_tavolo FROM Tavoli WHERE zona='".$z."'");
            while($tav=mysqli_fetch_assoc($res)){
                $tavoli[]=$tav['numero_tavolo'];
            }
            mysqli_free_result($res);	
        }
            

        mysqli_autocommit($link, FALSE); /* disable autocommit */

        
        $responsabile = mysqli_real_escape_string($link, $_POST['responsabile']);

        $date=time();
        $date=date('Y-m-d', $date);

        foreach ($tavoli as $key => $value) {
            $value = mysqli_real_escape_string($link, $value);

            $query_del = "DELETE FROM ResponsabiliSerata WHERE serata = '$date' AND tavolo = $value";
            $query = "INSERT INTO ResponsabiliSerata(serata, tavolo, responsabile, numero_progressivo) 
                                            VALUES ('$date', $value, '$responsabile', 1)";

            if(!esegui_query($link, $query_del) || !esegui_query($link, $query)){
                echo '#error#Errore durante l\'operazione';
                mysqli_rollback($link);
                disconnetti_mysql($link, NULL);
                die();
            }
        }

        if (!mysqli_commit($link)) echo "#error#Errore durante l'operazione\n";

        disconnetti_mysql($link, NULL);
    }
    else if( isset($_POST['zone']) && is_array($_POST['zone']) 
        && isset($_POST['operazione']) && $_POST['operazione']=='disassocia-init'){

        $link = connetti_mysql();
        $zone = $_POST['zone'];
        $tavoli=array();
        foreach ($zone as $key=>$z) {
            $res= esegui_query($link, "SELECT numero_tavolo FROM Tavoli WHERE zona='".$z."'");
            while($tav=mysqli_fetch_assoc($res)){
                $tavoli[]=$tav['numero_tavolo'];
            }
            mysqli_free_result($res);	
        }

        mysqli_autocommit($link, FALSE); /* disable autocommit */


        $date=time();
        $date=date('Y-m-d', $date);

        foreach ($tavoli as $key => $value) {
            $value = mysqli_real_escape_string($link, $value);

            $query_del = "DELETE FROM ResponsabiliSerata WHERE serata = '$date' AND tavolo = $value";

            if(!esegui_query($link, $query_del)){
                echo '#error#Errore durante l\'operazione';
                mysqli_rollback($link);
                disconnetti_mysql($link, NULL);
                die();
            }
        }

        if (!mysqli_commit($link)) echo "#error#Errore durante l'operazione\n";

        disconnetti_mysql($link, NULL);
    }
    else if( isset($_POST['operazione']) && $_POST['operazione']=='check'){

        $link = connetti_mysql();

        $date=time();
        $date=date('Y-m-d', $date);

        $serata_attuale = ottieni_data_serata_attuale();
        if($serata_attuale == 0){
            echo "#error#Errore durante l'acquisizione della data";
        }
        else{

            if($serata_attuale == -1){ $serata_attuale = $date; }


            if(!( $res = esegui_query($link, "SELECT * FROM ResponsabiliSerata WHERE serata = '$serata_attuale'"))){
                echo '#error#Errore durante l\'operazione';
            }
            else{
                if(mysqli_num_rows($res)<=0){
                    echo '#error#Nessun responsabile impostato per la serata odierna';
                }
            }
        }

        disconnetti_mysql($link, NULL);
    }
    else{
        echo '#error#Operazione non riconosciuta o parametri mancanti';
    }
	
?>