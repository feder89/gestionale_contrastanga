<?php
	require_once '../include/core.inc.php';
	
	$materie=array();

	$link = connetti_mysql();
  $peso = false;
  if(isset($_POST['nome_portata']) && strlen($_POST['nome_portata'])>0){
    $peso=true;
    $nome_portata = mysqli_real_escape_string($link, $_POST['nome_portata']);
    $query = "SELECT mp.*, cp.peso
              FROM MateriePrime mp
              INNER JOIN ComposizionePortata cp ON cp.materia_prima = mp.nome_materia
              INNER JOIN Portata p ON p.nome_portata = cp.portata
              WHERE p.nome_portata ='".$nome_portata."'";
  }
  else if(isset($_POST['nome_materia']) && strlen($_POST['nome_materia'])>0){
    $nome_materia = mysqli_real_escape_string($link, $_POST['nome_materia']);
    $query = "SELECT * FROM MateriePrime WHERE nome_materia = '".$nome_materia."'";
  }
  else{
    $query = "SELECT * FROM MateriePrime WHERE 1";
  }
	
  $result = NULL;
				
	if( ($result = esegui_query($link, $query))){ 
    while($row = mysqli_fetch_assoc($result)){
      if($peso) $materie[]=array('nome_materia' => $row['nome_materia'], 'genere' => $row['genere'], 'peso' => $row['peso']);
      else $materie[]=array('nome_materia' => $row['nome_materia'], 'genere' => $row['genere']);
    }
  }
  else{
    /* error */
    $materie[]=array('error' => '#error#Errore durante l\'acquisizione dei dati');
  }

	disconnetti_mysql($link, $result); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	echo json_encode($materie);
?>