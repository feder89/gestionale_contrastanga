<?php
require '../assets/php-export-data-master/php-export-data.class.php';
require_once '../include/core.inc.php';
$link = connetti_mysql();
$date = ottieni_data_serata_attuale();
//$date="2016-05-27";
$serate = array();
$testa = array();
$testa[] = 'Portata';
$nome_file = 'esporta_totale_vendite_quintana.xls';
$exporter = new ExportDataExcel('browser', $nome_file);
$exporter->initialize();
$portate = array();
$query_serata="SELECT data FROM Serata";
if(!($res1=esegui_query($link,$query_serata))){
    $exporter->addRow("errore nel database");
} else{
	while ($row1 = mysqli_fetch_assoc($res1)){
         $serate[] = $row1['data']; 
         $testa[] = date('d/m/Y', strtotime($row1['data']));
         //$exporter->addRow($row1['data']);
    }
    $testa[] = "Totale piatti venduti";
    $testa[] = "Prezzo";
    //$exporter->addRow("Portata", $serate, "Totale piatti venduti", "Prezzo");
    $exporter->addRow($testa);
}
$query_portate = "SELECT * FROM Portata p ORDER BY FIELD(p.categoria, 'pane e coperto', 'antipasto', 'bruschette e crostoni', 'primo', 'secondo', 'piadina', 'contorno', 'dolce', 'bevanda', 'cantinetta'), p.nome_portata";
if(!($res3=esegui_query($link, $query_portate))){
    $exporter->addRow("errore");
}else{
    while($row3=mysqli_fetch_assoc($res3)){
        $portate[] = array("nome" => $row3['nome_portata'], "price" => $row3['prezzo_finale']);
    }
}

/*$query_ordini_totale="SELECT o.portata, p.categoria, SUM(o.quantita) AS num_portate, p.prezzo_finale
					  FROM Ordini o
            		  INNER JOIN Portata p
            		  ON p.nome_portata=o.portata
            		  GROUP BY o.portata
            		  ORDER BY FIELD(p.categoria, 'pane e coperto', 'antipasto', 'bruschette e crostoni', 'primo', 'secondo', 'piadina', 'contorno', 'dolce', 'bevanda', 'cantinetta'), p.nome_portata";
if(!($res=esegui_query($link,$query_ordini_totale))){
    $exporter->addRow("errore");
}
else {
	//$exporter->addRow(array("Portata", "Categoria", "Quantità", "Prezzo"));
	while(($row = mysqli_fetch_assoc($res))){ */
        
        foreach ($portate as $key=>$piatto) {
            $result=array();
            $totale_piatti=0;
            $result[]=$piatto['nome'];
            $prezzo_p=0;
            foreach ($serate as $key=>$serata) {
                $query_ordini_serata="SELECT SUM(o.quantita) AS num_portate, p.prezzo_finale
        					  FROM Ordini o
                    		  INNER JOIN Portata p
                    		  ON p.nome_portata=o.portata
                              INNER JOIN comande c ON c.tavolo=o.tavolo AND c.indice=o.indice AND c.serata=o.serata
                    		  WHERE c.serata='".$serata."' AND o.portata = '".$piatto['nome']."' 
                    		  GROUP BY o.portata";
                $res2=esegui_query($link,$query_ordini_serata);
                if(mysqli_num_rows($res2)<1){
                    //$exporter->addRow("errore");
                    //unset($array)?$result=array():$result=array();
                    //$result=array();
                    $prezzo_p= "€ ".number_format($piatto['price'], 2, '.', ' ');
                    $result[]=0;
                    $totale_piatti +=0;
                }else{
                    //$prezzo_p=0;
                    while ($row2=mysqli_fetch_assoc($res2)){
                        $result[]=$row2['num_portate'];
                        $totale_piatti += $row2['num_portate'];
                        $prezzo_p= "€ ".number_format($piatto['price'], 2, '.', ' '); 
                        //$exporter->addRow(array($piatto, $row2['num_portate'],"€ ".number_format($row2['prezzo_finale'], 2, '.', ' ')));
                    }
                }
            }
            $result[]=$totale_piatti;
            $result[]=$prezzo_p;
            //$exporter->addRow(array($row['portata'],$row['num_portate'],"€ ".number_format($row['prezzo_finale'], 2, '.', ' ')));
            $exporter->addRow($result);
            //unset($result)?$result=array():$result=array();
            //$result=array();
        }
        
	/*}
} */
$exporter->addRow(array());
$exporter->addRow(array('Menù', 'n. venduti', 'prezzo'));
$query_quant_menu_fissi="SELECT menu, COUNT(*) AS NUM, prezzo_fisso FROM Comande c INNER JOIN Menu m ON c.menu=m.nome_menu WHERE M.fisso=1 GROUP BY c.menu";
$query_cop_menu_fissi="SELECT menu, SUM(o.quantita) AS n FROM Comande c INNER JOIN Ordini o ON c.tavolo=o.tavolo AND c.indice=o.indice AND c.serata=o.serata INNER JOIN Menu m ON c.menu=m.nome_menu WHERE o.portata='Pane e Coperto' AND m.fisso=1 GROUP BY m.nome_menu";
$res5=esegui_query($link,$query_quant_menu_fissi);
if(mysqli_num_rows($res5)>=1){
    while($row5=mysqli_fetch_assoc($res5)){
        $exporter->addRow(array($row5['menu'], $row5['NUM'], "€ ".number_format($row5['prezzo_fisso'],2, '.',' ')));
    } 
}
mysqli_free_result($res5);
$exporter->addRow(array());
$exporter->addRow(array('Menù', 'coperti venduti'));
$res4=esegui_query($link,$query_cop_menu_fissi);
if(mysqli_num_rows($res4)>=1){
    while($row4=mysqli_fetch_assoc($res4)){
        $exporter->addRow(array($row4['menu'], $row4['n']));
    } 
}
$exporter->finalize();
disconnetti_mysql($link);
exit();

?>
				
