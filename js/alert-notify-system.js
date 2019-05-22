$(document).ready(function(){
	controllaPiattiRimanenti();
	setInterval(function(){ 
		controllaPiattiRimanenti();
	}, 60000); //1minuto
});

var soglia = 10;
var lista_prec = null;

function controllaPiattiRimanenti(){
	$.ajax({
        type: 'POST',
        url: "ajax/ottieni_lista_piatti_rimanenti.ajax.php", 
        dataType: 'json',
        timeout: 20000,
        data : {
        	soglia: soglia
        },
        beforeSend: function(){
        },
        success: function(result){
            /* ajax succesfull, look what happened in the php */
            var msg = '';
            var errore = false;
            var lista_portate = new Array();

            $.each(result, function(index, portata) {
                if (typeof portata['error'] != 'undefined') { errore = true; msg = portata['error']; return false; }
                lista_portate.push(portata);
            });
            if(lista_portate.length == 0 ){ 
                $('#drodown-button-notify').find('.badge').remove();
                $('#header_notification_bar #text-top-not').text('Nessuna portata in esaurimento');
                return false; 
            }

            if(!errore){
                if(lista_prec == null){ lista_prec = lista_portate; }
                else if(stessePortate(lista_portate, lista_prec)) {  return false; }             

            	var num_alert = lista_portate.length;
	            //aggiorna badge numero alert
	            $('#drodown-button-notify').find('.badge').remove();
	            $('#drodown-button-notify').append('<span class="badge bg-warning">'+num_alert+'</span>');

                $('#header_notification_bar #text-top-not').text('Stanno per finire '+num_alert+' portate');

                $('#header_notification_bar #not-list li:not(.not-remove').remove();

                $.each(lista_portate, function(index, portata) {
                    $('#header_notification_bar #not-list').append('<li>'+
                          '<a href="#">'+
                              '<span class="label label-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></span>&nbsp;&nbsp;'+
                              portata['nome_portata']+
                              '<span class="small italic" style="margin-left:5px;">'+portata['quantita']+' rimanenti</span>'+
                          '</a>'+
                      '</li>');
                });
                

                $('#drodown-button-notify').off('click').on('click', function(){
                    $('#drodown-button-notify').find('.badge').removeClass('bg-warning');
                });

                lista_prec = lista_portate;

            }
            else notify_top(msg, 'Ottieni lista Piatti Rimanenti'); 
	    },
        error: function( jqXHR, textStatus, errorThrown ){
            notify_top("#error#Errore durante l'operazione", 'Ottieni lista Piatti Rimanenti');
        }
    });
}


function stessePortate(arr1, arr2){
    if(arr1.length != arr2.length) return false;
    var res = true;
    $.each(arr1, function(index, portata1) {
        var nome_portata = portata1['nome_portata'];
        var has_value = false;
        $.each(arr2, function(index, portata2) {
            if(nome_portata == portata2['nome_portata']) has_value=true;
        });
        if(!has_value){ res = false; return false;}
    });
    return res;
}













