var open_tab = 0; //all'inizio parte sempre dal tab 0 (tutte le comande)
var order_col = 2; //ordina per comanda
var continua; //per aggiornare o no pagina su salva_nuova_comanda

$(document).ready(function(){

	$('#buttons-wrapper li a').off('click').on('click', function(){
		open_tab = $(this).closest('li').index();
		aggiornaListaComande();
	});

	//aggiornamento automatico
	setInterval(function(){ 
		aggiornaListaComande();
		aggiornaPostiLiberi();
	}, 60000); //1minuto

	//click riga tabella comande
	$('#lista-ordini tbody').on('click', 'tr', function(){
        var t=$(this).data('tavolo');
        var i=$(this).data('indice');
        if($(this).data('attiva') == 1){
        	modificaComanda(t,i);
        }
        else{
        	visualizzaComandaChiusa(t,i);
        }
    });

	$('#lista-ordini thead th:nth-child(3)').addClass('highlighted-sorted');
    $('#lista-ordini thead').on('click', 'th', function(){
        var idx = $(this).index();
        if(idx == 2){ 
        	order_col=2; 
        	$('#lista-ordini thead th').removeClass('highlighted-sorted');
        	$('#lista-ordini thead th:nth-child(3)').addClass('highlighted-sorted');
        	aggiornaListaComande(); 
        	
        }
        else if (idx == 3){ 
        	order_col=3; 
        	$('#lista-ordini thead th').removeClass('highlighted-sorted');
        	$('#lista-ordini thead th:nth-child(4)').addClass('highlighted-sorted');
        	aggiornaListaComande(); 
        }
    });

	//bottoni piu e meno
	$('#lista-piatti').on('click', '.btn-op', function(){
		var input = $(this).closest('td').find('input');
		var input_val = input.val();
		if(input_val == '') input_val = 0;

		switch(String($(this).data('op'))){
			case '+1':
				input.val( parseInt(input_val)+1 );
				$(this).closest('td').find('input').change();
				break;
			case '-1':
				input.val( parseInt(input_val)-1 );
				$(this).closest('td').find('input').change();
				break;
		}
	});

	//bottoni a valore fisso
	$('#lista-piatti').on('click', '.btn-fixval', function(){
		$(this).closest('td').find('input').val( parseInt($(this).data('val')));
		$(this).closest('td').find('input').change();
	});

	$('#menu_name').selectpicker({
		noneSelectedText:'Nessun Menù selezionato',
	});

	//nuova comanda
	$('#wrapper-right').on('click', '.nuova_comanda', function(){
		var tavolo = $(this).data('tavolo');
		var responsabile = $(this).data('responsabile');
		var zona = $(this).data('zona');
		nuovaComanda(tavolo, responsabile, zona);
	});
	//annulla
	$('#wrapper-right').on('click', '.annulla', function(){
		annullaNuovaComanda();
	});
});


var timer;

function nuovaComanda(tavolo, responsabile, zona){
	$('#wrapper-right').fadeOut("fast", function(){
		$('#selezione-tavoli').hide();
		_inflate_nuova_comanda(tavolo, responsabile, zona);
		$('#nuova_comanda').show();
		$(this).fadeIn("fast");	
	});
}

function _inflate_nuova_comanda(tavolo, responsabile, zona){

	$('#bottoni-comanda-mod').empty();

	$('#totale-parziale').val('');
	$('#totale-parziale-persona').val('');

	$('#numero-soci').removeClass('input-error');
	$('#annotazioni').removeClass('input-error');
	$('#sconto-manuale').removeClass('input-error');
	//resetta la select
	$('#menu_name').prop('selectedIndex',0);
	$('#menu_name').selectpicker('render');

	//resetta soci
	$('#numero-soci').val('');
	$('#annotazioni').val('');
	$('#sconto-manuale').val('');

	//disattiva bottone salva
	$('#wrapper-right .salva').prop("disabled",true);
    $('#wrapper-right .salvachiudi').prop("disabled",true);
	$('#wrapper-right .annulla').prop("disabled",false);

	$('#menu_name').off('change').on('change', function(){
		$('#menu_name').selectpicker('render');

		$('#totale-parziale').val('');
		$('#totale-parziale-persona').val('');

		//disattiva bottone salva
		$('#wrapper-right .salva').prop("disabled",true);
        $('#wrapper-right .salvachiudi').prop("disabled",true);

		var val = $('#menu_name').val();
		if(val==''){
			//svuota tabella lista piatti
			$('#lista-piatti tbody').html('<tr><td colspan="6" style="padding:10px 20px !important; text-align:center; ">Seleziona un Menù</td></tr>');
		}
		else{
			$.ajax({
		        type: 'POST',
		        url: "ajax/ottieni_lista_portate.ajax.php", 
		        dataType: 'json',
		        timeout: 20000,
		        data : {
		            nome_menu_order: val
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

		            if(!errore){
		            	$('#lista-piatti tbody').empty();
		            	$.each(lista_portate, function(index, portata) {
			           		$('#lista-piatti tbody').append('<tr '+( portata['nome_portata'].toLowerCase() == 'pane e coperto' ? 'class="highlighted"' : '')+'><td>'+portata['prezzo_finale']+'</td><td>'+portata['nome_portata']+'</td><td>'+portata['categoria']+'</td><td>'+( (portata['quantita_rimanente'] != null && portata['nome_portata'].toLowerCase() != 'pane e coperto') ? portata['quantita_rimanente'] : '')+'</td><td></td>'+
		           												'<td>'+
								                                    '<div class="row">'+
								                                        '<div class="fl">'+
								                                            '<div class="btn-group" role="group" aria-label="...">'+
								                                                '<button type="button" class="btn btn-default btn-fixval" data-val="1">1</button>'+
								                                                '<button type="button" class="btn btn-default btn-fixval" data-val="2">2</button>'+
								                                                '<button type="button" class="btn btn-default btn-fixval" data-val="3">3</button>'+
								                                                '<button type="button" class="btn btn-default btn-fixval" data-val="4">4</button>'+
								                                                '<button type="button" class="btn btn-default btn-fixval" data-val="5">5</button>'+
								                                            '</div>'+
								                                        '</div>'+
								                                        '<div class="input-group">'+
								                                            '<span class="input-group-btn">'+
								                                                '<button type="button" class="btn btn-danger btn-op" data-op="-1">'+
								                                                    '<span class="glyphicon glyphicon-minus"></span>'+
								                                                '</button>'+
								                                            '</span>'+
								                                            '<input step="1" type="number" class="form-control small num"  value="">'+
								                                            '<span class="input-group-btn">'+
								                                                '<button type="button" class="btn btn-success btn-op" data-op="+1">'+
								                                                    '<span class="glyphicon glyphicon-plus"></span>'+
								                                                '</button>'+
								                                            '</span>'+
								                                        '</div>'+
								                                    '</div>'+
								                                '</td>'+
			           									   '</tr>');
			            });

			            $('#bottoniright').html('<button type="button" style="margin-right:7px;" class="btn btn-success salvachiudi">Salva e chiudi <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button>'
                                                +'<button type="button" style="margin-right:7px;" class="btn btn-success salva">Salva <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button>'
                                                +'<button type="button" class="btn btn-danger annulla">Annulla</button>'
                                        );
			            $('#bottoni-comanda-mod').html('<button type="button" style="margin-right:7px;" class="btn btn-success salvachiudi">Salva e chiudi <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button><button type="button" style="margin-right:7px;" class="btn btn-success salva">Salva <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button><button type="button" class="btn btn-danger annulla">Annulla</button></div>');
                        //$('#bottoni-comanda-mod1').html('<button type="button" style="margin-right:7px;" class="btn btn-success salvachiudi">Salva e chiudi <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button><button type="button" style="margin-right:7px;" class="btn btn-success salva">Salva <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button>');
			            $('#bottonigetcom').empty();

			            //annulla
		           		$('#wrapper-right .salva').prop("disabled",false);
                        $('#wrapper-right .salvachiudi').prop("disabled",false);
                        $('#wrapper-right .salva').off('click').on('click', function(){
//                            var indice=ottieniNuovoIndice(tavolo);
							salvaNuovaComanda(tavolo, responsabile);
                            /*if(!continua){clearTimeout(timer);
    					    timer = setTimeout(function(){ 
      							refreshComanda(tavolo, indice);
      						}, 5000);}*/
                        });
                        $('#wrapper-right .salvachiudi').off('click').on('click', function(){
							salvaNuovaComandaChiudi(tavolo, responsabile);
                        });

						$('#lista-piatti tbody input').off('keydown').on('keydown', function(){
							clearTimeout(timer);
							timer = setTimeout(function(){
							  	aggiornaTotParzialeNuovo(tavolo);
							}, 2000);
						});

						$('#lista-piatti tbody input').off('change').on('change', function(){
							clearTimeout(timer);
							timer = setTimeout(function(){
							  	aggiornaTotParzialeNuovo(tavolo);
							}, 2000);
						});

						$('#numero-soci').off('keydown').on('keydown', function(){
							clearTimeout(timer);
							timer = setTimeout(function(){
							  	aggiornaTotParzialeNuovo(tavolo);
							}, 2000);
						});

						$('#sconto-manuale').off('keydown').on('keydown', function(){
							clearTimeout(timer);
							timer = setTimeout(function(){
							  	aggiornaTotParzialeNuovo(tavolo);
							}, 2000);
						});

						$('#numero-soci').off('change').on('change', function(){
							clearTimeout(timer);
							timer = setTimeout(function(){
							  	aggiornaTotParzialeNuovo(tavolo);
							}, 2000);
						});

						$('#sconto-manuale').off('change').on('change', function(){
							clearTimeout(timer);
							timer = setTimeout(function(){
							  	aggiornaTotParzialeNuovo(tavolo);
							}, 2000);
						});

		            }
		            else notify_top(msg, 'Ottieni lista Portate'); 
			    },
		        error: function( jqXHR, textStatus, errorThrown ){
		            notify_top("#error#Errore durante l'operazione", 'Ottieni lista Portate');
		        }
		    });
		}
	});

	//info tavolo
	$('.info-tavolo').text('Tavolo: '+tavolo);

	//info responsabile
	$('.info-responsabile span').text(responsabile);

	//info zona
	$('.info-zona span').text(zona);

	//svuota tabella lista piatti
	$('#lista-piatti tbody').html('<tr><td colspan="6" style="padding:10px 20px !important; text-align:center; ">Seleziona un Menù</td></tr>');

	//imposta lo standard
	var select_val = '';
	$("#menu_name option").each(function(){
		if(select_val == '' && $(this).val() != '') select_val = $(this).val();
	    if($(this).val().toLowerCase() == 'standard')  select_val = $(this).val();
	});

	$('#menu_name').val(select_val);
	$('#menu_name').selectpicker('render');
	$("#menu_name").change();
}
function refreshComanda(tavolo, indice){
    window.location.href = 'gestione_comande.php?tavolo='+tavolo+'&indice='+indice;
}
function annullaNuovaComanda(){
	$('#wrapper-right').fadeOut("fast", function(){
		$('#nuova_comanda').hide();
		$('#selezione-tavoli').show();
		$(this).fadeIn("fast");	
	});
}
function ottieniNuovoIndice(tavolo){
    var indice= null;
    $.ajax({
        async: false,
        type: 'POST',
        url: "ajax/ottieni_indice_tavolo.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
            tavolo: tavolo
        },
        beforeSend: function(){
        	//disattiva bottone salva
			/*$('#wrapper-right .salva').prop("disabled",true);
              $('#wrapper-right .salvachiudi').prop("disabled",true);
			$('#modal-gest .modal-footer button').prop("disabled",true);*/
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(errore){
            	notify_top(result, 'Ottieni indice'); 
            	//attiva bottone salva
				/*$('#wrapper-right .salva').prop("disabled",false);
                  $('#wrapper-right .salvachiudi').prop("disabled",false);
				$('#modal-gest .modal-footer button').prop("disabled",false);*/
            }
            else {
            	//disattiva bottone salva
                  
                  indice = result;
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	//attiva bottone salva
        	/*$('#modal-gest .modal-footer button').prop("disabled",false);
			$('#wrapper-right .salva').prop("disabled",false);
              $('#wrapper-right .salvachiudi').prop("disabled",false);*/
            notify_top("#error#Errore durante l'operazione", 'Ottieni indice'); 
        }   
	});
    return indice;
}
function salvaNuovaComanda(tavolo, responsabile){

	//controlla i valori
	if(! _controlla_valori()) return false;
    continua = false;
	var portate = new Array();
	$('#lista-piatti tbody tr').each(function(){
		var portata = $(this).find('td:nth-child(2)').text();
		var quantita = parseInt($(this).find('td:nth-child(6)').find('input').val());
		if(!isNaN(quantita) && quantita>0){
			portate.push(new Array(portata, quantita));
		}
        if(portata == 'Pane e Coperto' && quantita<1){
            continua=true;
            alert ("nessun coperto inserito");
            //notify_top('#error#Nessun coperto inserito', 'Inserimento Nuova Comanda');
            return false;
        }else if(portata == 'Pane e Coperto' && isNaN(quantita)){
            continua=true;
            alert ("nessun coperto inserito");
            //notify_top('#error#Nessun coperto inserito', 'Inserimento Nuova Comanda');
            return false;
        }
	});
	if(portate.length<1){
        continua=true;
        notify_top('#error#Nessuna portata selezionata', 'Inserimento Nuova Comanda');  
        return false; }

	//avvia stampa
    if(!continua){
	_stampa_su_stampante(tavolo, portate, null);

	$('#modal-gest #modal-titolo').text( 'Stampa Comanda');
	$('#modal-gest .modal-body').html( '<span style="font-size:15px;">La comanda è stata stampata correttamente?</span>');
	$('#modal-gest .modal-footer').html( '<button class="btn btn-default ristampa" type="button"><i class="fa fa-print" aria-hidden="true"></i> Ristampa</button>'+
										'<button class="btn btn-success ok" type="button"><i class="fa fa-check" aria-hidden="true"></i> Si</button>');
     $('#modal-gest').modal('show');
	 var menu = $('#menu_name').val();
     var indice=ottieniNuovoIndice(tavolo);

		var numero_soci = parseInt($('#numero-soci').val());
		if(isNaN(numero_soci)){
			if($('#numero-soci').val() != ''){

				$('#numero-soci').addClass('input-error');
		                   		
				$('#numero-soci').keyup(function(){
					$('#numero-soci').removeClass('input-error');
				});
				return false;
			}
			else {
				numero_soci = 0;
			}
		}

		var sconto_manuale = parseFloat($('#sconto-manuale').val());
		if(isNaN(sconto_manuale)){
			if($('#sconto-manuale').val() != ''){

				$('#sconto-manuale').addClass('input-error');
		                   		
				$('#sconto-manuale').keyup(function(){
					$('#sconto-manuale').removeClass('input-error');
				});
				return false;
			}
			else {
				sconto_manuale = 0;
			}
		}

		var annotazioni = $('#annotazioni').val();
        

		if(portate.length <=0){ notify_top('#error#Nessuna portata selezionata', 'Inserimento Nuova Comanda');  return false; }

		$.ajax({
	        type: 'POST',
	        url: "ajax/gestione_comande.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'nuova_comanda',
	            menu: menu,
	            numero_soci: numero_soci,
	            sconto_manuale: sconto_manuale,
	            tavolo: tavolo,
	            ordini: portate,
	            responsabile: responsabile,
	            annotazioni: annotazioni
	        },
	        beforeSend: function(){
	        	//disattiva bottone salva
				$('#wrapper-right .salva').prop("disabled",true);
                $('#wrapper-right .salvachiudi').prop("disabled",true);
				$('#modal-gest .modal-footer button').prop("disabled",false);
	        },
	        success: function(result){
	            var errore = false;
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore){
	            	notify_top(result, 'Inserimento Nuova Comanda'); 
	            	//attiva bottone salva
					$('#wrapper-right .salva').prop("disabled",false);
                    $('#wrapper-right .salvachiudi').prop("disabled",false);
					$('#modal-gest .modal-footer button').prop("disabled",false);
	            }
	            else if(stringStartsWith(result, '#indice#')){
	            	//disattiva bottone salva
					//$('#wrapper-right .salva').prop("disabled",true);
                    //$('#wrapper-right .salvachiudi').prop("disabled",true);
	            	//torna indietro 
	            	//annullaNuovaComanda();
	            	//aggiorna lista comande
	            	aggiornaListaComande();
	            	aggiornaPostiLiberi();
                    $('#wrapper-right .salva').prop("disabled",false);
                    $('#wrapper-right .salvachiudi').prop("disabled",false);
	            	//chiudi modal
					//$('#modal-gest').modal('hide');
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	        	//attiva bottone salva
	        	$('#modal-gest .modal-footer button').prop("disabled",false);
				$('#wrapper-right .salva').prop("disabled",false);
                $('#wrapper-right .salvachiudi').prop("disabled",false);
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Nuova Comanda'); 
	        }   

		});
        
	//listener bottoni
	$('#modal-gest .ristampa').off('click').on('click', function(){
		_stampa_su_stampante(tavolo, portate, null);
	});
	$('#modal-gest .ok').off('click').on('click', function(){
    
		//salva su db
		/*var menu = $('#menu_name').val();

		var numero_soci = parseInt($('#numero-soci').val());
		if(isNaN(numero_soci)){
			if($('#numero-soci').val() != ''){

				$('#numero-soci').addClass('input-error');
		                   		
				$('#numero-soci').keyup(function(){
					$('#numero-soci').removeClass('input-error');
				});
				return false;
			}
			else {
				numero_soci = 0;
			}
		}

		var sconto_manuale = parseFloat($('#sconto-manuale').val());
		if(isNaN(sconto_manuale)){
			if($('#sconto-manuale').val() != ''){

				$('#sconto-manuale').addClass('input-error');
		                   		
				$('#sconto-manuale').keyup(function(){
					$('#sconto-manuale').removeClass('input-error');
				});
				return false;
			}
			else {
				sconto_manuale = 0;
			}
		}

		var annotazioni = $('#annotazioni').val();
        

		if(portate.length <=0){ notify_top('#error#Nessuna portata selezionata', 'Inserimento Nuova Comanda');  return false; }

		$.ajax({
	        type: 'POST',
	        url: "ajax/gestione_comande.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'nuova_comanda',
	            menu: menu,
	            numero_soci: numero_soci,
	            sconto_manuale: sconto_manuale,
	            tavolo: tavolo,
	            ordini: portate,
	            responsabile: responsabile,
	            annotazioni: annotazioni
	        },
	        beforeSend: function(){
	        	//disattiva bottone salva
				$('#wrapper-right .salva').prop("disabled",true);
                $('#wrapper-right .salvachiudi').prop("disabled",true);
				$('#modal-gest .modal-footer button').prop("disabled",true);
	        },
	        success: function(result){
	            var errore = false;
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore){
	            	notify_top(result, 'Inserimento Nuova Comanda'); 
	            	//attiva bottone salva
					$('#wrapper-right .salva').prop("disabled",false);
                    $('#wrapper-right .salvachiudi').prop("disabled",false);
					$('#modal-gest .modal-footer button').prop("disabled",false);
	            }
	            else if(stringStartsWith(result, '#indice#')){
	            	//disattiva bottone salva
					//$('#wrapper-right .salva').prop("disabled",true);
                    //$('#wrapper-right .salvachiudi').prop("disabled",true);
	            	//torna indietro 
	            	//annullaNuovaComanda();
	            	//aggiorna lista comande
	            	aggiornaListaComande();
	            	aggiornaPostiLiberi();
                    $('#wrapper-right .salva').prop("disabled",false);
                    $('#wrapper-right .salvachiudi').prop("disabled",false);
	            	//chiudi modal
					$('#modal-gest').modal('hide');
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	        	//attiva bottone salva
	        	$('#modal-gest .modal-footer button').prop("disabled",false);
				$('#wrapper-right .salva').prop("disabled",false);
                $('#wrapper-right .salvachiudi').prop("disabled",false);
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Nuova Comanda'); 
	        }   

		}); */
        //('#modal-gest').modal('hide');
        refreshComanda(tavolo, indice);
        //$('#modal-gest').modal('hide');
	});
    }
	//mostra modal
//	$('#modal-gest').modal('show');
}

function salvaNuovaComandaChiudi(tavolo, responsabile){
     continua=false;
	//controlla i valori
	if(! _controlla_valori()) return false;

	var portate = new Array();
	$('#lista-piatti tbody tr').each(function(){
		var portata = $(this).find('td:nth-child(2)').text();
		var quantita = parseInt($(this).find('td:nth-child(6)').find('input').val());
		if(!isNaN(quantita) && quantita>0){
			portate.push(new Array(portata, quantita));
		}
        if(portata == 'Pane e Coperto' && quantita<1){
            continua=true;
            alert ("nessun coperto inserito");
            //notify_top('#error#Nessun coperto inserito', 'Inserimento Nuova Comanda');
            return false;
        }else if(portata == 'Pane e Coperto' && isNaN(quantita)){
            continua=true;
            alert ("nessun coperto inserito");
            //notify_top('#error#Nessun coperto inserito', 'Inserimento Nuova Comanda');
            return false;
        }
	});
	if(portate.length <=0){ notify_top('#error#Nessuna portata selezionata', 'Inserimento Nuova Comanda');  return false; }

	//avvia stampa
    if(!continua){
	_stampa_su_stampante(tavolo, portate, null);

	$('#modal-gest #modal-titolo').text( 'Stampa Comanda');
	$('#modal-gest .modal-body').html( '<span style="font-size:15px;">La comanda è stata stampata correttamente?</span>');
	$('#modal-gest .modal-footer').html( '<button class="btn btn-default ristampa" type="button"><i class="fa fa-print" aria-hidden="true"></i> Ristampa</button>'+
										'<button class="btn btn-success ok" type="button"><i class="fa fa-check" aria-hidden="true"></i> Si</button>');
	
	//listener bottoni
	$('#modal-gest .ristampa').off('click').on('click', function(){
		_stampa_su_stampante(tavolo, portate, null);
	});
	$('#modal-gest .ok').off('click').on('click', function(){
		//salva su db
        salva=true;
		var menu = $('#menu_name').val();

		var numero_soci = parseInt($('#numero-soci').val());
		if(isNaN(numero_soci)){
			if($('#numero-soci').val() != ''){

				$('#numero-soci').addClass('input-error');
		                   		
				$('#numero-soci').keyup(function(){
					$('#numero-soci').removeClass('input-error');
				});
				return false;
			}
			else {
				numero_soci = 0;
			}
		}

		var sconto_manuale = parseFloat($('#sconto-manuale').val());
		if(isNaN(sconto_manuale)){
			if($('#sconto-manuale').val() != ''){

				$('#sconto-manuale').addClass('input-error');
		                   		
				$('#sconto-manuale').keyup(function(){
					$('#sconto-manuale').removeClass('input-error');
				});
				return false;
			}
			else {
				sconto_manuale = 0;
			}
		}

		var annotazioni = $('#annotazioni').val();

		if(portate.length <=0){ notify_top('#error#Nessuna portata selezionata', 'Inserimento Nuova Comanda');  return false; }

		$.ajax({
	        type: 'POST',
	        url: "ajax/gestione_comande.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'nuova_comanda',
	            menu: menu,
	            numero_soci: numero_soci,
	            sconto_manuale: sconto_manuale,
	            tavolo: tavolo,
	            ordini: portate,
	            responsabile: responsabile,
	            annotazioni: annotazioni
	        },
	        beforeSend: function(){
	        	//disattiva bottone salva
				$('#wrapper-right .salva').prop("disabled",true);
                $('#wrapper-right .salvachiudi').prop("disabled",true);
				$('#modal-gest .modal-footer button').prop("disabled",true);
	        },
	        success: function(result){
	            var errore = false;
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore){
	            	notify_top(result, 'Inserimento Nuova Comanda'); 
	            	//attiva bottone salva
					$('#wrapper-right .salva').prop("disabled",false);
                    $('#wrapper-right .salvachiudi').prop("disabled",false);
					$('#modal-gest .modal-footer button').prop("disabled",false);
	            }
	            else{
	            	//disattiva bottone salva
					$('#wrapper-right .salva').prop("disabled",true);
                    $('#wrapper-right .salvachiudi').prop("disabled",true);
	            	//torna indietro 
	            	annullaNuovaComanda();
	            	//aggiorna lista comande
	            	aggiornaListaComande();
	            	aggiornaPostiLiberi();
	            	//chiudi modal
					$('#modal-gest').modal('hide');
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	        	//attiva bottone salva
	        	$('#modal-gest .modal-footer button').prop("disabled",false);
				$('#wrapper-right .salva').prop("disabled",false);
                $('#wrapper-right .salvachiudi').prop("disabled",false);
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Nuova Comanda'); 
	        }   

		});

	});
    }

	//mostra modal
	$('#modal-gest').modal('show');
}

function _controlla_valori(){

	//controlla portate
	var portate = new Array();
	$('#lista-piatti tbody tr').each(function(){
		var portata = $(this).find('td:nth-child(2)').text();
		var quantita = parseInt($(this).find('td:nth-child(6)').find('input').val());
		if(!isNaN(quantita) && quantita>0){
			portate.push(new Array(portata, quantita));
		}
	});
	if(portate.length <=0){
    continua=true; 
    notify_top('#error#Nessuna portata selezionata', 'Inserimento Nuova Comanda');  return false; }

	//numero soci
	var numero_soci = parseInt($('#numero-soci').val());
	if(isNaN(numero_soci)){
		if($('#numero-soci').val() != ''){

			$('#numero-soci').addClass('input-error');
	                   		
			$('#numero-soci').keyup(function(){
				$('#numero-soci').removeClass('input-error');
			});
			return false;
		}
	}
	else if(numero_soci<0){
		$('#numero-soci').addClass('input-error');
	                   		
		$('#numero-soci').keyup(function(){
			$('#numero-soci').removeClass('input-error');
		});
		return false;
	}

	//sconto manuale
	var sconto_manuale = parseFloat($('#sconto-manuale').val());
	if(isNaN(sconto_manuale)){
		if($('#sconto-manuale').val() != ''){

			$('#sconto-manuale').addClass('input-error');
	                   		
			$('#sconto-manuale').keyup(function(){
				$('#sconto-manuale').removeClass('input-error');
			});
			return false;
		}
	}
	else if(sconto_manuale<0){
		$('#sconto-manuale').addClass('input-error');
	                   		
		$('#sconto-manuale').keyup(function(){
			$('#sconto-manuale').removeClass('input-error');
		});
		return false;
	}

	//sconto manuale
	var annotazioni = $('#annotazioni').val();
	//true

	return true;
}

function _stampa_su_stampante(tavolo, portate, indice){

	$.post('stampa/stampa_ordine.php', {
            tavolo: tavolo,
        	ordini: portate,
        	indice: ( indice == null ? 0 : indice)
        }, function(result) {
        	newpage = result;
        	var myWindow = window.open("", "myWindow2", 'height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
        	myWindow.document.write(newpage);
        	myWindow.document.close();
        	myWindow.print();
        	myWindow.close(); 
    });

	/*
	//avvia stampa
	$.ajax({
        type: 'POST',
        url: "stampa/stampa_ordine.php",
        dataType: "text",
        timeout: 20000,
        data : {
            tavolo: tavolo,
        	ordini: portate,
        	indice: indice
        },
        beforeSend: function(){
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(errore){
            	notify_top(result, 'Stampa Comanda'); 
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	notify_top('#error#Errore durante la stampa', 'Stampa Comanda'); 
        }   

	});*/
}

/**************************************
 MODIFICA
 ******************************************/
function modificaComanda(tavolo, indice){
	_inflate_modifica_comanda(tavolo, indice);
}

function _inflate_modifica_comanda(tavolo, indice){

	$('#totale-parziale').val('');
	$('#totale-parziale-persona').val('');

	$('#numero-soci').removeClass('input-error');
	$('#annotazioni').removeClass('input-error');
	$('#sconto-manuale').removeClass('input-error');
	$('#wrapper-right .annulla').prop("disabled",false);

	$.ajax({
        type: 'POST',
        url: "ajax/ottieni_info_comanda.ajax.php", 
        dataType: 'json',
        timeout: 20000,
        data : {
            tavolo: tavolo,
            indice: indice
        },
        beforeSend: function(){
        },
        success: function(result){
            /* ajax succesfull, look what happened in the php */
            var msg = '';
            var errore = false;
            var comanda = result;
            var ordini;

            if (typeof comanda == 'undefined' || comanda.length <= 0) { errore = true; msg = '#error#Errore durante l\'acquisizione dei dati'; }
            if (typeof comanda['error'] != 'undefined') { errore = true; msg = comanda['error']; }
            if (typeof comanda['ordini'] == 'undefined') { errore = true; msg = '#error#Errore durante l\'acquisizione dei dati'; }
            if (typeof comanda['attiva'] == 'undefined' ||  comanda['attiva']==0) { return false; }

            if(!errore){
            	ordini = comanda['ordini'];

				//resetta soci
				$('#numero-soci').val(comanda['numero_soci']);
				$('#numero-soci').data('val_prec', comanda['numero_soci']); //salva il valore precedente

				//resetta soci
				$('#annotazioni').val(comanda['annotazioni']);
				$('#annotazioni').data('val_prec', comanda['annotazioni']); //salva il valore precedente

				//resetta sconto
				$('#sconto-manuale').val(comanda['sconto_manuale']);
				$('#sconto-manuale').data('val_prec', comanda['sconto_manuale']); //salva il valore precedente

				//disattiva bottone salva
				$('#wrapper-right .salva').prop("disabled",true);
                $('#wrapper-right .salvachiudi').prop("disabled",true);

				$('#menu_name').off('change').on('change', function(){
					$('#menu_name').selectpicker('render');

					//disattiva bottone salva
					$('#wrapper-right .salva').prop("disabled",true);
                    $('#wrapper-right .salvachiudi').prop("disabled",true);

					var val = $('#menu_name').val();
					if(val==''){
						//svuota tabella lista piatti
						$('#lista-piatti tbody').html('<tr><td colspan="6" style="padding:10px 20px !important; text-align:center; ">Seleziona un Menù</td></tr>');
					}
					else{
						$.ajax({
					        type: 'POST',
					        url: "ajax/ottieni_lista_portate.ajax.php", 
					        dataType: 'json',
					        timeout: 20000,
					        data : {
					            nome_menu_order: val
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

					            if(!errore){
					            	$('#lista-piatti tbody').empty();
					            	$.each(lista_portate, function(index, portata) {
						           		$('#lista-piatti tbody').append('<tr '+( portata['nome_portata'].toLowerCase() == 'pane e coperto' ? 'class="highlighted"' : '')+'><td>'+portata['prezzo_finale']+'</td><td>'+portata['nome_portata']+'</td><td>'+portata['categoria']+'</td>'+
						           											'<td>'+( (portata['quantita_rimanente'] != null && portata['nome_portata'].toLowerCase() != 'pane e coperto')? portata['quantita_rimanente'] : '')+'</td>'+
						           											'<td>'+_trovaPiattiPrecedenti(portata['nome_portata'], ordini)+'</td>'+
					           												'<td>'+
											                                    '<div class="row">'+
											                                        '<div class="fl">'+
											                                            '<div class="btn-group" role="group" aria-label="...">'+
											                                                '<button type="button" class="btn btn-default btn-fixval" data-val="1">1</button>'+
											                                                '<button type="button" class="btn btn-default btn-fixval" data-val="2">2</button>'+
											                                                '<button type="button" class="btn btn-default btn-fixval" data-val="3">3</button>'+
											                                                '<button type="button" class="btn btn-default btn-fixval" data-val="4">4</button>'+
											                                                '<button type="button" class="btn btn-default btn-fixval" data-val="5">5</button>'+
											                                            '</div>'+
											                                        '</div>'+
											                                        '<div class="input-group">'+
											                                            '<span class="input-group-btn">'+
											                                                '<button type="button" class="btn btn-danger btn-op" data-op="-1">'+
											                                                    '<span class="glyphicon glyphicon-minus"></span>'+
											                                                '</button>'+
											                                            '</span>'+
											                                            '<input step="1" type="number" class="form-control small num"  value="">'+
											                                            '<span class="input-group-btn">'+
											                                                '<button type="button" class="btn btn-success btn-op" data-op="+1">'+
											                                                    '<span class="glyphicon glyphicon-plus"></span>'+
											                                                '</button>'+
											                                            '</span>'+
											                                        '</div>'+
											                                    '</div>'+
											                                '</td>'+
						           									   '</tr>');
						            });

						            //aggiorna il parziale attuale
						            clearTimeout(timer);
						            aggiornaTotParziale(tavolo,indice);

					            	$('#bottoniright').html('');
						            $('#bottoni-comanda-mod').html('<button type="button" style="margin-right:7px;" class="btn btn-success salvachiudi">Salva e chiudi <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button>'
						            +'<button type="button" style="margin-right:7px;" class="btn btn-success salva">Salva <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button>'
						            +'<button type="button" class="btn btn-danger annulla">Annulla</button></div>');
						            $('#bottonigetcom').html('<button type="button" style="margin-right:7px;" class="btn btn-success salvachiudi">Salva e chiudi <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button><button type="button" style="margin-right:7px;" class="btn btn-success salva">Salva <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button>'
                                                            +'<button style="margin-right:7px;" type="button" class="btn btn-danger annulla">Annulla</button>'
						            						+'<button style="margin-right:7px;" type="button" class="btn btn-danger chiudi-comanda">Chiudi Comanda</button>'
						            						+'<button style="margin-right:7px;" type="button" class="btn btn-info stampa-ricevuta"><i class="fa fa-print" aria-hidden="true"></i> Stampa Ricevuta</button>'
						            						+'<button style="margin-right:7px;" type="button" class="btn btn-default stampa-fisc"><i class="fa fa-newspaper-o" aria-hidden="true"></i> Stampa Ric. Fiscale</button>'
						            						);
                                    //$('#bottoni-comanda-mod1').html('<br/><button type="button" style="margin-right:7px;" class="btn btn-success salvachiudi">Salva e chiudi <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button><button type="button" style="margin-right:7px;" class="btn btn-success salva">Salva <i style="margin-left:5px;" class="fa fa-floppy-o" aria-hidden="true"></i></button>');

						            
						            $('#bottonigetcom button').off('click').on('click', function(){
						            	if($(this).hasClass('chiudi-comanda')) chiudiComanda(tavolo, indice);
						            	else if($(this).hasClass('stampa-fattura')) stampaFattura(tavolo, indice);
						            	else if($(this).hasClass('stampa-ricevuta')) stampaRicevuta(tavolo, indice);
						            	else if($(this).hasClass('conto-inviato')) contoInviato(tavolo, indice);
                                        else if($(this).hasClass('elimina-comanda')) eliminaComanda(tavolo, indice);
                                        else if($(this).hasClass('stampa-fisc')) stampaRicevutaFiscale(tavolo, indice);

						            });

						            //annulla
					           		$('#wrapper-right .salva').prop("disabled",false);
                                    $('#wrapper-right .salvachiudi').prop("disabled",false);
									$('#wrapper-right .salva').off('click').on('click', function(){
										salvaModificaComanda(tavolo, indice);
                                        /*clearTimeout(timer);
            							timer = setTimeout(function(){ 
            							  	refreshComanda(tavolo, indice);
            							}, 5000);*/ 
									});
                                    $('#wrapper-right .salvachiudi').off('click').on('click', function(){
										salvaModificaComandaChiudi(tavolo, indice);
									})

									$('#lista-piatti tbody input').off('keydown').on('keydown', function(){
										clearTimeout(timer);
										timer = setTimeout(function(){
										  	aggiornaTotParziale(tavolo, indice);
										}, 2000);
									});
									$('#lista-piatti tbody input').off('change').on('change', function(){
										clearTimeout(timer);
										timer = setTimeout(function(){
										  	aggiornaTotParziale(tavolo, indice);
										}, 2000);
									});

									$('#numero-soci').off('keydown').on('keydown', function(){
										clearTimeout(timer);
										timer = setTimeout(function(){
										  	aggiornaTotParziale(tavolo, indice);
										}, 2000);
									});

									$('#sconto-manuale').off('keydown').on('keydown', function(){
										clearTimeout(timer);
										timer = setTimeout(function(){
										  	aggiornaTotParziale(tavolo, indice);
										}, 2000);
									});

									$('#numero-soci').off('change').on('change', function(){
										clearTimeout(timer);
										timer = setTimeout(function(){
										  	aggiornaTotParziale(tavolo, indice);
										}, 2000);
									});

									$('#sconto-manuale').off('change').on('change', function(){
										clearTimeout(timer);
										timer = setTimeout(function(){
										  	aggiornaTotParziale(tavolo, indice);
										}, 2000);
									});


					            }
					            else notify_top(msg, 'Ottieni lista Portate'); 
						    },
					        error: function( jqXHR, textStatus, errorThrown ){
					        	console.log(jqXHR);
					            notify_top("#error#Errore durante l'operazione", 'Ottieni lista Portate');
					        }
					    });
					}
				});

				//imposta il valore della select
				$('#menu_name').val(comanda['menu']);
				$('#menu_name').selectpicker('render');
				$("#menu_name").change(); //call on change event

				//info tavolo
				$('.info-tavolo').text('Comanda: '+comanda['tavolo']+'/'+comanda['indice']);

				//info responsabile
				$('.info-responsabile span').text(comanda['responsabile']);

				//info zona
				$('.info-zona span').text(comanda['zona']);

				//svuota tabella lista piatti
				//$('#lista-piatti tbody').html('<tr><td colspan="6" style="padding:10px 20px !important; text-align:center; ">Seleziona un Menù</td></tr>');

				//mostra il tutto
				$('#selezione-tavoli').hide();
				$('#nuova_comanda').show();
            }
            else notify_top(msg, 'Ottieni info Comanda'); 
	    },
        error: function( jqXHR, textStatus, errorThrown ){
            notify_top("#error#Errore durante l'operazione", 'Ottieni info Comanda');
        }
    });
}

function salvaModificaComanda(tavolo, indice){

	//controlla i valori
	var has_positive = false; //stampa solo se sono state aggiunte portate
	var portate = new Array();
	$('#lista-piatti tbody tr').each(function(){
		var portata = $(this).find('td:nth-child(2)').text();
		var quantita = parseInt($(this).find('td:nth-child(6)').find('input').val());
		if(quantita>0) has_positive = true;
		if(!isNaN(quantita)){
			portate.push(new Array(portata, quantita));
		}
	});
	if(portate.length <=0){ 
		var num_soci = _ottieni_numero_soci();
		var sconto_manuale = _ottieni_sconto_manuale();
		var annotazioni = $('#annotazioni').val();

		if((num_soci === false || num_soci == $('#numero-soci').data('val_prec')) && (sconto_manuale === false || sconto_manuale == $('#sconto-manuale').data('val_prec')) && annotazioni == $('#annotazioni').data('val_prec')){
			notify_top('#error#Nessuna portata selezionata', 'Inserimento Nuova Comanda'); 
		}
		else{
			aggiornaNumSoci(tavolo,indice,num_soci, sconto_manuale, annotazioni);
		}
		return false;
	}
	var numero_soci = _ottieni_numero_soci();
	if(numero_soci === false){
		$('#numero-soci').addClass('input-error');
	                   		
		$('#numero-soci').keyup(function(){
			$('#numero-soci').removeClass('input-error');
		});
		return false;
	}

	var sconto_manuale = _ottieni_sconto_manuale();
	if(sconto_manuale === false){
		$('#sconto-manuale').addClass('input-error');
	                   		
		$('#sconto-manuale').keyup(function(){
			$('#sconto-manuale').removeClass('input-error');
		});
		return false;
	}

	var annotazioni = $('#annotazioni').val();
    var menu = $('#menu_name').val();

		$.ajax({
	        type: 'POST',
	        url: "ajax/gestione_comande.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'modifica_comanda',
	            //menu: menu,
	            numero_soci: numero_soci,
	            sconto_manuale: sconto_manuale,
	            tavolo: tavolo,
	            indice:indice,
	            ordini: portate,
	            annotazioni: annotazioni
	        },
	        beforeSend: function(){
	        	//disattiva bottone salva
				$('#wrapper-right .salva').prop("disabled",true);
                $('#wrapper-right .salvachiudi').prop("disabled",true);
				$('#modal-gest .modal-footer button').prop("disabled",true);
	        },
	        success: function(result){
	            var errore = false;
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore){
	            	notify_top(result, 'Inserimento Nuova Comanda'); 
	            	//attiva bottone salva
					$('#wrapper-right .salva').prop("disabled",false);
                    $('#wrapper-right .salvachiudi').prop("disabled",false);
					$('#modal-gest .modal-footer button').prop("disabled",false);
	            }
	            else{
	            	//disattiva bottone salva
					$('#wrapper-right .salva').prop("disabled",true);
                    $('#wrapper-right .salvachiudi').prop("disabled",true);
	            	//torna indietro 
	            	//annullaNuovaComanda();
	            	//aggiorna lista comande
	            	aggiornaListaComande();
	            	aggiornaPostiLiberi();
	            	//chiudi modal
					
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	        	//attiva bottone salva
	        	$('#modal-gest .modal-footer button').prop("disabled",false);
				$('#wrapper-right .salva').prop("disabled",false);
                $('#wrapper-right .salvachiudi').prop("disabled",false);
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Nuova Comanda'); 
	        }   

		});

	//avvia stampa
	if(has_positive) _stampa_su_stampante(tavolo, portate, indice);

	$('#modal-gest #modal-titolo').text( 'Stampa Comanda');
	$('#modal-gest .modal-body').html( '<span style="font-size:15px;">La comanda è stata stampata correttamente?</span>');
	$('#modal-gest .modal-footer').html( '<button class="btn btn-default ristampa" type="button"><i class="fa fa-print" aria-hidden="true"></i> Ristampa</button>'+
										'<button class="btn btn-success ok" type="button"><i class="fa fa-check" aria-hidden="true"></i> Si</button>');
	
	//listener bottoni
	$('#modal-gest .ristampa').off('click').on('click', function(){
		_stampa_su_stampante(tavolo, portate, indice);
	});
	$('#modal-gest .ok').off('click').on('click', function(){
        $('#modal-gest').modal('hide');
		refreshComanda(tavolo, indice);
		

	});

	//mostra modal
	if(has_positive) $('#modal-gest').modal('show');
    else $('#modal-gest .ok').click();
}

function salvaModificaComandaChiudi(tavolo, indice){

	//controlla i valori
	var has_positive = false; //stampa solo se sono state aggiunte portate
	var portate = new Array();
	$('#lista-piatti tbody tr').each(function(){
		var portata = $(this).find('td:nth-child(2)').text();
		var quantita = parseInt($(this).find('td:nth-child(6)').find('input').val());
		if(quantita>0) has_positive = true;
		if(!isNaN(quantita)){
			portate.push(new Array(portata, quantita));
		}
	});
	if(portate.length <=0){ 
		var num_soci = _ottieni_numero_soci();
		var sconto_manuale = _ottieni_sconto_manuale();
		var annotazioni = $('#annotazioni').val();

		if((num_soci === false || num_soci == $('#numero-soci').data('val_prec')) && (sconto_manuale === false || sconto_manuale == $('#sconto-manuale').data('val_prec')) && annotazioni == $('#annotazioni').data('val_prec')){
			notify_top('#error#Nessuna portata selezionata', 'Inserimento Nuova Comanda'); 
		}
		else{
			aggiornaNumSoci(tavolo,indice,num_soci, sconto_manuale, annotazioni);
		}
		return false;
	}
	var numero_soci = _ottieni_numero_soci();
	if(numero_soci === false){
		$('#numero-soci').addClass('input-error');
	                   		
		$('#numero-soci').keyup(function(){
			$('#numero-soci').removeClass('input-error');
		});
		return false;
	}

	var sconto_manuale = _ottieni_sconto_manuale();
	if(sconto_manuale === false){
		$('#sconto-manuale').addClass('input-error');
	                   		
		$('#sconto-manuale').keyup(function(){
			$('#sconto-manuale').removeClass('input-error');
		});
		return false;
	}

	var annotazioni = $('#annotazioni').val();

	//avvia stampa
	if(has_positive) _stampa_su_stampante(tavolo, portate, indice);

	$('#modal-gest #modal-titolo').text( 'Stampa Comanda');
	$('#modal-gest .modal-body').html( '<span style="font-size:15px;">La comanda è stata stampata correttamente?</span>');
	$('#modal-gest .modal-footer').html( '<button class="btn btn-default ristampa" type="button"><i class="fa fa-print" aria-hidden="true"></i> Ristampa</button>'+
										'<button class="btn btn-success ok" type="button"><i class="fa fa-check" aria-hidden="true"></i> Si</button>');
	
	//listener bottoni
	$('#modal-gest .ristampa').off('click').on('click', function(){
		_stampa_su_stampante(tavolo, portate, indice);
	});
	$('#modal-gest .ok').off('click').on('click', function(){
		//salva su db
		var menu = $('#menu_name').val();

		$.ajax({
	        type: 'POST',
	        url: "ajax/gestione_comande.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'modifica_comanda',
	            //menu: menu,
	            numero_soci: numero_soci,
	            sconto_manuale: sconto_manuale,
	            tavolo: tavolo,
	            indice:indice,
	            ordini: portate,
	            annotazioni: annotazioni
	        },
	        beforeSend: function(){
	        	//disattiva bottone salva
				$('#wrapper-right .salva').prop("disabled",true);
                $('#wrapper-right .salvachiudi').prop("disabled",true);
				$('#modal-gest .modal-footer button').prop("disabled",true);
	        },
	        success: function(result){
	            var errore = false;
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore){
	            	notify_top(result, 'Inserimento Nuova Comanda'); 
	            	//attiva bottone salva
					$('#wrapper-right .salva').prop("disabled",false);
                    $('#wrapper-right .salvachiudi').prop("disabled",false);
					$('#modal-gest .modal-footer button').prop("disabled",false);
	            }
	            else{
	            	//disattiva bottone salva
					$('#wrapper-right .salva').prop("disabled",true);
                    $('#wrapper-right .salvachiudi').prop("disabled",true);
	            	//torna indietro 
	            	annullaNuovaComanda();
	            	//aggiorna lista comande
	            	aggiornaListaComande();
	            	aggiornaPostiLiberi();
	            	//chiudi modal
					$('#modal-gest').modal('hide');
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	        	//attiva bottone salva
	        	$('#modal-gest .modal-footer button').prop("disabled",false);
				$('#wrapper-right .salva').prop("disabled",false);
                $('#wrapper-right .salvachiudi').prop("disabled",false);
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Nuova Comanda'); 
	        }   

		});

	});

	//mostra modal
	if(has_positive) $('#modal-gest').modal('show');
	else $('#modal-gest .ok').click();
}

function _ottieni_numero_soci(){
	var numero_soci = parseInt($('#numero-soci').val());
	if(isNaN(numero_soci)){
		if($('#numero-soci').val() != ''){
			return false;
		}
		else numero_soci = 0;
	}
	else if(numero_soci<0) return false;
	
	return numero_soci;
}

function _ottieni_sconto_manuale(){
	var sconto_manuale = parseFloat($('#sconto-manuale').val());
	if(isNaN(sconto_manuale)){
		if($('#sconto-manuale').val() != ''){
			return false;
		}
		else sconto_manuale = 0;
	}
	else if(sconto_manuale<0) return false;
	
	return sconto_manuale;
}

function _trovaPiattiPrecedenti(portata, ordini){
	var str = '';
	$.each(ordini, function(index, ordine) {
		if (ordine['portata'] == portata) { str = ordine['quantita']; return false; }
	});
	return str;
}

function aggiornaNumSoci(tavolo,indice,numero_soci, sconto_manuale, annotazioni){
	$.ajax({
        type: 'POST',
        url: "ajax/gestione_comande.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
            operazione: 'modifica_comanda_soci',
            //menu: menu,
            numero_soci: numero_soci,
            sconto_manuale: sconto_manuale,
            tavolo: tavolo,
            indice:indice,
            annotazioni: annotazioni
        },
        beforeSend: function(){
        	//disattiva bottone salva
			$('#wrapper-right .salva').prop("disabled",true);
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(errore){
            	notify_top(result, 'Aggiorna Comanda'); 
            	//attiva bottone salva
				$('#wrapper-right .salva').prop("disabled",false);
            }
            else{
            	//disattiva bottone salva
				$('#wrapper-right .salva').prop("disabled",true);
            	//torna indietro 
            	annullaNuovaComanda();
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	//attiva bottone salva
			$('#wrapper-right .salva').prop("disabled",false);
            notify_top("#error#Errore durante l'operazione", 'Aggiorna Comanda'); 
        }   

	});
}

function aggiornaListaComande(){
	$.ajax({
        type: 'POST',
        url: "ajax/ottieni_lista_comande.ajax.php",
        dataType: "json",
        timeout: 20000,
        data : {
        	order_col: order_col,
        	tab: open_tab
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
            	$('#lista-ordini tbody').empty();
            	$.each(lista_comande, function(index, comanda) {
	                $('#lista-ordini tbody').append('<tr data-attiva="'+comanda['attiva']+'" data-tavolo="'+comanda['tavolo']+'" data-indice="'+comanda['indice']+'">'+
                                                  '<td><i  style="position:relative;" class="fa fa-list-alt '+( comanda['attiva'] == 1 ? 'attiva' : '')+'" aria-hidden="true">'+
                                                  		( comanda['attiva'] == 1 && comanda['conto_inviato'] == 1 ? '<i class="fa fa-paper-plane absolute-icon" aria-hidden="true"></i>' : '')+
                                                        ( comanda['attiva'] == 0 ? ( comanda['pagata'] == 1 ? '<i class="fa fa-eur absolute-icon eur" aria-hidden="true"></i>' : '<i class="fa fa-times absolute-icon times" aria-hidden="true"></i>') : '')+

                                                  '</i></td>'+
                                                  '<td>'+comanda['responsabile']+'</td>'+
                                                  '<td>'+comanda['tavolo']+'/'+comanda['indice']+'</td>'+
                                                  '<td>'+(comanda['num_comanda'] == null ? '' : comanda['num_comanda']) +'</td>'+
                                          '</tr>');
	            });
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        }   

	});
}

function aggiornaPostiLiberi(){
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

            	$('#conteggio-posti .ptot span').text(posti_totali);
            	$('#conteggio-posti .pocc span').text(posti_occupati);
            	$('#conteggio-posti .plib span').text((posti_totali-posti_occupati) < 0 ? 0 : posti_totali-posti_occupati);
            }

        },
        error: function( jqXHR, textStatus, errorThrown ){
        }   

	});
}

/********************
 *  ELIMINA COMANDA *
 *******************/
function eliminaComanda(tavolo, indice){
    $('#modal-gest #modal-titolo').text( 'Elimina Comanda');
    $('#modal-gest .modal-body').html( '<span style="font-size:15px;">Sei sicuro di voler eliminare la comanda</span>');
    
    $('#modal-gest .modal-footer').html( '<button class="btn btn-danger annulla" type="button">Annulla</button>'+
										'<button class="btn btn-success pagato" type="button"><i class="fa fa-check" aria-hidden="true"></i> Si</button>');
                                        
   //listener bottoni
	$('#modal-gest .annulla').off('click').on('click', function(){
		$('#modal-gest').modal('hide');
	});
	$('#modal-gest .pagato').off('click').on('click', function(){
		$.ajax({
            type: 'POST',
            url:'ajax/elimina_comanda.ajax.php',
            dataType: 'text',
            timeout: 20000,
            data: {
                tavolo: tavolo,
                indice: indice
            },
            beforeSend: function(){
            	//disattiva bottone salva
    			$('#wrapper-right .salva').prop("disabled",true);
    			$('#wrapper-right .annulla').prop("disabled",true);
    			$('#wrapper-right .chiudi-comanda').prop("disabled",true);
    			$('#wrapper-right .stampa-ricevuta').prop("disabled",true);
    			$('#wrapper-right .stampa-fattura').prop("disabled",true);
    			$('#wrapper-right .stampa-fisc').prop("disabled",true);
            },
            success: function(result){
                console.log(result);
                var errore = false;
                if(stringStartsWith(result, '#error#')) errore=true;
    
                if(errore){
                	notify_top(result, 'Elimina Comanda'); 
                	//attiva bottone salva
    				$('#wrapper-right .salva').prop("disabled",false);
    				$('#wrapper-right .annulla').prop("disabled",false);
    				$('#wrapper-right .chiudi-comanda').prop("disabled",false);
    				$('#wrapper-right .stampa-ricevuta').prop("disabled",false);
    				$('#wrapper-right .stampa-fattura').prop("disabled",false);
    				$('#wrapper-right .stampa-fisc').prop("disabled",false);
                }
                else{
                	//disattiva bottone salva
    				$('#wrapper-right .salva').prop("disabled",true);
    				$('#wrapper-right .annulla').prop("disabled",true);
    				$('#wrapper-right .chiudi-comanda').prop("disabled",true);
    				$('#wrapper-right .stampa-ricevuta').prop("disabled",true);
    				$('#wrapper-right .stampa-fattura').prop("disabled",true);
    				$('#wrapper-right .stampa-fisc').prop("disabled",true);
                	//torna indietro 
                	annullaNuovaComanda();
                	$('#modal-gest').modal('hide');
                	aggiornaListaComande();
                	aggiornaPostiLiberi();
                	notify_top(result, 'Elimina Comanda'); 
                }
            },
            error: function( jqXHR, textStatus, errorThrown ){
            	//attiva bottone salva
    			$('#wrapper-right .salva').prop("disabled",false);
    			$('#wrapper-right .annulla').prop("disabled",false);
    			$('#wrapper-right .chiudi-comanda').prop("disabled",false);
    			$('#wrapper-right .stampa-ricevuta').prop("disabled",false);
    			$('#wrapper-right .stampa-fattura').prop("disabled",false);
    			$('#wrapper-right .stampa-fisc').prop("disabled",false);
                notify_top("#error#Errore durante l'operazione", 'Elimina Comanda'); 
            }
        });   
    });
    
    //mostra modal
	$('#modal-gest').modal('show');
}


/*******************
STAMPA
****************/
function chiudiComanda(tavolo, indice){

	$('#modal-gest #modal-titolo').text( 'Chiudi Comanda');
	$('#modal-gest .modal-body').html( '<span style="font-size:15px;">Il conto è stato pagato?</span>');
										
	$('#modal-gest .modal-footer').html( '<button class="btn btn-danger non-pagato" type="button"><i class="fa fa-times" aria-hidden="true"></i> No</button>'+
										'<button class="btn btn-success pagato" type="button"><i class="fa fa-check" aria-hidden="true"></i> Si</button>');
	
	//listener bottoni
	$('#modal-gest .non-pagato').off('click').on('click', function(){
		_chiudiComanda(tavolo, indice, 0);
	});
	$('#modal-gest .pagato').off('click').on('click', function(){
		_chiudiComanda(tavolo, indice, 1);
	});

	//mostra modal
	$('#modal-gest').modal('show');
	
}

function _chiudiComanda(tavolo, indice, pagato){

	$.ajax({
        type: 'POST',
        url: "ajax/gestione_comande.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
            operazione: 'chiudi_comanda',
            tavolo: tavolo,
            indice:indice,
            pagata: pagato
        },
        beforeSend: function(){
        	//disattiva bottone salva
			$('#wrapper-right .salva').prop("disabled",true);
			$('#wrapper-right .annulla').prop("disabled",true);
			$('#wrapper-right .chiudi-comanda').prop("disabled",true);
			$('#wrapper-right .stampa-ricevuta').prop("disabled",true);
			$('#wrapper-right .stampa-fattura').prop("disabled",true);
			$('#wrapper-right .stampa-fisc').prop("disabled",true);
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(errore){
            	notify_top(result, 'Chiudi Comanda'); 
            	//attiva bottone salva
				$('#wrapper-right .salva').prop("disabled",false);
				$('#wrapper-right .annulla').prop("disabled",false);
				$('#wrapper-right .chiudi-comanda').prop("disabled",false);
				$('#wrapper-right .stampa-ricevuta').prop("disabled",false);
				$('#wrapper-right .stampa-fattura').prop("disabled",false);
				$('#wrapper-right .stampa-fisc').prop("disabled",false);
            }
            else{
            	//disattiva bottone salva
				$('#wrapper-right .salva').prop("disabled",true);
				$('#wrapper-right .annulla').prop("disabled",true);
				$('#wrapper-right .chiudi-comanda').prop("disabled",true);
				$('#wrapper-right .stampa-ricevuta').prop("disabled",true);
				$('#wrapper-right .stampa-fattura').prop("disabled",true);
				$('#wrapper-right .stampa-fisc').prop("disabled",true);
            	//torna indietro 
            	annullaNuovaComanda();
            	$('#modal-gest').modal('hide');
            	aggiornaListaComande();
            	aggiornaPostiLiberi();
            	notify_top(result, 'Chiudi Comanda'); 
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	//attiva bottone salva
			$('#wrapper-right .salva').prop("disabled",false);
			$('#wrapper-right .annulla').prop("disabled",false);
			$('#wrapper-right .chiudi-comanda').prop("disabled",false);
			$('#wrapper-right .stampa-ricevuta').prop("disabled",false);
			$('#wrapper-right .stampa-fattura').prop("disabled",false);
			$('#wrapper-right .stampa-fisc').prop("disabled",false);
            notify_top("#error#Errore durante l'operazione", 'Chiudi Comanda'); 
        }   

	});
}

function visualizzaComandaChiusa(tavolo,indice){
	$('#modal-gest #modal-titolo').html( 'Riepilogo Comanda <b>'+tavolo+'/'+indice+'</b>');
	$('#modal-gest .modal-body').html( '<div class="com-top-info">'
											+'<button type="button" class="btn btn-default">Numero Soci: <span id="num-soci-riep"></span></button>'
											+'<button type="button" class="btn btn-default">Responsabile: <span id="resp-riep"></span></button>'
											+'<button type="button" class="btn btn-danger butt-pagata">Pagata: <span id="pagata-riep"></span></button>'
									  +'</div>'
									  +'<div id="ann-info">'
									  +'</div>'
									  +'<div class="info-tbl-piatti">'
											+'<table style="margin-top:15px" class="table table-striped table-bordered table-hover table-selected">'
												+'<thead>'
													+'<tr><th>Portata</th><th>Quantità</th><th>Prezzo</th><th>Totale</th></tr>'
												+'</thead>'
												+'<tbody>'
													+'<tr><td colspan="4">Nessuna Portata disponibile</td></tr>'
												+'</tbody>'
											+'</table>'
									  +'</div>'
									  +'<div class="info-totale-chiusa">'
											+'<p>Totale prodotti: <span class="tot-chiusa"></span> €</p>'
											+'<p><span class="num-soci-chiusa"></span> soci con sconto del 10%</p>'
											+'<p>Sconto: <span class="sconto-manuale"></span> €</p>'
											+'<p>Totale Comanda: <span class="tot-chiusa-scont"></span> €</p>'
									  +'</div>');
	$('#modal-gest .modal-footer').html( '<button style="margin-right:7px;" type="button" class="btn btn-info stampa-ricevuta"><i class="fa fa-print" aria-hidden="true"></i> Stampa Ricevuta</button>'+
										'<button style="margin-right:7px;" type="button" class="btn btn-success riapri-comanda"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Riapri Comanda</button>'+
										'<button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>');
	
	//listener bottoni
	$('#modal-gest .modal-footer button').off('click').on('click', function(){
    	if($(this).hasClass('stampa-fattura')) stampaFattura(tavolo, indice);
    	else if($(this).hasClass('stampa-ricevuta')) stampaRicevuta(tavolo, indice);
    	else if($(this).hasClass('stampa-fisc')) stampaRicevutaFiscale(tavolo, indice);
    	else if($(this).hasClass('riapri-comanda')) riapriComanda(tavolo, indice);
    });

	$.ajax({
        type: 'POST',
        url: "ajax/ottieni_info_comanda_chiusa.ajax.php",
        dataType: "json",
        timeout: 20000,
        data : {
            tavolo: tavolo,
            indice:indice
        },
        beforeSend: function(){
        },
        success: function(result){
            var msg = '';
            var errore = false;

            if (typeof result['error'] != 'undefined') { errore = true; msg = result['error']; }
            else if (typeof result['ordini'] == 'undefined') { errore = true; msg = '#error#Errore durante l\'acquisizione dei dati'; }

            if(errore){
            	notify_top(msg, 'Ottieni info Comanda'); 
            }
            else{
            	$('#num-soci-riep').text(result['numero_soci']);
            	$('#resp-riep').text(result['responsabile']);
            	$('#pagata-riep').html(result['pagata'] == 1 ? '<i class="fa fa-check" aria-hidden="true"></i>' : '<i class="fa fa-times" aria-hidden="true"></i>');

            	if(typeof result['annotazioni'] != 'undefined' && result['annotazioni'] != '' && result['annotazioni'] != null) $('#ann-info').html('<p style="margin: 10px 0 0 5px">Annotazioni: '+result['annotazioni']+'</p>');

            	$('.butt-pagata').removeClass('btn-danger').removeClass('btn-success');
            	if(result['pagata'] == 1) $('.butt-pagata').addClass('btn-success');
            	else $('.butt-pagata').addClass('btn-danger');

	            ordini = result['ordini'];
	            if(ordini.length>0) $('.info-tbl-piatti table tbody').empty();
	            $.each(ordini, function(index, ordine) {
	                $('.info-tbl-piatti table tbody').append('<tr><td>'+ordine['portata']+'</td><td>'+ordine['quantita']+'</td><td>'+ordine['prezzo']+' €</td><td>'+ordine['prezzo_quant']+' €</td></tr>');
	            });

	            $('.info-totale-chiusa .sconto-manuale').text(result['sconto_manuale']);
	            $('.info-totale-chiusa .tot-chiusa').text(result['totale']);
	            $('.info-totale-chiusa .tot-chiusa-scont').text(result['totale_scontato']);
	            $('.info-totale-chiusa .num-soci-chiusa').text(result['numero_soci']);

            	
            	//mostra modal
            	$('#modal-gest').modal('show');
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	console.log(jqXHR);
            notify_top("#error#Errore durante l'operazione", 'Ottieni info Comanda'); 
        }   

	});
}


function stampaRicevuta(tavolo,indice){

	$.post('stampa/stampa_ricevuta.php', {
            tavolo: tavolo,
        	indice: indice
        }, function(result) {
        	newpage = result;
        	/*myWindow = window.open('javascript: document.write(window.opener.newpage);', '_blank','height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
        	myWindow.document.close();
        	contoInviato(tavolo, indice);
        	myWindow.print();
        	myWindow.close();*/
        	var myWindow = window.open("", "myWindow", 'height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
        	myWindow.document.write(newpage);
        	myWindow.document.close();
        	contoInviato(tavolo, indice);
        	myWindow.print();
        	myWindow.close();    
    });

	
	//avvia stampa
	/*$.ajax({
        type: 'POST',
        url: "stampa/stampa_ricevuta.php",
        dataType: "text",
        timeout: 60000,
        data : {
            tavolo: tavolo,
        	indice: indice
        },
        beforeSend: function(){
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;
        	newpage = result;
        	//myWindow = window.open('javascript: document.write(window.opener.newpage);', '_blank','height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
        	var myWindow = window.open("", "myWindow", 'height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
        	myWindow.document.write(newpage);
        	myWindow.document.close();
        	contoInviato(tavolo, indice);
        	myWindow.print();
        	myWindow.close();            
        	if(errore){
            	notify_top(result, 'Stampa Ricevuta'); 
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	notify_top('#error#Errore durante la stampa', 'Stampa Ricevuta'); 
        }   

	});*/
}


function stampaFattura(tavolo,indice){

	$.post('stampa/stampa_fattura.php', {
            tavolo: tavolo,
        	indice: indice
        }, function(result) {
        	newpage = result;
        	myWindow = window.open('javascript: document.write(window.opener.newpage);', 'popUpWindow','height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
        	myWindow.document.close();
        	myWindow.print();
        	myWindow.close();
    });

	
	
	
	/*//avvia stampa
	$.ajax({
        type: 'POST',
        url: "ajax/stampa_ricevuta.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
            tavolo: tavolo,
        	indice: indice
        },
        beforeSend: function(){
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(errore){
            	notify_top(result, 'Stampa Fattura'); 
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	notify_top('#error#Errore durante la stampa', 'Stampa Fattura'); 
        }   

	});*/
}

function riapriComanda(tavolo, indice){
	$.ajax({
        type: 'POST',
        url: "ajax/riapri_comanda.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
            tavolo: tavolo,
        	indice: indice
        },
        beforeSend: function(){
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(errore){
            	notify_top(result, 'Riapri Comanda'); 
            }else{
            	aggiornaListaComande();
            	aggiornaPostiLiberi();
            	//chiudi modal
				$('#modal-gest').modal('hide');
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	notify_top('#error#Errore durante la riapertura', 'Riapri Comanda'); 
        }   

	});
}

function aggiornaTotParziale(tavolo,indice){

	//crea array
	var num_coperti_nuovi = 0;
	var nuovi_ordini = new Array();
	$.each($('#lista-piatti tbody tr'), function(index){
		var quant = $(this).find('td:nth-child(6) input').val();
		if(quant == '' || quant == 0) return true; //skip
		nuovi_ordini.push(new Array($(this).find('td:nth-child(2)').text(), quant ));
		if($(this).find('td:nth-child(2)').text().toLowerCase() == 'pane e coperto'){ num_coperti_nuovi = quant;}
	});

	$.ajax({
        type: 'POST',
        url: "ajax/ottieni_tot_parziale.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
            tavolo: tavolo,
        	indice: indice,
        	nuovi_ordini: nuovi_ordini,
        	num_coperti: num_coperti_nuovi,
        	num_soci: ($('#numero-soci').val() == '' ? 0 : $('#numero-soci').val()),
        	sconto_manuale: ($('#sconto-manuale').val() == '' ? 0 : $('#sconto-manuale').val()),
        	nuovo:0
        },
        beforeSend: function(){
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(errore){
            	notify_top(result, 'Aggiornamento Totale Parziale'); 
            }else{
            	//aggiorna i campi
            	var tmp = result.replace(/ /g,'').split("/");
            	$('#totale-parziale').val(tmp[0]);
            	$('#totale-parziale-persona').val(tmp[1]);
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	notify_top('#error#Errore durante l\'aggiornamento', 'Aggiornamento Totale Parziale'); 
        }   

	});
}

function aggiornaTotParzialeNuovo(tavolo){

	//crea array
	var nuovi_ordini = new Array();
	var has_coperti = false;
	var num_coperti = 0;
	$.each($('#lista-piatti tbody tr'), function(index){
		var quant = $(this).find('td:nth-child(6) input').val();
		if(quant == '' || quant == 0) return true; //skip
		nuovi_ordini.push(new Array($(this).find('td:nth-child(2)').text(), quant ));
		if($(this).find('td:nth-child(2)').text().toLowerCase() == 'pane e coperto'){ num_coperti = quant; has_coperti = true;}
	});
	if(!has_coperti) return false;

	$.ajax({
        type: 'POST',
        url: "ajax/ottieni_tot_parziale.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
            tavolo: tavolo,
            num_coperti: num_coperti,
            menu: $('#menu_name').val(),
        	nuovi_ordini: nuovi_ordini,
        	num_soci: ($('#numero-soci').val() == '' ? 0 : $('#numero-soci').val()),
        	sconto_manuale: ($('#sconto-manuale').val() == '' ? 0 : $('#sconto-manuale').val()),
        	nuovo: 1
        },
        beforeSend: function(){
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            if(errore){
            	notify_top(result, 'Aggiornamento Totale Parziale'); 
            }else{
            	//aggiorna i campi
            	var tmp = result.replace(/ /g,'').split("/");
            	$('#totale-parziale').val(tmp[0]);
            	$('#totale-parziale-persona').val(tmp[1]);

            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	notify_top('#error#Errore durante l\'aggiornamento', 'Aggiornamento Totale Parziale'); 
        }   

	});
}

function contoInviato(tavolo, indice){
	$.ajax({
        type: 'POST',
        url: "ajax/gestione_comande.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
        	tavolo: tavolo,
        	indice:indice,
        	operazione: 'invia-conto'
        },
        beforeSend: function(){
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;

            notify_top(result, 'Invia Conto');
            if(!errore){
            	annullaNuovaComanda();
            	aggiornaListaComande();
            }
        },
        error: function( jqXHR, textStatus, errorThrown ){
        	notify_top('#error#Errore durante l\'operazione', 'Invia Conto');
        }   

	});
}


function stampaRicevutaFiscale(tavolo,indice){
	$('#modal-gest #modal-titolo').text( 'Stampa Ricevuta Fiscale');
	$('#modal-gest .modal-body').html( '<span style="font-size:15px;">La ricevuta fiscale è stata stampata correttamente?</span>');
	$('#modal-gest .modal-footer').html( '<button class="btn btn-default ristampa" type="button"><i class="fa fa-print" aria-hidden="true"></i> Ristampa</button>'+
										'<button class="btn btn-success ok" type="button"><i class="fa fa-check" aria-hidden="true"></i> Si</button>');
	var success=false;
	$.ajax({
        type: 'POST',
        url: "ajax/gestisci_ricevute_fiscali.ajax.php",
        dataType: "text",
        timeout: 20000,
        data : {
        	tavolo: tavolo,
        	indice:indice,
        	operazione: 'stampa-ricevuta-fiscale'
        },
        beforeSend: function(){
        	$('#modal-gest .modal-footer button').prop("disabled",false);
        },
        success: function(result){
            var errore = false;
            if(stringStartsWith(result, '#error#')) errore=true;
            if(!errore){
            	stampaFiscale(tavolo,indice);
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
		stampaFiscale(tavolo, indice);
	});
	$('#modal-gest .ok').off('click').on('click', function(){
		$('#modal-gest').modal('hide');
	});
}

function stampaFiscale(tavolo, indice){
	$.post('stampa/stampa_ricevuta_new.php', {
            tavolo: tavolo,
        	indice: indice
        }, function(result) {
        	newpage = result;
        	/*myWindow = window.open('javascript: document.write(window.opener.newpage);', '_blank','height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
        	myWindow.document.close();
        	contoInviato(tavolo, indice);
        	myWindow.print();
        	myWindow.close();*/
        	var myWindow = window.open("", "myWindow3", 'height=500, width=800, left=300, top=100, resizable=yes, scrollbars=yes, toolbar=yes, menubar=no, location=no, directories=no, status=yes');
        	myWindow.document.write(newpage);
        	myWindow.document.close();
        	myWindow.print();
        	myWindow.close();    
    });
}