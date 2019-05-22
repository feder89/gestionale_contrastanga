$(document).ready(function(){
    aggiornaStatistiche();
    setInterval(function(){ 
        aggiornaListaComande();
        aggiornaStatistiche();
    }, 30000); //mezzo minuto
});

function aggiornaListaComande(){
    $.ajax({
        type: 'POST',
        url: "ajax/ottieni_lista_comande.ajax.php",
        dataType: "json",
        timeout: 20000,
        data : {
            solo_attive: 'true'
        },
        beforeSend: function(){
        },
        success: function(result){
            /* ajax succesfull, look what happened in the php */
            var msg = '';
            var errore = false;
            var lista_comande = new Array();

            $.each(result, function(index, comanda) {
                if (typeof comanda['error'] != 'undefined') { errore = true; msg = comanda['error']; return false; }
                lista_comande.push(comanda);
            });

            if(!errore){
                $('#lista-ordini-home tbody').empty();
                $.each(lista_comande, function(index, comanda) {
                    $('#lista-ordini-home tbody').append('<tr data-tavolo="'+comanda['tavolo']+'" data-indice="'+comanda['indice']+'">'+
                                                  '<td>'+comanda['responsabile']+'</td>'+
                                                  '<td>'+comanda['tavolo']+'/'+comanda['indice']+'</td>'+
                                          '</tr>');
                });
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        }   

    });
}

function aggiornaStatistiche(){

    //aggiorna posti liberi
    $.ajax({
        type: 'POST',
        url: "ajax/ottieni_info_posti.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {},
        beforeSend: function(){
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(!errore){
                var posti_occupati = (result.split("-"))[0];
                var posti_totali = (result.split("-"))[1];

                var posti_liberi = ((posti_totali-posti_occupati) < 0 ? 0 : posti_totali-posti_occupati);
                $('#posti-iberi-stat').text(posti_liberi);
            }

        },
        error: function( jqXHR, textStatus, errorThrown ){
        }   

    });

    //aggiorna altre statistiche
    $.ajax({
        type: 'POST',
        url: "ajax/ottieni_statistiche_index.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {},
        beforeSend: function(){
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(!errore){
                var num_comande = (result.split("/"))[0];
                var incasso = (result.split("/"))[1];
                var num_coperti = (result.split("/"))[2];

                $('#num-comande-stat').text(num_comande);
                $('#incasso-stat').text(incasso+' €');
                $('#num-coperti-stat').text(num_coperti);
            }

        },
        error: function( jqXHR, textStatus, errorThrown ){
        }   

    });
}














