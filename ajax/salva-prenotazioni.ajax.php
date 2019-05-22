<?php 
	require_once '../include/core.inc.php';
	$link = connetti_mysql();
	$exixst=false;
	$tavoli_old=0;
	$coperti_old=0;
	$date=ottieni_data_serata_attuale();
	$query_old = "SELECT * FROM prenotazioni WHERE serata = '$date'";
	$query;
	$tavoli;
	$coperti;
    if(!($res =esegui_query($link, $query_old))){
        echo '#error#Errore durante l\'operazione';
        mysqli_rollback($link);
        disconnetti_mysql($link, NULL);
        die();
    }else{
        if(mysqli_num_rows($res)>0){
        	$exixst=true;
            //there is already an entry
            $row = mysqli_fetch_assoc($res);
            //controlla che il resposabile non sia lo stesso
            $tavoli_old=$row['tavoli'];
            $coperti_old=$row['coperti'];
        }
	}	
	if(isset($_POST['tavoli']) && $_POST['tavoli']>0 && isset($_POST['coperti']) && $_POST['coperti']>0){
		$tavoli=$_POST['tavoli'];
		$coperti=$_POST['coperti'];
		if($exixst){
			$tavoli+=$tavoli_old;
            $coperti+=$coperti_old;
            $query="UPDATE prenotazioni SET tavoli=$tavoli, coperti=$coperti WHERE serata = '$date'";
		}else{
			$query="INSERT INTO prenotazioni (serata, tavoli, coperti) VALUES ('$date',$tavoli, $coperti)";
		}
		
	}elseif (isset($_POST['tavoli']) && $_POST['tavoli']>0) {
		$tavoli=$_POST['tavoli'];
		if($exixst){
			$tavoli+=$tavoli_old;
            $query="UPDATE prenotazioni SET tavoli=$tavoli WHERE serata = '$date'";
		}else{
			$query="INSERT INTO prenotazioni (serata, tavoli, coperti) VALUES ('$date',$tavoli, 0)";
		}
	}elseif (isset($_POST['coperti']) && $_POST['coperti']>0) {
		$coperti=$_POST['coperti'];
		if($exixst){
            $coperti+=$coperti_old;
            $query="UPDATE prenotazioni SET coperti=$coperti WHERE serata = '$date'";
		}else{
			$query="INSERT INTO prenotazioni (serata, tavoli, coperti) VALUES ('$date',0, $coperti)";
		}
	}
	if(strlen($query)>0){

		if(!esegui_query($link, $query)){
            echo '#error#Errore durante l\'operazione '.$query;
            mysqli_rollback($link);
            disconnetti_mysql($link, NULL);
            die();
        }else{
        	echo "Prenotazioni salvate correttamente!";
        }

	}
	disconnetti_mysql($link, NULL);
?>