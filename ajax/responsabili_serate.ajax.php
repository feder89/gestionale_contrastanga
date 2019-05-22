<?php
	require_once '../include/core.inc.php';

    if( isset($_POST['tavoli']) && is_array($_POST['tavoli']) && isset($_POST['responsabile']) && strlen($_POST['responsabile'])>0 
        && isset($_POST['operazione']) && $_POST['operazione']=='inserisci-init'){

        $link = connetti_mysql();

        mysqli_autocommit($link, FALSE); /* disable autocommit */

        $tavoli = $_POST['tavoli'];
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
    else if( isset($_POST['tavoli']) && is_array($_POST['tavoli']) 
        && isset($_POST['operazione']) && $_POST['operazione']=='disassocia-init'){

        $link = connetti_mysql();

        mysqli_autocommit($link, FALSE); /* disable autocommit */

        $tavoli = $_POST['tavoli'];

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
    else if( isset($_POST['tavoli']) && is_array($_POST['tavoli']) && isset($_POST['responsabile']) && strlen($_POST['responsabile'])>0 
        && isset($_POST['operazione']) && $_POST['operazione']=='inserisci_mod'){

        $link = connetti_mysql();

        mysqli_autocommit($link, FALSE); /* disable autocommit */

        $tavoli = $_POST['tavoli'];
        $responsabile = mysqli_real_escape_string($link, $_POST['responsabile']);

        $date=ottieni_data_serata_attuale();
        if($date <=0){
            echo "#error#Errore durante l'acquisizione della data";
        }
        else{

            foreach ($tavoli as $key => $value) {
                $value = mysqli_real_escape_string($link, $value);

                $query_check = "SELECT MAX(numero_progressivo) AS num_progr_max FROM ResponsabiliSerata WHERE serata = '$date' AND tavolo = $value GROUP BY serata, tavolo";
                $query = "INSERT INTO ResponsabiliSerata(serata, tavolo, responsabile, numero_progressivo) 
                                                VALUES ('$date', $value, '$responsabile', _replace_)";

                if(!($res = esegui_query($link, $query_check))){
                    echo '#error#Errore durante l\'operazione';
                    mysqli_rollback($link);
                    disconnetti_mysql($link, NULL);
                    die();
                } 
                else{
                    if(mysqli_num_rows($res)>0){
                        //there is already an entry
                        $row = mysqli_fetch_assoc($res);
                        //controlla che il resposabile non sia lo stesso
                        $query_check2 = "SELECT * FROM ResponsabiliSerata WHERE serata = '$date' AND tavolo = $value AND numero_progressivo =".$row['num_progr_max'];
                        if(!($res2 = esegui_query($link, $query_check2))){
    		                echo '#error#Errore durante l\'operazione';
    		                mysqli_rollback($link);
    		                disconnetti_mysql($link, NULL);
    		                die();
    		            } 
    		            else{
    		            	if(mysqli_num_rows($res2)>0 && ($row2 = mysqli_fetch_assoc($res2)) && $row2['responsabile'] != $responsabile){
    		            		$query = str_replace('_replace_', $row['num_progr_max']+1, $query);
    		            	}
    		            	else {
    		            		continue;
    		            	}
    		            }
                        
                    }
                    else{
                        //firs entry
                        $query = str_replace('_replace_', '1', $query);
                    }

                    if(!esegui_query($link, $query)){
                        echo '#error#Errore durante l\'operazione';
                        mysqli_rollback($link);
                        disconnetti_mysql($link, NULL);
                        die();
                    } 
                }
            }

            if (!mysqli_commit($link)) echo "#error#Errore durante l'operazione\n";
        }

        disconnetti_mysql($link, NULL);
    }
    else if( isset($_POST['tavoli']) && is_array($_POST['tavoli']) 
        && isset($_POST['operazione']) && $_POST['operazione']=='disassocia-mod'){

        $link = connetti_mysql();

        $data=array();

        mysqli_autocommit($link, FALSE); /* disable autocommit */

        $tavoli = $_POST['tavoli'];

        $date=ottieni_data_serata_attuale();
        if($date <=0){
            $data[]=array('error' => "#error#Errore durante l'acquisizione della data");
        }
        else{

            foreach ($tavoli as $key => $value) {
                $value = mysqli_real_escape_string($link, $value);

                $query_check = "SELECT MAX(numero_progressivo) AS num_progr_max FROM ResponsabiliSerata WHERE serata = '$date' AND tavolo = $value GROUP BY serata, tavolo";

                if(!($res= esegui_query($link, $query_check))){
                    $data[]=array('error' => '#error#Errore durante l\'operazione');
                    echo json_encode($data);
                    mysqli_rollback($link);
                    disconnetti_mysql($link, NULL);
                    die();
                }
                else{
                    if(mysqli_num_rows($res)>0){
                        //get the max
                        $row = mysqli_fetch_assoc($res);
                        if($row['num_progr_max'] > 1){
                            $query_del = "DELETE FROM ResponsabiliSerata WHERE serata = '$date' AND tavolo = $value AND numero_progressivo=".$row['num_progr_max'];

                            if(!esegui_query($link, $query_del)){
                                $data[]=array('error' => '#error#Errore durante l\'operazione');
                                echo json_encode($data);
                                mysqli_rollback($link);
                                disconnetti_mysql($link, NULL);
                                die();
                            } 

                            $query_get = "SELECT * FROM ResponsabiliSerata WHERE serata = '$date' AND tavolo = $value AND numero_progressivo=".($row['num_progr_max']-1);

                            if(!($res2 = esegui_query($link, $query_get))){
                                $data[]=array('error' => '#error#Errore durante l\'operazione');
                                echo json_encode($data);
                                mysqli_rollback($link);
                                disconnetti_mysql($link, NULL);
                                die();
                            }

                            $row2 = mysqli_fetch_assoc($res2);

                            $data[]=array('eliminato' => 1, 'nuovo_responsabile' => ( isset($row2['responsabile']) ? $row2['responsabile'] : ""), 'tavolo' => $value);
                        }
                        else if($row['num_progr_max'] == 1){
                            $data[]=array('tavolo' => $value, 'eliminato' => 0, 'msg' => '#error#Impossibile eliminare l\'unico responsabile del tavolo '.$value);
                            continue;
                        }
                    }
                    else{
                        //nothing to delete
                        continue;
                    }  
                }
            }

            if (!mysqli_commit($link)) $data[]=array('error' => '#error#Errore durante l\'operazione');
        }

        /* output in json format */
        echo json_encode($data);

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