<?php
	require_once '../include/core.inc.php';

    if( isset($_POST['ordini']) && is_array($_POST['ordini']) && isset($_POST['menu']) && strlen($_POST['menu'])>0 
        && isset($_POST['numero_soci']) && is_numeric($_POST['numero_soci']) && $_POST['numero_soci']>=0
        && isset($_POST['sconto_manuale']) && is_numeric($_POST['sconto_manuale']) && $_POST['sconto_manuale']>=0 
        && isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])
        && isset($_POST['responsabile']) && strlen($_POST['responsabile'])>0 
        && isset($_POST['annotazioni'])
        && isset($_POST['operazione']) && $_POST['operazione']=='nuova_comanda'){

        $link = connetti_mysql();

        mysqli_autocommit($link, FALSE); /* disable autocommit */

        $ordini = $_POST['ordini'];
        $menu = mysqli_real_escape_string($link, $_POST['menu']);
        $numero_soci = mysqli_real_escape_string($link, $_POST['numero_soci']);
        $sconto_manuale = mysqli_real_escape_string($link, $_POST['sconto_manuale']);
        $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
        $resp = mysqli_real_escape_string($link, $_POST['responsabile']);
        $annotazioni = mysqli_real_escape_string($link, $_POST['annotazioni']);

        $date=ottieni_data_serata_attuale();
        if($date <=0){
            echo "#error#Errore durante l'acquisizione della data";
        }
        else{

            //controlla ultima comanda (ultimo indice)
            $query_check = "SELECT MAX(indice) AS indice_max FROM Comande WHERE serata = '$date' AND tavolo = $tavolo GROUP BY serata, tavolo";
            if(!($res = mysqli_query($link, $query_check))){
                echo '#error#Errore durante l\'operazione';
                mysqli_rollback($link);
                disconnetti_mysql($link);
                die();
            }
            //imposta indice
            if(mysqli_num_rows($res)>=1){
                $row = mysqli_fetch_assoc($res);
                $nuovo_indice = $row['indice_max']+1;
            }
            else{
                $nuovo_indice = 1;
            }
            mysqli_free_result($res);

            $query_num_comanda="SELECT MAX(num_comanda) AS max_num_com FROM Comande WHERE serata='$date'";
            if(!($res3 = mysqli_query($link, $query_num_comanda))){
                echo '#error#Errore durante l\'operazione';
                mysqli_rollback($link);
                disconnetti_mysql($link);
                die();
            }else{
                $row3 = mysqli_fetch_assoc($res3);
                if(!is_null($row3['max_num_com'])){
                    $num_comanda = $row3['max_num_com']+1;
                }else{
                    $num_comanda = 1;
                }
            }

            //inserisci comanda
            $query = "INSERT INTO Comande(serata, tavolo, indice, menu, numero_soci, responsabile, sconto_manuale, annotazioni, num_comanda) VALUES ('$date', $tavolo, $nuovo_indice, '$menu', $numero_soci, '$resp', $sconto_manuale, '$annotazioni', $num_comanda)";
            if(!($res = mysqli_query($link, $query))){
                echo '#error#Errore durante l\'operazione';
                mysqli_rollback($link);
                disconnetti_mysql($link);
                die();
            }

            mysqli_free_result($res);

            //inserisci ordini e rimuovi quantita
            foreach ($ordini as $key => $ordine) {
                $portata = mysqli_real_escape_string($link, $ordine[0]);
                $quantita = mysqli_real_escape_string($link, $ordine[1]);

                $query_ins = "INSERT INTO Ordini (quantita, serata, tavolo, indice, portata) VALUES ($quantita, '$date', $tavolo, $nuovo_indice, '$portata')";
                $query_quant = "UPDATE QuantitàPiattiSerata SET quantità = GREATEST(0, quantità - $quantita) WHERE serata='$date' AND piatto = '$portata'";
                if(!esegui_query($link, $query_ins) || !esegui_query($link, $query_quant)){
                    echo '#error#Errore durante l\'operazione';
                    mysqli_rollback($link);
                    disconnetti_mysql($link, NULL);
                    die();
                }
            }

            if (!mysqli_commit($link)) echo "#error#Errore durante l'operazione\n";
        }

        disconnetti_mysql($link, NULL);
    }
    else if( isset($_POST['ordini']) && is_array($_POST['ordini']) 
        && isset($_POST['numero_soci']) && is_numeric($_POST['numero_soci']) && $_POST['numero_soci']>=0 
        && isset($_POST['sconto_manuale']) && is_numeric($_POST['sconto_manuale']) && $_POST['sconto_manuale']>=0 
        && isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])
        && isset($_POST['annotazioni'])
        && isset($_POST['indice']) && is_numeric($_POST['indice']) && $_POST['indice']>=1
        && isset($_POST['operazione']) && $_POST['operazione']=='modifica_comanda'){

        $link = connetti_mysql();

        mysqli_autocommit($link, FALSE); /* disable autocommit */

        $ordini = $_POST['ordini'];
        $numero_soci = mysqli_real_escape_string($link, $_POST['numero_soci']);
        $sconto_manuale = mysqli_real_escape_string($link, $_POST['sconto_manuale']);
        $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
        $indice = mysqli_real_escape_string($link, $_POST['indice']);
        $annotazioni = mysqli_real_escape_string($link, $_POST['annotazioni']);

        $date=ottieni_data_serata_attuale();
        if($date <=0){
            echo "#error#Errore durante l'acquisizione della data";
        }
        else{

            //controlla se la comanda esiste
            $query_check = "SELECT * FROM Comande WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice";
            if(!($res = mysqli_query($link, $query_check))){
                echo '#error#Errore durante l\'operazione';
                mysqli_rollback($link);
                disconnetti_mysql($link);
                die();
            }
            //imposta indice
            if(mysqli_num_rows($res)<=0){
                echo "#error#Comanda $tavolo/$indice inesistente per la serata odierna";
                mysqli_rollback($link);
                disconnetti_mysql($link);
                die();
            }


            //aggiorna num soci
            $query = "UPDATE Comande SET numero_soci = $numero_soci, sconto_manuale = $sconto_manuale, annotazioni = '$annotazioni' WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice";
            if(!mysqli_query($link, $query)){
                echo '#error#Errore durante l\'operazione';
                mysqli_rollback($link);
                disconnetti_mysql($link);
                die();
            }

            //inserisci ordini
            foreach ($ordini as $key => $ordine) {
                $portata = mysqli_real_escape_string($link, $ordine[0]);
                $quantita = mysqli_real_escape_string($link, $ordine[1]);

                //controlla se l'ordine per quella portata già esiste
                $query_check_ord = "SELECT * FROM Ordini WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice AND portata = '$portata'";
                if(!($res = mysqli_query($link, $query_check_ord))){
                    echo '#error#Errore durante l\'operazione';
                    mysqli_rollback($link);
                    disconnetti_mysql($link);
                    die();
                }
                if(mysqli_num_rows($res)>0){
                    //gia esiste, riduci e 
                    if($quantita>=0){
                        //gia esiste, riduci e 
                        $query_update = "UPDATE Ordini SET quantita = GREATEST(0, quantita + $quantita) WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice AND portata = '$portata'";
                        $query_update_quant = "UPDATE QuantitàPiattiSerata SET quantità = GREATEST(0, quantità - $quantita) WHERE serata='$date' AND piatto = '$portata'";
                        $query_del_zero = "DELETE FROM Ordini WHERE quantita = 0 AND serata = '$date' AND tavolo = $tavolo AND indice = $indice";
                        if(!esegui_query($link, $query_update) || !esegui_query($link, $query_del_zero) || !esegui_query($link, $query_update_quant)){
                            echo '#error#Errore durante l\'operazione';
                            mysqli_rollback($link);
                            disconnetti_mysql($link, NULL);
                            die();
                        }
                    }
                    else{
                        $query_update = "UPDATE Ordini SET quantita = GREATEST(0, quantita + $quantita) WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice AND portata = '$portata'";
                        //$query_set_var = "SET @quantita_reale := (SELECT quantita FROM Ordini WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice AND portata = '$portata')";
                        $query_update_quant = "UPDATE QuantitàPiattiSerata SET quantità = GREATEST(0, quantità + LEAST(ABS($quantita), (
                                                                                                                                                SELECT quantita 
                                                                                                                                                FROM Ordini 
                                                                                                                                                WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice AND portata = '$portata'
                                                                                                                                            )
                                                                                                                            )
                                                                                                        ) 
                                                WHERE serata='$date' AND piatto = '$portata'";
                        $query_del_zero = "DELETE FROM Ordini WHERE quantita = 0 AND serata = '$date' AND tavolo = $tavolo AND indice = $indice";
                        if(!esegui_query($link, $query_update_quant) || !esegui_query($link, $query_update) || !esegui_query($link, $query_del_zero)){
                            echo '#error#Errore durante l\'operazione';
                            mysqli_rollback($link);
                            disconnetti_mysql($link, NULL);
                            die();
                        }
                    }
                }
                else{
                    //non esiste, inserisci
                    if($quantita>0){
                        $query_ins = "INSERT INTO Ordini (quantita, serata, tavolo, indice, portata) VALUES ($quantita, '$date', $tavolo, $indice, '$portata')";
                        $query_update_quant = "UPDATE QuantitàPiattiSerata SET quantità = GREATEST(0, quantità - $quantita) WHERE serata='$date' AND piatto = '$portata'";
                        if(!esegui_query($link, $query_ins) || !esegui_query($link, $query_update_quant)){
                            echo '#error#Errore durante l\'operazione';
                            mysqli_rollback($link);
                            disconnetti_mysql($link, NULL);
                            die();
                        }
                    }
                }
            }

            if(isset($res)) mysqli_free_result($res);

            if (!mysqli_commit($link)) echo "#error#Errore durante l'operazione\n";
        }

        disconnetti_mysql($link, NULL);
    }
    else if( isset($_POST['numero_soci']) && is_numeric($_POST['numero_soci']) && $_POST['numero_soci']>=0 
        && isset($_POST['sconto_manuale']) && is_numeric($_POST['sconto_manuale']) && $_POST['sconto_manuale']>=0 
        && isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])
        && isset($_POST['annotazioni'])
        && isset($_POST['indice']) && is_numeric($_POST['indice']) && $_POST['indice']>=1
        && isset($_POST['operazione']) && $_POST['operazione']=='modifica_comanda_soci'){

        $link = connetti_mysql();

        $numero_soci = mysqli_real_escape_string($link, $_POST['numero_soci']);
        $sconto_manuale = mysqli_real_escape_string($link, $_POST['sconto_manuale']);
        $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
        $indice = mysqli_real_escape_string($link, $_POST['indice']);
        $annotazioni = mysqli_real_escape_string($link, $_POST['annotazioni']);

        $date=ottieni_data_serata_attuale();
        if($date <=0){
            echo "#error#Errore durante l'acquisizione della data";
        }
        else{

            //controlla se la comanda esiste
            $query_check = "SELECT * FROM Comande WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice";
            if(!($res = mysqli_query($link, $query_check))){
                echo '#error#Errore durante l\'operazione';
                disconnetti_mysql($link);
                die();
            }
            //imposta indice
            if(mysqli_num_rows($res)<=0){
                echo "#error#Comanda $tavolo/$indice inesistente per la serata odierna";
                disconnetti_mysql($link);
                die();
            }
            mysqli_free_result($res);

            //aggiorna num soci
            $query = "UPDATE Comande SET numero_soci = $numero_soci, sconto_manuale = $sconto_manuale, annotazioni = '$annotazioni' WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice";
            if(!mysqli_query($link, $query)){
                echo '#error#Errore durante l\'operazione';
                disconnetti_mysql($link);
                die();
            }

        }

        disconnetti_mysql($link, NULL);
    }
    else if( isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])
        && isset($_POST['indice']) && is_numeric($_POST['indice']) && $_POST['indice']>=1
        && isset($_POST['pagata']) && is_numeric($_POST['pagata']) && ( $_POST['pagata'] == 0 || $_POST['pagata'] == 1 )
        && isset($_POST['operazione']) && $_POST['operazione']=='chiudi_comanda'){

        $date=ottieni_data_serata_attuale();
        if($date <=0){
            echo "#error#Errore durante l'acquisizione della data";
        }
        else{

            $link = connetti_mysql();

            $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
            $indice = mysqli_real_escape_string($link, $_POST['indice']);
            $pagata = mysqli_real_escape_string($link, $_POST['pagata']);



            //controlla ultima comanda (ultimo indice)
            $query = "UPDATE Comande SET attiva=0, pagata = $pagata WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice";
            if(!mysqli_query($link, $query)){
                echo '#error#Errore durante l\'operazione';
            }
            else{
                echo 'Comanda chiusa correttamente!';
            }

            disconnetti_mysql($link);

        }
    }
    else if( isset($_POST['tavolo']) && is_numeric($_POST['tavolo'])
        && isset($_POST['indice']) && is_numeric($_POST['indice']) && $_POST['indice']>=1
        && isset($_POST['operazione']) && $_POST['operazione']=='invia-conto'){

        $date=ottieni_data_serata_attuale();
        if($date <=0){
            echo "#error#Errore durante l'acquisizione della data";
        }
        else{

            $link = connetti_mysql();

            $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
            $indice = mysqli_real_escape_string($link, $_POST['indice']);

            //controlla ultima comanda (ultimo indice)
            $query = "UPDATE Comande SET conto_inviato=1 WHERE serata = '$date' AND tavolo = $tavolo AND indice = $indice";
            if(!mysqli_query($link, $query)){
                echo '#error#Errore durante l\'operazione';
            }
            else{
                echo 'Conto inviato correttamente!';
            }

            disconnetti_mysql($link);

        }
    }
    else{
        echo '#error#Operazione non riconosciuta o parametri mancanti';
    }
	
?>