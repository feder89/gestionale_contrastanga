<?php
	require_once '../include/core.inc.php';

	$date=ottieni_data_serata_attuale();
    if($date <=0){
        echo '#error#Errore durante l\'acquisizione della data';
    }
    else{
		$link = connetti_mysql();
		
		$nome_portata_posti = 'pane e coperto';

        $query_posti = "SELECT DISTINCT t.*
                              FROM Tavoli t
                              INNER JOIN ResponsabiliSerata rs ON rs.tavolo = t.numero_tavolo
                              AND rs.serata = '$date'";

        //vedi solo le comande attive!!
        $query_posti_occupati = "SELECT c.*, o.quantita AS numero_persone
                                 FROM Comande c
                                 INNER JOIN Ordini o ON o.serata = c.serata AND o.tavolo = c.tavolo AND o.indice = c.indice
                                 WHERE c.serata = '$date' AND c.attiva=1 AND LOWER(o.portata) = '$nome_portata_posti'";

        if(($res = mysqli_query($link, $query_posti)) && ($res2 = mysqli_query($link, $query_posti_occupati))){
          $posti_totali = 0;
          $posti_occupati = 0;
          while(($row = mysqli_fetch_assoc($res))){
            $posti_totali+= $row['posti'];
          }
          while(($row = mysqli_fetch_assoc($res2))){
            $posti_occupati+= $row['numero_persone'];
          }
          echo $posti_occupati.'-'.$posti_totali;
        } 
        else echo '#error#Errore durante l\'operazione';

	  	disconnetti_mysql($link);
	}

	
	
?>