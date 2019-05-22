<?php
	require_once '../include/core.inc.php';
	
	$comanda=array();


  $date=ottieni_data_serata_attuale();
  if($date <=0){
      $comanda=array('error' => '#error#Errore durante l\'acquisizione della data');
  }
  else{

    $link = connetti_mysql();

    $order_by = " ORDER BY c.attiva DESC, c.tavolo ASC, c.indice ASC";
    if(isset($_POST['order_col'])){
      switch ($_POST['order_col']) {
        case 2:
          $order_by = " ORDER BY c.attiva DESC, c.tavolo ASC, c.indice ASC";
          break;
        case 3:
          $order_by = " ORDER BY -c.num_comanda DESC";
          break;
      }
    }

    if(isset($_POST['solo_attive']) && $_POST['solo_attive']=='true'){
      $query = "SELECT * 
                FROM Comande c 
                WHERE c.serata='$date' AND c.attiva=1
                ".$order_by;
    }
    else if(isset($_POST['tab']) && is_numeric($_POST['tab']) && $_POST['tab']>=1 && $_POST['tab']<=2){
      $query = "SELECT * 
                FROM Comande c 
                WHERE c.serata='$date' AND c.attiva=".( $_POST['tab'] == 1 ? '1' : '0')
                .$order_by;
    }
    else{ 
      $query = "SELECT * 
                FROM Comande c 
                WHERE c.serata='$date'
                ".$order_by;
    }

    if (($result=esegui_query($link,$query))) {
        while ($row1=mysqli_fetch_assoc($result)) {
            $comanda[] = array('attiva' => $row1['attiva'], 'tavolo' => $row1['tavolo'], 'indice' => $row1['indice'], 'responsabile' => $row1['responsabile'], 'conto_inviato' => $row1['conto_inviato'], 'pagata' => $row1['pagata'], 'num_comanda' => $row1['num_comanda']);
        }
    }
    else{
      /* error */
      $comanda=array('error' => '#error#Errore durante l\'acquisizione dei dati');
    }

    disconnetti_mysql($link);
  }


	echo json_encode($comanda);
?>