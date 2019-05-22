$(document).ready(function(){
	var table = $('#editable-sample');
    var nome = undefined;

	/* on click on table row, open modal to add menu */
	table.on('click', 'tbody tr td', function(){
		/* skip columns with edit /modify functions */
		if($(this).index() == 5 || $(this).index() == 6){
            return 0; 
        }else if($(this).index() != 5 && $(this).index() != 6){
            nome = $(this).parent().find('td').eq(0).html();
        }     
         
        
		
		/* check that we are not editing */
		var modifing = false;
		
		$(this).parent('tr').find('td').each(function(){
			if($(this).text() == 'Aggiorna' || $(this).text() == 'Salva'){ modifing = true; }
			else if($(this).index() == 0){ nome = $(this).text(); }
		});
		if(modifing){ return 0; }
		if (typeof nome === 'undefined') { console.log("impossibile ottenere il nome del menu"); return 0;}

		$('#modal-gest #modal-titolo').text( 'Gestisci le portate del menù "'+nome+'"');

		/* ottieni lista portate per questa serata */
		var errore = false;
		var msg = '';
        $('#modal-gest-table tr').remove();
		var portate = new Array();
        var presenti= new Array();
        $.ajax({
            type: 'POST',
            url: "ajax/ottieni_lista_portate.ajax.php",
            dataType: "json",
            timeout: 20000,
            async: false,
            data : {
                nome_menu: nome	
            },
            beforeSend: function(){
            },
            success: function(result){

                $.each(result, function(index, portata) {
                    if (typeof portata['error'] !== 'undefined') { errore = true; msg = portata['error']; return false; }
                	presenti.push( portata );
                });

            },
            error: function( jqXHR, textStatus, errorThrown ){
                notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Portate del Menù'); 
                errore = true;
            }
        });
            
    
        $.ajax({
            type: 'POST',
            url: "ajax/ottieni_lisata_portate_menu.ajax.php",
            dataType: "json",
            timeout: 20000,
            async: false,
            data : {
                nome_menu: nome	
            },
            beforeSend: function(){
            },
            success: function(result){

                $.each(result, function(index, portata) {
                    if (typeof portata['error'] !== 'undefined') { errore = true; msg = portata['error']; return false; }
                	portate.push( portata );
                });

            },
            error: function( jqXHR, textStatus, errorThrown ){
                notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Portate del Menù'); 
                errore = true;
            }
        });

		if(errore){
            if(msg != '') notify_top(msg, 'Recupero lista Portate del Menù');
        }
        else{
        	/* load new rows into modal table */
        	//EditableTableModal.clear();
            $.each(portate, function(index, value){
                $('#modal-gest-table').append('<tr><td><input type="checkbox"/></td><td>'+value['nome_portata']+'</td></tr>');
                /*$('#modal-gest-table').append('<tr><td><input type="checkbox"/></td><td>'+value['nome_portata']+'</td></tr>');
                if($.inArray(value['nome_portata'], presenti)!=-1){
                    $('#modal-gest-table').append('<tr class="selected-green"><td><input type="checkbox"/></td><td>'+value['nome_portata']+'</td></tr>');
                    //$('tr').addClass('selected-green');   
                }else{
                    $('#modal-gest-table').append('<tr><td><input type="checkbox"/></td><td>'+value['nome_portata']+'</td></tr>');
                }  */
            });
        	
        	EditableTableModal.setMenuname(nome);

        	/* open modal */
			$('#modal-gest').modal('toggle');

            /*
            $('#modal-gest').on('shown.bs.modal', function () {
                EditableTableModal.adjustColumns();
            });
            */


        }
        $('#modal-gest-table tr').each(function(index, el){
            var piatto = $(this).find("td").eq(1).html();
            $.each(presenti, function(key, value){
                var p=value['nome_portata'];
                if(piatto==p){
                    $(el).addClass('selected-green');
                }
            });
            //console.log(piatto);
            
        });
	});
    $('#button-close').on('click', function(){
        window.location.href ='gestione_menu.php';
    });
    $('#button-close-up').on('click', function(){
        window.location.href ='gestione_menu.php';
    });
    $('#button-salva').on('click', function(e){
        var piatti= new Array();
		$("#modal-gest-table tbody tr").each(function( index ) {
		    var checkbox = $(this).find('input[type="checkbox"]');
		    if(checkbox.attr("checked")) piatti.push($(this).find("td:nth-child(2)").text());
		});
        if(piatti.length <=0) return false;
        $.ajax({
            type: 'POST',
            url: 'ajax/composizione_menu.ajax.php',
            dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'inserisci',
	            piatti: piatti,
                menu: nome
	        },
            beforeSend: function(){
	        },
	        success: function(result){
	            var errore = false;
                //console.log(result);
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore) notify_top(result, 'Inserimento Composizione Menù'); 
	            else {
	            	//mostra responsabile nella tabella
	            	$("#modal-gest-table tbody tr").each(function( index ) {
	            		$(this).find('input[type="checkbox"]').attr('checked', false);
					    var piatto = $(this).find('td:nth-child(2)').text();
					    if($.inArray( piatto, piatti) != -1){
					    	$(this).addClass('selected-green');
					    	//$(this).find('td:nth-child(4)').text(select.val());
					    }
					});
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Composizione Menù'); 
	        }   

		});
      
	    e.preventDefault();
        return false;
        window.location.href ='gestione_menu.php';
  	});
    
    $('#button-rimuovi').on('click', function(e){
        var piatti= new Array();
        $('#modal-gest-table tbody tr').each(function(index){
            var checkbox_r=$(this).find('input[type="checkbox"]');
            if(checkbox_r.attr("checked")) piatti.push($(this).find('td:nth-child(2)').text());
        });
        if(piatti.length<=0) return false;
        
        $.ajax({
            type: 'POST',
            url: 'ajax/composizione_menu.ajax.php',
            dataType: "text",
	        timeout: 20000,
            data: {
                operazione: 'rimuovi',
                piatti: piatti,
                menu: nome
            },
            beforeSend: function(){
            },
            success: function(result){
                var errore = false;
                if(stringStartsWith(result, '#error#')) errore=true;
                if(errore) notify_top(result, 'Inserimento Composizione Menù'); 
                else{
                    $('#modal-gest-table tbody tr').each(function(index){
                        $(this).find('input[type="checkbox"]').attr('checked', false);
                        var piatto = $(this).find('td:nth-child(2)').text();
                        if($.inArray(piatto, piatti) != -1){
                            $(this).removeClass('selected-green');
                        }
                    });
                }
            },
            error: function( jqXHR, textStatus, errorThrown ){
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Composizione Menù'); 
	        }
        });
        
        e.preventDefault();
        return false;
        window.location.href ='gestione_menu.php';    
    });
});

function stringStartsWith (string, prefix) {
    return string.substring(0, prefix.length) == prefix;
}
