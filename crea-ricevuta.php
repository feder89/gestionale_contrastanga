<?php
  require_once 'include/header.inc.php';
  $serata_attuale = ottieni_data_serata_attuale();
  $serata_attuale = date_format(date_create($serata_attuale), "d/m/Y");
?>
<section class="wrapper site-min-height">
  <!-- page start-->
  <section class="panel">
      <header class="panel-heading">
          Crea Ricevuta
      </header>
      <div class="panel-body">
          <div class="adv-table editable-table ">


            <div class="space15"></div>

              <table class="table table-striped table-hover table-bordered portate" id="editable-sample">
                <thead>
                  <tr>
                    <th>Portata</th>
                    <th>Quantità</th>
                    <th>Prezzo</th>
                  </tr>
                  <tr>
                    <td style="text-align: center;"><button type="button" class="btn btn-default btn-lg active">Pasto Completo</button></td>
                    <td style="text-align: center;">
                      <div class="input-group">
                        <input type="number" class="form-control" id="quant" oninput="setQuant(this.value)" placeholder="Numero pasti">
                      </div>
                    </td>
                    <td>
                      <div class="input-group">
                        <span class="input-group-addon">€</span>
                        <input type="number" class="form-control" placeholder="Prezzo" id="price" oninput="setPrice(this.value)" aria-describedby="basic-addon1">
                        <span class="input-group-addon" >.00</span>
                      </div>
                    </td>
                    <tr>
                      <td colspan="3" style="text-align:right;">
                        Totale € <span id="tot"></span>
                        <button type="button" id="print" class="btn btn-primary btn-sm stampa-fisc" style="margin-left:10px;"><i class="fa fa-print" aria-hidden="true"></i><span style="margin-left:8px;">Stampa Ricevuta Fiscale</span></button>
                      </td>
                    </tr>                    
                  </tr>
              </thead>
              <tbody>
              </tbody>
            </table>

          </div>
      </div>
  </section>
  <!-- page end-->
</section>
<script type="text/javascript">
function stringStartsWith (string, prefix) {
    return string.substring(0, prefix.length) == prefix;
}
document.getElementById("print").disabled=true;
  var totale=(0).toFixed(2);
  var quantita=0;
  var prezzo = 0;
  //totale=(quantita*prezzo).toFixed(2);
  var totField=document.getElementById("tot");
  totField.innerHTML=totale;
  function setPrice(val){
    prezzo=$("#price").val();
    totale=(quantita*prezzo).toFixed(2);
    totField.innerHTML=totale;
    if(totale>0){document.getElementById("print").disabled=false;}
    else{document.getElementById("print").disabled=true;}
  }
  function setQuant(val){
    quantita=$("#quant").val();
    totale=(quantita*prezzo).toFixed(2);
    totField.innerHTML=totale;
    if(totale>0){document.getElementById("print").disabled=false;}
    else{document.getElementById("print").disabled=true;}
  }
  $('button').off('click').on('click', function(){
     if($(this).hasClass('stampa-fisc')) stampaRicevutaFiscale(quantita, prezzo, 'Pasto Completo');

  });
  function stampaRicevutaFiscale(quantita, prezzo, pasto) {
    $('#modal-gest #modal-titolo').text( 'Stampa Ricevuta Fiscale');
  $('#modal-gest .modal-body').html( '<span style="font-size:15px;">La ricevuta fiscale è stata stampata correttamente?</span>');
  $('#modal-gest .modal-footer').html( '<button class="btn btn-default ristampa" type="button"><i class="fa fa-print" aria-hidden="true"></i> Ristampa</button>'+
                    '<button class="btn btn-success ok" type="button"><i class="fa fa-check" aria-hidden="true"></i> Si</button>');
  var success=false;
  $.ajax({
        type: 'POST',
        url: "ajax/gestisci_crea_ricevuta_fiscale.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
          quantita: quantita,
          prezzo:prezzo,
          pasto: pasto
        },
        beforeSend: function(){
          $('#modal-gest .modal-footer button').prop("disabled",false);
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;
            if(!errore){
              avviaStampa(quantita,prezzo,pasto);
              $('#modal-gest').modal('show');
              notify_top(result, 'Stampa Ricevuta Fiscale');
            }
      
            if(errore){
              $('#modal-gest .modal-footer button').prop("disabled",false);
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
          $('#modal-gest .modal-footer button').prop("disabled",false);
          notify_top('#error#Errore durante l\'operazione', 'Stampa Ricevuta Fiscale');
        }   

  });
  
     
  
    $('#modal-gest .ristampa').off('click').on('click', function(){
      avviaStampa(quantita,prezzo,pasto);
    });
    $('#modal-gest .ok').off('click').on('click', function(){
      $('#modal-gest').modal('hide');
    });
  }
  function avviaStampa(quantita, prezzo, pasto){
    $.post('stampa/stampa_ricevuta_fiscale_spot.php', {
          quantita: quantita,
          prezzo:prezzo,
          pasto:pasto
        }, function(result) {
          newpage = result;
          /*myWindow = window.open('javascript: document.write(window.opener.newpage);', '_blank','height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
          myWindow.document.close();
          contoInviato(tavolo, indice);
          myWindow.print();
          myWindow.close();*/
          var myWindow = window.open("", "myWindow4", 'height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
          myWindow.document.write(newpage);
          myWindow.document.close();
          myWindow.print();
          myWindow.close();    
    });
  }
</script>

<?php
    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
  require_once 'include/footer.inc.php';
?>

