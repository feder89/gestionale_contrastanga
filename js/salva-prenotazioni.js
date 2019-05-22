$('#salva-prenotati').click(function (e) {
	e.preventDefault();
	var tav=$('#tavoli-preonotati').val();
	var cop=$('#coperti-preonotati').val();
	//console.log(tav, cop);
	$.ajax({
                        type: 'POST',
                        url: "ajax/salva-prenotazioni.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            tavoli: tav,
                            coperti: cop
                        },
                        beforeSend: function(){
                        },
                        success: function(result){
                            /* Editing this row and want to save it */
                            var errore = false;
                            if(stringStartsWith(result, '#error#')) errore=true;

                            notify_top(result, 'Operazione di aggiornamento');     
                        },
                        error: function( jqXHR, textStatus, errorThrown ){
                            /*alert('Errore durante operazione '+errorThrown );*/
                            notify_top("#error#Errore durante l'operazione", 'Operazione di aggiornamento'); 
                        }
                    });
})