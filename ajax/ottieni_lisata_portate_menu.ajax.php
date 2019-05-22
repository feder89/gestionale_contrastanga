<?php
    require_once '../include/core.inc.php';
	unset($portate);
	$portate=array();

	$link = connetti_mysql();
    if(isset($_POST['nome_menu']) && strlen($_POST['nome_menu'])>0){

        $nome_menu = mysqli_real_escape_string($link, $_POST['nome_menu']);
        $query_fisso="SELECT * FROM Menu WHERE nome_menu='".$nome_menu."'";
        $res_fix=esegui_query($link, $query_fisso);
        $fisso=false;
        if(mysqli_num_rows($res_fix)>0){
            $row_fix=mysqli_fetch_assoc($res_fix);
            if($row_fix['fisso'] == 1){
                $fisso=true;
            }
        }
        //mysqli_free_result($res_fix);
        if($fisso){
            $query = "SELECT nome_portata 
                      FROM Portata p
                      WHERE p.nome_portata LIKE '%FISSO' OR p.categoria='pane e coperto'
        			  ORDER BY p.id";
            //$portate[]=array('nome_portata' => 'Pane e Coperto');
        }else{
            $query = "SELECT nome_portata 
                      FROM Portata p
                      WHERE p.nome_portata NOT LIKE '%FISSO'
        			  ORDER BY p.id";
        }
    }else{
        $portate[]=array('error' => '#error#Errore durante l\'acquisizione dei dati'); 
    }
        
 $result = NULL;
				
	if( ($result = esegui_query($link, $query))){ 
        while($row = mysqli_fetch_assoc($result)){
            $portate[]=array('nome_portata' => $row['nome_portata']);

            /*if(isset($quantita_rimanente) && $quantita_rimanente){
                $date=ottieni_data_serata_attuale();
                if($date <=0){
                    $portate[]=array('error' => '#error#Errore durante l\'acquisizione della data');
                }
                else{
                    $query_port = "SELECT * FROM QuantitàPiattiSerata WHERE serata='$date' AND piatto = '".$row['nome_portata']."'";
                    if( ($res2 = esegui_query($link, $query_port))){ 
                        $row2 = mysqli_fetch_assoc($res2);
                        $portate[]=array('nome_portata' => $row['nome_portata'], 'categoria' => $row['categoria'], 'prezzo_finale' => $row['prezzo_finale'], 'quantita_rimanente' => $row2['quantità']);
                    }
                    else{
                        /* error *//*
                        $portate[]=array('error' => '#error#Errore durante l\'acquisizione dei dati');
                    }
                }
            }
            else{
                $portate[]=array('nome_portata' => $row['nome_portata']);
            }*/
        }
    }
    else{
        /* error */
        $portate[]=array('error' => '#error#Errore durante l\'acquisizione dei dati');
    }
    //mysqli_free_result($result);
	disconnetti_mysql($link, $result); #visto che non ho un result_set gli passo NULL.. nella funzione in core.in.php ho aggiunto il controllo
    //print_r($portate);
    
	echo json_encode($portate);

?>