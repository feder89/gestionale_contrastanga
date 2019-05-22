<?php
	require_once '../include/core.inc.php';
	
	$comanda=array();

  if(isset($_POST['tavolo']) && is_numeric($_POST['tavolo']) && isset($_POST['indice']) && is_numeric($_POST['indice']) && $_POST['indice']>=1){

    $date=ottieni_data_serata_attuale();
    if($date <=0){
        $comanda=array('error' => '#error#Errore durante l\'acquisizione della data');
    }
    else{

      $link = connetti_mysql();

      $tavolo = mysqli_real_escape_string($link, $_POST['tavolo']);
      $indice = mysqli_real_escape_string($link, $_POST['indice']);

      $query = "SELECT * 
                FROM Comande c 
                INNER JOIN Tavoli t ON c.tavolo = t.numero_tavolo 
                WHERE c.serata = '$date' AND c.tavolo = $tavolo AND c.indice = $indice";

      $query_ordini = "SELECT * FROM Ordini WHERE serata='$date' AND tavolo = $tavolo AND indice = $indice";

      if(isset($_POST['ordini']) && $_POST['ordini']==0){
        //caso senza ordini
        if( ($result = esegui_query($link, $query))){ 
          if($row = mysqli_fetch_assoc($result)){
            $comanda=array('serata' => $row['serata'], 'tavolo' => $row['tavolo'], 'indice' => $row['indice'], 'menu' => $row['menu'], 'numero_soci' => $row['numero_soci'],    'attiva' => $row['attiva'], 'zona' => $row['zona'], 'responsabile' => $row['responsabile'], 'pagata' => $row['pagata'], 'sconto_manuale'=> $row['sconto_manuale'], 'annotazioni'=> $row['annotazioni']);
          }
        }
        else{
          /* error */
          $comanda=array('error' => '#error#Errore durante l\'acquisizione dei dati');
        }
      }
      else {
    	  //caso con ordini
      	if( ($result = esegui_query($link, $query)) && ($res_ord = esegui_query($link, $query_ordini))){ 
          if($row = mysqli_fetch_assoc($result)){
            $ordini=array();
            while(($row_ord = mysqli_fetch_assoc($res_ord))){
              $ordini[]= array('portata' => $row_ord['portata'], 'quantita' => $row_ord['quantita']);
            }
            $comanda=array('serata' => $row['serata'], 'tavolo' => $row['tavolo'], 'indice' => $row['indice'], 'menu' => $row['menu'], 'numero_soci' => $row['numero_soci'],    'attiva' => $row['attiva'], 'zona' => $row['zona'], 'responsabile' => $row['responsabile'], 'pagata' => $row['pagata'], 'sconto_manuale'=> $row['sconto_manuale'], 'annotazioni'=> $row['annotazioni'], 'ordini' => $ordini);
          }
        }
        else{
          /* error */
          $comanda=array('error' => '#error#Errore durante l\'acquisizione dei dati');
        }
      }

      disconnetti_mysql($link);
    }
  }
  else{
    /* error */
    $comanda=array('error' => '#error#Parametri non riconosciuti');
  }

	echo json_encode($comanda);
?>