<?php
	require_once '../include/core.inc.php';
	
	$portate=array();

	$link = connetti_mysql();

  if(isset($_POST['data_serata']) && strlen($_POST['data_serata'])>0){

    $data_serata = mysqli_real_escape_string($link, $_POST['data_serata']);
    $query = "SELECT p.* 
              FROM Portata p
              INNER JOIN ComposizioneMenu cm ON cm.portata = p.nome_portata
              INNER JOIN Menu m ON m.nome_menu = cm.menu
              INNER JOIN MenuSerata ms ON ms.menu = m.nome_menu
              WHERE ms.serata = '".$data_serata."' 
              GROUP BY p.nome_portata
			  ORDER BY p.id"
              ;
  }
  else if(isset($_POST['data_serata_distinct']) && strlen($_POST['data_serata_distinct'])>0){

    $data_serata = mysqli_real_escape_string($link, $_POST['data_serata_distinct']);
    $query = "SELECT DISTINCT p.* 
              FROM Portata p
              INNER JOIN ComposizioneMenu cm ON cm.portata = p.nome_portata
              INNER JOIN Menu m ON m.nome_menu = cm.menu
              INNER JOIN MenuSerata ms ON ms.menu = m.nome_menu
              WHERE ms.serata = '".$data_serata."'
              GROUP BY p.nome_portata
			  ORDER BY p.id"
              ;
  }
  else if(isset($_POST['nome_menu']) && strlen($_POST['nome_menu'])>0){

    $nome_menu = mysqli_real_escape_string($link, $_POST['nome_menu']);
    $query = "SELECT p.* 
              FROM Portata p
              INNER JOIN ComposizioneMenu cm ON cm.portata = p.nome_portata
              INNER JOIN Menu m ON m.nome_menu = cm.menu
              WHERE m.nome_menu ='".$nome_menu."'
			  ORDER BY p.id";
  }
  else if(isset($_POST['nome_menu_order']) && strlen($_POST['nome_menu_order'])>0){

    $quantita_rimanente = true;

    $nome_menu = mysqli_real_escape_string($link, $_POST['nome_menu_order']);
    $query = "SELECT p.* 
              FROM Portata p
              INNER JOIN ComposizioneMenu cm ON cm.portata = p.nome_portata
              INNER JOIN Menu m ON m.nome_menu = cm.menu
              WHERE m.nome_menu ='".$nome_menu."'
              ORDER BY p.id"; 
  }
  else if(isset($_POST['nome_portata']) && strlen($_POST['nome_portata'])>0){
    $nome_portata = mysqli_real_escape_string($link, $_POST['nome_portata']);
    $query = "SELECT * FROM Portata WHERE nome_portata = '".$nome_portata."'";
  }
  else{
    $query = "SELECT * FROM Portata WHERE 1";
  }
	
  $result = NULL;
				
	if( ($result = esegui_query($link, $query))){ 
        while($row = mysqli_fetch_assoc($result)){

            if(isset($quantita_rimanente) && $quantita_rimanente){
                $date=ottieni_data_serata_attuale();
                if($date <=0){
                    $portate[]=array('error' => '#error#Errore durante l\'acquisizione della data');
                }
                else{
                    $query_port = "SELECT * FROM QuantitaPiattiSerata WHERE serata='$date' AND piatto = '".$row['nome_portata']."'";
                    if( ($res2 = esegui_query($link, $query_port))){ 
                        $row2 = mysqli_fetch_assoc($res2);
                        $portate[]=array('nome_portata' => $row['nome_portata'], 'categoria' => $row['categoria'], 'prezzo_finale' => $row['prezzo_finale'], 'quantita_rimanente' => $row2['quantita']);
                    }
                    else{
                        /* error */
                        $portate[]=array('error' => '#error#Errore durante l\'acquisizione dei dati');
                    }
                }
            }
            else{
                $portate[]=array('nome_portata' => $row['nome_portata'], 'categoria' => $row['categoria'], 'prezzo_finale' => $row['prezzo_finale']);
            }
        }
    }
    else{
        /* error */
        $portate[]=array('error' => '#error#Errore durante l\'acquisizione dei dati');
    }

	disconnetti_mysql($link, $result); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

	echo json_encode($portate);
?>