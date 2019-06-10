<?php
  require_once 'include/header.inc.php';

  $link=connetti_mysql();
  $query="SELECT * FROM Ricevutefiscali";
  $res=mysqli_query($link, $query) or die("#error#".mysqli_error($link));
  ?>
  <section class="wrapper site-min-height">
  <!-- page start-->
  <section class="panel">
      <header class="panel-heading">
          Lista Ricevute
      </header>
      <div class="panel-body">
          <div class="adv-table editable-table ">
          <script type="text/javascript">
          	function stringStartsWith (string, prefix) {
			    return string.substring(0, prefix.length) == prefix;
			}
				function calcolaTotale(_data, _tavolo, _indice, tot){
					$.ajax({
				        type: 'POST',
				        url: "ajax/ottieni_totale_comanda.ajax.php",
				        dataType: "text",
				        timeout: 20000,
				        data : {
				          data: _data,
				          tavolo: _tavolo,
				          indice: _indice
				        },
				        success: function(result){
				            var errore = false;
				            if(stringStartsWith(result, '#error#')) errore=true;
				            if(!errore){
				              document.getElementById("tot"+tot).innerHTML=result;
				            }
				        },
				        error: function( jqXHR, textStatus, errorThrown ){
				          notify_top('#error#Errore durante l\'operazione', 'Calcola Totale Ricevuta');
				        }   

				  });
				}
</script>

            <div class="space15"></div>

              <table class="table table-striped table-hover table-bordered portate" id="editable-sample">
                <thead>
                  <tr>
                    <th>NR. Ric.</th>
                    <th>Data</th>
                    <th>Tavolo</th>
                    <th>Toatale</th>
                  </tr>
              </thead>
              <tbody>
              	<?php
              		$i=0;
              		while ($row=mysqli_fetch_assoc($res)) {
					  	echo '<tr>
					  		<td style="text-align: center;">
					  			'.$row["n_ric"].'
					  		</td>
					  		<td style="text-align: center;">
					  			'.$row["serata"].'
					  		</td>
					  		<td style="text-align: center;">';
					  			if($row["tavolo"]>0){ echo $row["tavolo"].'/'.$row["indice"];}
					  			else{ echo ' - ';}
					  		echo '</td>
					  		<td style="text-align: center;">
					  			€ ';
					  			if($row["tavolo"]==0){echo number_format($row["totale"], 2,',', ' ');}
					  			else{echo '<script type="text/javascript"> calcolaTotale("'.$row["serata"].'",'.$row["tavolo"].','.$row["indice"].','.$i.');</script>
					  						<span id="tot'.$i.'"></span>';
					  						$i++;
									}
					  		echo '</td>
					  	</tr>';
				  	}
                  ?>
              </tbody>
            </table>

          </div>
      </div>
  </section>
  <!-- page end-->
</section>
          	



<?php
    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
  require_once 'include/footer.inc.php';
?>