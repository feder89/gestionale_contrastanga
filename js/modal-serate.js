$(document).ready(function(){
	var table = $('#editable-sample');

	/* on click on table row, open modal to add menu */
	table.on('click', 'tbody tr td', function(){
		/* skip columns with edit /modify functions */
        if($(this).index() == 4 || $(this).index() == 5 || $(this).index() == 6){ return 0; }
		
		/* check that we are not editing */
		var modifing = false;
		var nome = undefined;
		var data = undefined;
		$(this).parent('tr').find('td').each(function(){
			if($(this).text() == 'Aggiorna' || $(this).text() == 'Salva'){ modifing = true; }
			else if($(this).index() == 0){ nome = $(this).text(); }
			else if($(this).index() == 1){ data = $(this).text(); }
		});
		if(modifing){ return 0; }
		if (typeof nome === 'undefined') { console.log("impossibile ottenere il nome della serata"); return 0;}
		if (typeof data === 'undefined') { console.log("impossibile ottenere la data della serata "+nome); return 0;}

		$('#modal-gest #modal-titolo').text( 'Gestisci i menù della serata "'+ nome+'" del '+data);
        $('#modal-gest .clearfix #bottone-continua-responsabili').remove();

		/* ottieni lista menu per questa serata */
		var errore = false;
		var msg = '';
		var menus = new Array();
        $.ajax({
            type: 'POST',
            url: "ajax/ottieni_lista_menu.ajax.php",
            dataType: "json",
            timeout: 20000,
            async: false,
            data : {
            	'data_serata': data
            },
            beforeSend: function(){
            },
            success: function(result){

                $.each(result, function(index, menu) {
                    if (typeof menu['error'] !== 'undefined') { errore = true; msg = menu['error']; return false; }
                	menus.push( menu );
                });

            },
            error: function( jqXHR, textStatus, errorThrown ){
                notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Menù della Serata'); 
                errore = true;
            }
        });

		if(errore){
            if(msg != '') notify_top(msg, 'Recupero lista Menù della Serata');
        }
        else{
        	/* load new rows into modal table */
        	EditableTableModal.clear();
        	EditableTableModal.addRows(menus);
        	EditableTableModal.setDate(data);
        	//$('#modal-gest #modal-gest-table tbody').html(html);

        	/* open modal */
			$('#modal-gest').modal('toggle');
        }
	});
});