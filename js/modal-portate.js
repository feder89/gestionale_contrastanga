$(document).ready(function(){
	var table = $('#editable-sample');

	/* on click on table row, open modal to add menu */
	table.on('click', 'tbody tr td', function(){
		/* skip columns with edit /modify functions */
		if($(this).index() == 5 || $(this).index() == 6){ return 0; }
		
		/* check that we are not editing */
		var modifing = false;
		var nome = undefined;
		$(this).parent('tr').find('td').each(function(){
			if($(this).text() == 'Aggiorna' || $(this).text() == 'Salva'){ modifing = true; }
			else if($(this).index() == 0){ nome = $(this).text(); }
		});
		if(modifing){ return 0; }
		if (typeof nome === 'undefined') { console.log("impossibile ottenere il nome della portata"); return 0;}

		$('#modal-gest #modal-titolo').text( 'Gestisci le materie prime della portata "'+nome+'"');

		/* ottieni lista portate per questa serata */
		var errore = false;
		var msg = '';
		var materie = new Array();
        $.ajax({
            type: 'POST',
            url: "ajax/ottieni_lista_materieprime.ajax.php",
            dataType: "json",
            timeout: 20000,
            async: false,
            data : {
            	'nome_portata': nome
            },
            beforeSend: function(){
            },
            success: function(result){

                $.each(result, function(index, materia) {
                    if (typeof materia['error'] !== 'undefined') { errore = true; msg = materia['error']; return false; }
                	materie.push( materia );
                });

            },
            error: function( jqXHR, textStatus, errorThrown ){
                notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Materie prime della Portata'); 
                errore = true;
            }
        });

		if(errore){
            if(msg != '') notify_top(msg, 'Recupero lista Materie prime della Portata');
        }
        else{
        	/* load new rows into modal table */
        	EditableTableModal.clear();
        	EditableTableModal.addRows(materie);
        	EditableTableModal.setPortataname(nome);

        	/* open modal */
			$('#modal-gest').modal('toggle');

            /*
            $('#modal-gest').on('shown.bs.modal', function () {
                EditableTableModal.adjustColumns();
            });
            */


        }
	});
});