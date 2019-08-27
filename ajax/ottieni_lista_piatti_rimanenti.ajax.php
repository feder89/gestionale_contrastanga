<?php
	require_once '../include/core.inc.php';
	
	$portate=array();

	if(isset($_POST['soglia']) && is_numeric($_POST['soglia']) && $_POST['soglia']>0){

    $date=ottieni_data_serata_attuale();
    if($date <=0){
        $portate[] = array('error' => "#error#Errore durante l'acquisizione della data");
    }
    else{
      $link = connetti_mysql();
      $soglia = mysqli_real_escape_string($link, $_POST['soglia']);

      $query = "SELECT * FROM QuantitaPiattiSerata WHERE serata = '$date' AND  quantita < $soglia ORDER BY quantita ASC";
      if(!($res = mysqli_query($link, $query))){
          $portate[] = array('error' => "#error#Errore durante l\'operazione");
      } 
      else{
        while ( ($row = mysqli_fetch_assoc($res))){
          $portate[]=array('nome_portata' => $row['piatto'], 'quantita' => $row['quantita']);
        }
      }

      disconnetti_mysql($link);
    }
  }
  else {
    $portate[] = array('error' => "#error#Errore, soglia non impostata correttamente");
  }

	echo json_encode($portate);
?>