var EditableTable = function () {

    var oTable;
    return {

        //main function to initiate the module
        init: function () {

            $('#bottone-continua').on('click', function(e){

                var today = new Date();
                var dd = ( today.getDate() <= 9 ? '0'+today.getDate() : today.getDate());
                var mm = ( today.getMonth()+1 <= 9 ? '0'+(today.getMonth()+1) : today.getMonth()+1); //January is 0!
                var yyyy = today.getFullYear();
                var data_oggi = yyyy+'-'+mm+'-'+dd;

                var quantita = $('#quantita-default');
                    
                if(quantita.val() == '' || isNaN(quantita.val()) || quantita.val()<0){
                    quantita.addClass('input-error');
                    
                    quantita.keyup(function(){
                        quantita.removeClass('input-error');
                    });
                    
                    return false;
                }

                //inserisci quelle di default e fai il controllo
                $.ajax({
                    type: 'POST',
                    url: "ajax/gestisci_piatti_serata.ajax.php",
                    dataType: "text",
                    timeout: 20000,
                    data : {
                        operazione: 'imposta-default-e-controlla',
                        default: quantita.val(),
                        data_oggi: data_oggi
                    },
                    beforeSend: function(){
                    },
                    success: function(result){
                        var errore = false;
                        if(stringStartsWith(result, '#error#')) errore=true;

                        if(!errore){ 
                            window.location.href = "index.php";
                            return false;
                        }
                        else notify_top(result, 'Operazione di inserimento e controllo');

                    },
                    error: function( jqXHR, textStatus, errorThrown ){
                        notify_top("#error#Errore durante l'operazione", 'Operazione di inserimento e controllo'); 
                    }
                });

                e.preventDefault();
                return false;
            });

            function restoreRow(oTable, nRow) {
                var aData = oTable.fnGetData(nRow);
                var jqTds = $('>td', nRow);

                for (var i = 0, iLen = jqTds.length; i < iLen; i++) {
                    oTable.fnUpdate(aData[i], nRow, i, false);
                }

                oTable.fnDraw();
            }

            function editRow(oTable, nRow, new_row) {
                var aData = oTable.fnGetData(nRow);
                var jqTds = $('>td', nRow);

                //console.log(materia);  
                var today = new Date();
                var dd = ( today.getDate() <= 9 ? '0'+today.getDate() : today.getDate());
                var mm = ( today.getMonth()+1 <= 9 ? '0'+(today.getMonth()+1) : today.getMonth()+1); //January is 0!
                var yyyy = today.getFullYear(); 

                /*get menu list */
                var select_html = undefined;
                $.ajax({
                    type: 'POST',
                    url: "ajax/ottieni_lista_portate.ajax.php",
                    dataType: "json",
                    timeout: 20000,
                    data : {
                        data_serata_distinct : yyyy+'-'+mm+'-'+dd
                    },
                    beforeSend: function(){
                    },
                    success: function(result){
                        var errore = false;
                        var msg = '';
                        select_html = '<select data-live-search="true" class="selectpicker" data-size="10" id="portata_name" name="portata_name">';
                        select_html += '<option value=""></option>';

                        $.each(result, function(index, portata) {
                            if (typeof portata['error'] !== 'undefined') { errore = true; msg = portata['error']; return false; }
                            select_html += '<option value="'+portata['nome_portata']+'">'+portata['nome_portata']+'</option>';
                        });

                        select_html += '</select>';

                        if(errore){
                            notify_top(msg, 'Recupero lista Portate');
                            oTable.fnDeleteRow(nRow);
                        }
                        else{
                            jqTds[0].innerHTML = select_html;
                            jqTds[1].innerHTML = '<input step="1" min="0" type="number" class="form-control small num"  value="' + aData[1] + '">';
                            jqTds[2].innerHTML = '<input disabled type="text" class="form-control small" value="' + aData[2] + '">';
                            jqTds[3].innerHTML = '<input disabled type="text" class="form-control small" value="' + aData[3] + '">';
                            jqTds[4].innerHTML = '<a class="edit" href="">Salva</a>';
                            jqTds[5].innerHTML = '<a class="cancel" href="">Annulla</a>';

                            $('#portata_name').selectpicker({
                              noneSelectedText:'Nessuna Portata selezionata',
                            });
                            
                            if(new_row) nRow.className = nRow.className + " nuovariga";   
                        }

                        //if(stringStartsWith(result, '#error#')) errore=true;
                        //notify_top(result, 'Recupero lista Menù');
                    },
                    error: function( jqXHR, textStatus, errorThrown ){
                        notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Portate'); 
                        oTable.fnDeleteRow(nRow);
                    }
                });   
            }

            function modifyRow(oTable, nRow) {
                var aData = oTable.fnGetData(nRow);
                var jqTds = $('>td', nRow);
                jqTds[0].innerHTML = '<input disabled type="text" class="form-control small" value="' + aData[0] + '">';
                jqTds[1].innerHTML = '<input step="1" min="0" type="number" class="form-control small num"  value="' + aData[1] + '">';
                jqTds[2].innerHTML = '<input disabled type="text" class="form-control small" value="' + aData[2] + '">';
                jqTds[3].innerHTML = '<input disabled type="text" class="form-control small" value="' + aData[3] + '">';
                jqTds[4].innerHTML = '<a class="edit" href="">Aggiorna</a>';  /* se cambiamo Aggiorna qui, cambia anche dentro modal-serate.js */
                jqTds[5].innerHTML = '<a class="cancel" href="">Annulla</a>';

            }

            function saveRow(oTable, nRow, hasInput = false) {
                $(nRow).removeClass('nuovariga');

                var jqInputs = $('input', nRow);
                var select = $('select', nRow);
                oTable.fnUpdate((hasInput ? jqInputs[0].value : select[0].value), nRow, 0, false);
                oTable.fnUpdate(jqInputs[1].value, nRow, 1, false);
                oTable.fnUpdate(jqInputs[2].value, nRow, 2, false);
                oTable.fnUpdate(jqInputs[3].value, nRow, 3, false);
                oTable.fnUpdate('<a class="edit" href="">Modifica</a>', nRow, 4, false);
                oTable.fnUpdate('<a class="delete" href="">Cancella</a>', nRow,5, false);
                oTable.fnDraw();
            }

            oTable = $('#editable-sample').DataTable({
                "aLengthMenu": [
                    [5, 15, 20, -1],
                    [5, 15, 20, "Tutti"] // change per page values here
                ],
                // set the initial value
                "iDisplayLength": 10,
                "bPaginate": false,
                "sDom": "<'row'<'col-lg-6'l><'col-lg-6'f>r>t<'row'<'col-lg-6'i><'col-lg-6'p>>",
                "sPaginationType": "bootstrap",
                "oLanguage": {
                    "sLengthMenu": "_MENU_ elementi per pagina",
                    "oPaginate": {
                        "sPrevious": "Precedente",
                        "sNext": "Successiva"
                    }
                },
                "aoColumnDefs": [
                    {
                        'aTargets': [0]
                    },
                    {
                        'aTargets': [1],
                        "bSearchable": false
                    },
                    {
                        "bSearchable": false,
                        'aTargets': [2],
                        "sType" : "date-euro"
                    },
                    {
                        "bSearchable": false,
                        'aTargets': [3],
                        "sType" : "date-euro"
                    },
                    {
                        'bSortable': false,
                        "bSearchable": false,
                        'aTargets': [4]
                    },
                    {
                        'bSortable': false,
                        "bSearchable": false,
                        'aTargets': [5]
                    }
                ],
                "aaSorting": [[ 0, "asc" ]] /* sort by second column */
            });

            $('#editable-sample_wrapper .dataTables_filter input').addClass("form-control medium"); // modify table search input
            $('#editable-sample_wrapper .dataTables_length select').addClass("form-control xsmall"); // modify table per page dropdown

            var nEditing = null;

            $('#editable-sample_new').click(function (e) {
                e.preventDefault();

                //cancella query ricerca
                if($('#editable-sample_filter input').val() != '') oTable.fnFilterClear();
                
                var d = new Date();

                var month = d.getMonth()+1;
                var day = d.getDate();
                
                var data = twoDigits(day) + '/' +
                    twoDigits(month) + '/' +
                    d.getFullYear();
                    //+ " " + twoDigits(d.getUTCHours()) + ":" + twoDigits(d.getUTCMinutes()) + ":" + twoDigits(d.getUTCSeconds());
                
                var aiNew = oTable.fnAddData(['', '', data, data,
                        '<a class="edit" href="" >Modifica</a>', '<a class="cancel" data-mode="new" href="">Cancella</a>'
                ]);
                var nRow = oTable.fnGetNodes(aiNew[0]);

				var curr = $(nRow);
				var all = curr.closest('table').find('tr');
				all.each(function(){
					if($(this) != curr && $(this).hasClass('nuovariga')) oTable.fnDeleteRow($(this)[0]);
					else restoreRow(oTable, $(this)[0]);
				});

                oTable.fnSort( [ [ 0, "asc" ] ] );

                editRow(oTable, nRow, true);
                nEditing = nRow;
            });

            $('#editable-sample a.delete').live('click', function (e) {
                e.preventDefault();
                
                var nRow = $(this).parents('tr')[0];
                var tr = $(this).closest('tr');    
                var portata = tr.children('td:nth-child(1)');

                if (confirm("Sei sicuro di voler cancellare il piatto \""+portata.text()+"\" dalla serata odierna?") == false) {
                    return;
                }
                //console.log(materia);  
                var today = new Date();
                var dd = ( today.getDate() <= 9 ? '0'+today.getDate() : today.getDate());
                var mm = ( today.getMonth()+1 <= 9 ? '0'+(today.getMonth()+1) : today.getMonth()+1); //January is 0!
                var yyyy = today.getFullYear();  

                $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_piatti_serata.ajax.php",
                        dataType: "text",
                        timeout: 20000,
                        data : {
                            operazione: 'cancella',
                            portata: portata.text(),
                            serata: yyyy+'-'+mm+'-'+dd
                        },
                        beforeSend: function(){
                        },
                        success: function(result){
                            var errore = false;
                            if(stringStartsWith(result, '#error#')) errore=true;

                            if(!errore) oTable.fnDeleteRow(nRow);

                            notify_top(result, 'Operazione di cancellazione');
                        },
                        error: function( jqXHR, textStatus, errorThrown ){
                            notify_top("#error#Errore durante l'operazione", 'Operazione di cancellazione'); 
                        }
                    });
            });
         
            $('#editable-sample a.cancel').live('click', function (e) {
                e.preventDefault();
                var nRow = $(this).parents('tr')[0];
                if ($(this).attr("data-mode") == "new") {
                    oTable.fnDeleteRow(nRow);
                    nEditing = null;
                } else if ($(nRow).hasClass('nuovariga')){
                    oTable.fnDeleteRow(nRow);
                    nEditing = null;
                } else {
                    restoreRow(oTable, nEditing);
                    nEditing = null;
                }
            });

            $('#editable-sample a.edit').live('click', function (e) {
                e.preventDefault();

                /* Get the row as a parent of the link that was clicked on */
                var nRow = $(this).parents('tr')[0];

				var curr = $(nRow);
				var all = curr.closest('table').find('tr');

                if(nEditing !== null && nEditing != nRow && nEditing.classList.contains('nuovariga')){

                    all.each(function(){
                        restoreRow(oTable, $(this)[0]);
                    });

				    var curr = $(nEditing);
				    var all = curr.parent().find('.nuovariga');

		            all.each(function(){
					    oTable.fnDeleteRow($(this)[0]);
				    });
				    
                    modifyRow(oTable, nRow);
		            nEditing = nRow;
                } 
                else if (nEditing !== null && nEditing != nRow) {

                    /* Currently editing - but not this row - restore the old before continuing to edit mode */
                    /* per sicurezzo faccio il restore su tutte prima */
                    all.each(function(){
                        restoreRow(oTable, $(this)[0]);
                    });
                    modifyRow(oTable, nRow);
                    nEditing = nRow;
                } 
                else if (nEditing == nRow && this.innerHTML == "Salva") {
                    

                   	var tr = $(nRow);   
                    var portata = tr.children('td:nth-child(1)').find('select');
                    var quantita = tr.children('td:nth-child(2)').find('input');
                   	
                   	if(portata.val() == ''){
                   		tr.find('.bootstrap-select button').addClass('input-error');
                        
                        $('#portata_name').change(function(){
                            $(this).parent().find('button').removeClass('input-error');
                        });
						
                   		return false;
                   	} else if(quantita.val() == '' || isNaN(quantita.val()) || quantita.val()==0){
                        quantita.addClass('input-error');
                        
                        quantita.keyup(function(){
                            quantita.removeClass('input-error');
                        });
                        
                        return false;
                    }

                    var today = new Date();
                    var dd = ( today.getDate() <= 9 ? '0'+today.getDate() : today.getDate());
                    var mm = ( today.getMonth()+1 <= 9 ? '0'+(today.getMonth()+1) : today.getMonth()+1); //January is 0!
                    var yyyy = today.getFullYear();
                   	
        		    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_piatti_serata.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'inserisci',
                            portata: portata.val(),
                            quantita: quantita.val(),
                            serata: yyyy+'-'+mm+'-'+dd
                        },
                        beforeSend: function(){
                        },
                        success: function(result){
                            /* Editing this row and want to save it */
                            var errore = false;
                            if(stringStartsWith(result, '#error#')) errore=true;

                            if(!errore){ 
                                saveRow(oTable, nRow);
                                oTable.fnSort( [ [ 0, "asc" ] ] );
                                nEditing = null;
                            } else {
                                nEditing = nRow;
                            }

        				    notify_top(result, 'Operazione di inserimento');     
        			    },
                        error: function( jqXHR, textStatus, errorThrown ){
                            /*alert('Errore durante operazione '+errorThrown );*/
                            notify_top("#error#Errore durante l'operazione", 'Operazione di inserimento');
                        }
                    });
                } 
                else if (nEditing == nRow && this.innerHTML == "Aggiorna"){

                    var tr = $(nRow);  
                    var portata = tr.children('td:nth-child(1)').find('input');
                    var quantita = tr.children('td:nth-child(2)').find('input');
                    
                    if(portata.val() == ''){
                        portata.addClass('input-error');
                        
                        portata.keyup(function(){
                            portata.removeClass('input-error');
                        });
                        
                        return false;
                    } else if(quantita.val() == '' || isNaN(quantita.val()) || quantita.val()==0){
                        quantita.addClass('input-error');
                        
                        quantita.keyup(function(){
                            quantita.removeClass('input-error');
                        });
                        
                        return false;
                    }
                    
                    var today = new Date();
                    var dd = ( today.getDate() <= 9 ? '0'+today.getDate() : today.getDate());
                    var mm = ( today.getMonth()+1 <= 9 ? '0'+(today.getMonth()+1) : today.getMonth()+1); //January is 0!
                    var yyyy = today.getFullYear();
                    
                    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_piatti_serata.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'aggiorna',
                            portata: portata.val(),
                            quantita: quantita.val(),
                            serata: yyyy+'-'+mm+'-'+dd
                        },
                        beforeSend: function(){
                        },
                        success: function(result){
                            /* Editing this row and want to save it */
                            var errore = false;
                            if(stringStartsWith(result, '#error#')) errore=true;

                            if(!errore){ 
                                saveRow(oTable, nRow, true);
                                nEditing = null;
                            } else {
                                nEditing = nRow;
                            }

                            notify_top(result, 'Operazione di aggiornamento');     
                        },
                        error: function( jqXHR, textStatus, errorThrown ){
                            /*alert('Errore durante operazione '+errorThrown );*/
                            notify_top("#error#Errore durante l'operazione", 'Operazione di aggiornamento'); 
                        }
                    });
                    
                } 
                else{ 
                    /* No edit in progress - let's start one */
                    modifyRow(oTable, nRow);
                    nEditing = nRow;
                }
            });
        }

    };

}();

function stringStartsWith (string, prefix) {
    return string.substring(0, prefix.length) == prefix;
}

function twoDigits(d) {
    if(0 <= d && d < 10) return "0" + d.toString();
    if(-10 < d && d < 0) return "-0" + (-1*d).toString();
    return d.toString();
}
