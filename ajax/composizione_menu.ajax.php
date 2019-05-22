<?php
    require_once '../include/core.inc.php';
    if(isset($_POST['piatti']) && is_array($_POST['piatti']) && isset($_POST['menu']) && strlen($_POST['menu'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='inserisci'){
        
        $link=connetti_mysql();
        $piatti = $_POST['piatti'];
        $menu=mysqli_real_escape_string($link, $_POST['menu']);
        mysqli_autocommit($link, FALSE);
        
        foreach ($piatti as $key=>$p) {
        	$query = "INSERT INTO Composizionemenu(menu, portata) VALUES ('$menu', '$p')";
            if(!esegui_query($link, $query)){
                echo '#error#Errore durante l\'operazione';
                mysqli_rollback($link);
                disconnetti_mysql($link, NULL);
                die();
            }
        }
        if (!mysqli_commit($link)) echo "#error#Errore durante l'operazione\n";

        disconnetti_mysql($link, NULL);
    }elseif(isset($_POST['piatti']) && is_array($_POST['piatti']) && isset($_POST['menu']) && strlen($_POST['menu'])>0 && isset($_POST['operazione']) && $_POST['operazione']=='rimuovi'){
        $link=connetti_mysql();
        $piatti = $_POST['piatti'];
        $menu=mysqli_real_escape_string($link, $_POST['menu']);
        mysqli_autocommit($link, FALSE);
        foreach ($piatti as $val=>$r) {
        	$query_r="DELETE FROM Composizionemenu WHERE portata='$r' AND menu ='$menu'";
            echo $query_r;
            if(!esegui_query($link, $query_r)){
                echo '#error#Errore durante l\'operazione';
                mysqli_rollback($link);
                disconnetti_mysql($link, NULL);
                die();
            }
        }
        if (!mysqli_commit($link)) echo "#error#Errore durante l'operazione\n";

        disconnetti_mysql($link, NULL);  
    }else{
        echo '#error#Operazione non riconosciuta o parametri mancanti';
    }

?>