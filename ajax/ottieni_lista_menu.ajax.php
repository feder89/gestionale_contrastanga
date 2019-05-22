<?php
	require_once '../include/core.inc.php';
	
	$menu=array();

	$link = connetti_mysql();

  $do_query = true;

  if(isset($_POST['data_serata']) && strlen($_POST['data_serata'])>0){
    if ( preg_match("/^([0-2][0-9]|3[0-1])\/(1[0-2]|0[1-9])\/\d\d\d\d$/", $_POST['data_serata'])) {
      $_POST['data_serata'] = explode('/', $_POST['data_serata']);
      $data_serata = $_POST['data_serata'][2].'-'.$_POST['data_serata'][1].'-'.$_POST['data_serata'][0];
      $data_serata = mysqli_real_escape_string($link, $data_serata);
      $query = "SELECT m.* 
                FROM Menu m
                INNER JOIN MenuSerata ms ON ms.menu = m.nome_menu
                INNER JOIN Serata s ON ms.serata = s.data
                WHERE s.data ='".$data_serata."'";
    } 
    else {
        $do_query = false;
        /* error */
        $menu[]=array('error' => '#error#La data non è nel formato corretto dd/mm/yyy');
    }
  }
  else if(isset($_POST['nome_menu']) && strlen($_POST['nome_menu'])>0){
    $nome_menu = mysqli_real_escape_string($link, $_POST['nome_menu']);
    $query = "SELECT * FROM Menu WHERE nome_menu = '".$nome_menu."'";
  }
  else{
    $query = "SELECT * FROM Menu WHERE 1";
  }
	
  $result = NULL;
  if($do_query){					
  	if( ($result = esegui_query($link, $query))){ 
      while($row = mysqli_fetch_assoc($result)){
        $menu[]=array('nome_menu' => $row['nome_menu'], 'fisso' => $row['fisso'], 'prezzo_fisso' => $row['prezzo_fisso']);
      }
    }
    else{
      /* error */
      $menu[]=array('error' => '#error#Errore durante l\'acquisizione dei dati');
    }
  }
	disconnetti_mysql($link, $result); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo

  echo json_encode($menu);
	
?>