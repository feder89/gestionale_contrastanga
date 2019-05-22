var EditableTable = function () {

    var oTable;
    var nomemenu;

    return {

        //main function to initiate the module
        init: function () {
			//$('#editable-sample tr > *:nth-child(8)').hide();
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
                
                jqTds[0].innerHTML = '<input type="text" maxlength="100" class="form-control small" value="' + aData[0] + '">';
                jqTds[1].innerHTML = '<select data-live-search="true" class="selectpicker" data-size="10" id="categoria_portata" name="menu_name">'+
                                        '<option value=""></option>'+
                                        '<option value="pane e coperto">Pane e Coperto</option>'+
                                        '<option value="antipasto">Antipasto</option>'+ 
                                        '<option value="bruschette e crostoni">Bruschette e Crostoni</option>'+
                                        '<option value="primo">Primo</option>'+
                                        '<option value="secondo">Secondo</option>'+
                                        '<option value="piadina">Piadina</option>'+
                                        '<option value="contorno">Contorno</option>'+
                                        '<option value="dolce">Dolce</option>'+
                                        '<option value="bevanda">Bevanda</option>'+
                                    '</select>';
                jqTds[2].innerHTML = '<input step="any" min="0.00" max="99.99" type="number" class="form-control small num"  value="' + aData[2] + '">';
                jqTds[3].innerHTML = '<input disabled type="text" class="form-control small" value="' + aData[3] + '">';
                jqTds[4].innerHTML = '<input disabled type="text" class="form-control small" value="' + aData[4] + '">';
                jqTds[5].innerHTML = '<a class="edit" href="">Salva</a>';
                jqTds[6].innerHTML = '<a class="cancel" href="">Annulla</a>';
				jqTds[7].innerHTML = '<input type="text" maxlength="3" class="form-control small" value="' + aData[7] + '">';

                $('#categoria_portata').selectpicker({
                    noneSelectedText:'Nessuna Categoria selezionata'
                });
                
                if(new_row) nRow.className = nRow.className + " nuovariga";   

            }

            function modifyRow(oTable, nRow) {
			    var aData = oTable.fnGetData(nRow);
                var jqTds = $('>td', nRow);
                jqTds[0].innerHTML = '<input type="text" maxlength="100" class="form-control small" value="' + aData[0] + '">';
                jqTds[1].innerHTML = '<select data-live-search="true" class="selectpicker" data-size="10" id="categoria_portata" name="menu_name">'+
                                        '<option value=""></option>'+
                                        '<option value="pane e coperto" '+(aData[1].toLowerCase() == 'pane e coperto' ? 'selected' : '')+'>Pane e Coperto</option>'+
                                        '<option value="antipasto" '+(aData[1].toLowerCase() == 'antipasto' ? 'selected' : '')+'>Antipasto</option>'+ 
                                        '<option value="bruschette e crostoni" '+(aData[1].toLowerCase() == 'bruschette e crostoni' ? 'selected' : '')+'>Bruschette e Crostoni</option>'+
                                        '<option value="primo" '+(aData[1].toLowerCase() == 'primo' ? 'selected' : '')+'>Primo</option>'+
                                        '<option value="secondo" '+(aData[1].toLowerCase() == 'secondo' ? 'selected' : '')+'>Secondo</option>'+
                                        '<option value="piadina" '+(aData[1].toLowerCase() == 'piadina' ? 'selected' : '')+'>Piadina</option>'+
                                        '<option value="contorno" '+(aData[1].toLowerCase() == 'contorno' ? 'selected' : '')+'>Contorno</option>'+
                                        '<option value="dolce" '+(aData[1].toLowerCase() == 'dolce' ? 'selected' : '')+'>Dolce</option>'+
                                        '<option value="bevanda" '+(aData[1].toLowerCase() == 'bevanda' ? 'selected' : '')+'>Bevanda</option>'+
                                    '</select>';
                jqTds[2].innerHTML = '<input step="any" min="0.00" max="99.99" type="number" class="form-control small num"  value="' + parseInt(aData[2]) + '">';
                jqTds[3].innerHTML = '<input disabled type="text" class="form-control small" value="' + aData[3] + '">';
                jqTds[4].innerHTML = '<input disabled type="text" class="form-control small" value="' + aData[4] + '">';
                jqTds[5].innerHTML = '<a class="edit" href="">Aggiorna</a>';
                jqTds[6].innerHTML = '<a class="cancel" href="">Annulla</a>';
				jqTds[7].innerHTML = '<input type="text" maxlength="3" class="form-control small" value="' + aData[7] + '">';

                 $('#categoria_portata').selectpicker({
                    noneSelectedText:'Nessuna Categoria selezionata',
                });
            }

            function saveRow(oTable, nRow) {

                $(nRow).removeClass('nuovariga');

                var jqInputs = $('input', nRow);
                var select = $('select', nRow);

                oTable.fnUpdate(jqInputs[0].value, nRow, 0, false);
                oTable.fnUpdate(select[0].value, nRow, 1, false);
                oTable.fnUpdate(jqInputs[2].value+" €", nRow, 2, false);
                oTable.fnUpdate(jqInputs[3].value, nRow, 3, false);
                oTable.fnUpdate(jqInputs[4].value, nRow, 4, false);
                oTable.fnUpdate('<a class="edit" href="">Modifica</a>', nRow, 5, false);
                oTable.fnUpdate('<a class="delete" href="">Cancella</a>', nRow, 6, false);
				oTable.fnUpdate(jqInputs[5].value, nRow, 7, false);
                oTable.fnDraw();
            }

            oTable = $('#editable-sample').dataTable({
                "aLengthMenu": [
                    [5, 10, 20, -1],
                    [5, 10, 20, "Tutti"] // change per page values here
                ],
                // set the initial value
                "iDisplayLength": 10,
                "bPaginate": false,
                "sDom": "<'row'<'col-lg-6'l><'col-lg-6'f>r>t<'row'<'col-lg-6'i><'col-lg-6'p>>",
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
                        "bSearchable": false,
                        'aTargets': [1],
                        "sType" : "categoria-portata"
                    },
                    {
                        "bSearchable": false,
                        'aTargets': [2]
                    },
                    {
                        "bSearchable": false,
                        'aTargets': [3],
                        "sType" : "date-euro"
                    },
                    {
                        "bSearchable": false,
                        'aTargets': [4],
                        "sType" : "date-euro"
                    },
                    {
                        'bSortable': false,
                        "bSearchable": false,
                        'aTargets': [5]
                    },
                    {
                        'bSortable': false,
                        "bSearchable": false,
                        'aTargets': [6]
                    },
					{
                        "bSearchable": false,
                        'aTargets': [7]
                    }
                ],
                "aaSorting": [[ 7, "asc" ]] /* sort by first column */
            });

            jQuery('#editable-sample_wrapper .dataTables_filter input').addClass("form-control medium"); // modify table search input
            jQuery('#editable-sample_wrapper .dataTables_length select').addClass("form-control xsmall"); // modify table per page dropdown

            var nEditing = null;
            var nome_prec; /* questo serve per la modifica (devo conoscere il valore che c'era prima) */

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
                
                var aiNew = oTable.fnAddData(['', '','', data, data,
                        '<a class="edit" href="" >Modifica</a>', '<a class="cancel" data-mode="new" href="">Cancella</a>',''
                ]);
                var nRow = oTable.fnGetNodes(aiNew[0]);

                var curr = $(nRow);
                var all = curr.closest('table').find('tr');
                all.each(function(){
                    if($(this) != curr && $(this).hasClass('nuovariga')) oTable.fnDeleteRow($(this)[0]);
                    else restoreRow(oTable, $(this)[0]);
                });

                oTable.fnSort( [ [ 7, "asc" ] ] );

                editRow(oTable, nRow, true);
                nEditing = nRow;
            });

            $('#editable-sample a.delete').live('click', function (e) {
                e.preventDefault();
                
                var nRow = $(this).parents('tr')[0];
                var tr = $(this).closest('tr');
                var nome = tr.children('td:nth-child(1)');  

                if (confirm("Sei sicuro di voler cancellare la portata \""+nome.text()+"?") == false) {
                    return false;
                }
            
                $.ajax({
                    type: 'POST',
                    url: "ajax/gestisci_portate.ajax.php",
                    dataType: "text",
                    timeout: 20000,
                    data : {
                        operazione: 'cancella',
                        nome: nome.text()
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
                    
                    nome_prec = $(nRow).find('td:first').text();
                    modifyRow(oTable, nRow);
                    nEditing = nRow;
                } 
                else if (nEditing !== null && nEditing != nRow) {

                    /* Currently editing - but not this row - restore the old before continuing to edit mode */
                    /* per sicurezzo faccio il restore su tutte prima */
                    all.each(function(){
                        restoreRow(oTable, $(this)[0]);
                    });
                    nome_prec = $(nRow).find('td:first').text();
                    modifyRow(oTable, nRow);
                    nEditing = nRow;
                } 
                else if (nEditing == nRow && this.innerHTML == "Salva") {
                    
                    var tr = $(nRow);

                    var categoria = $('#categoria_portata');
                    var nome = tr.children('td:first-child').find('input');
                    var costo = tr.children('td:nth-child(3)').find('input');
					var id = tr.children('td:nth-child(8)').find('input');

                    if(nome.val() == ''){
                        nome.addClass('input-error');
                        
                        nome.change(function(){
                            $(this).removeClass('input-error');
                        });
                        
                        return false;
                    } 
                    else if(categoria.val() == ''){
                        categoria.closest('td').find('.bootstrap-select button').addClass('input-error');
                        
                        categoria.change(function(){
                            $(this).closest('td').find('.bootstrap-select button').removeClass('input-error');
                        });
                    }
                    else if(costo.val() == '' || costo.val()<=0 || costo.val()>99.99){
                        costo.addClass('input-error');
                        
                        costo.keyup(function(){
                            costo.removeClass('input-error');
                        });
                        
                        return false;
                    }
					else if(id.val() == '' || id.val()<=0 || !isNaN(id)){
                        id.addClass('input-error');
                        
                        id.keyup(function(){
                            id.removeClass('input-error');
                        });
                        
                        return false;
                    }

                    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_portate.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'inserisci',
                            nome: nome.val(),
                            categoria: categoria.val(),
                            costo: costo.val(),
							id: id.val()
                        },
                        beforeSend: function(){
                        },
                        success: function(result){
                            /* Editing this row and want to save it */
                            var errore = false;
                            if(stringStartsWith(result, '#error#')) errore=true;

                            if(!errore){ 
                                saveRow(oTable, nRow);
                                oTable.fnSort( [ [ 7, "asc" ] ] );
                                nEditing = null;
                                var curr = $(nRow);

                                curr.removeClass('riga-antipasto').removeClass('riga-primo').removeClass('riga-secondo').removeClass('riga-contorno').removeClass('riga-dolce')
                                     .removeClass('riga-paneecoperto').removeClass('riga-piadina').removeClass('riga-bruschetteecrostoni').removeClass('riga-bevanda');

                                switch(curr.find('td:nth-child(2)').text().toLowerCase()) {
                                    case 'pane e coperto':
                                        curr.addClass('riga-paneecoperto');
                                        break;
                                    case 'piadina':
                                        curr.addClass('riga-piadina');
                                        break;
                                    case 'bruschette e crostoni':
                                        curr.addClass('riga-bruschetteecrostoni');
                                        break;
                                    case 'bevanda':
                                        curr.addClass('riga-bevanda');
                                        break;
                                    case 'antipasto':
                                        curr.addClass('riga-antipasto');
                                        break;
                                    case 'primo':
                                        curr.addClass('riga-primo');
                                        break;
                                    case 'secondo':
                                        curr.addClass('riga-secondo');
                                        break;
                                    case 'contorno':
                                        curr.addClass('riga-contorno');
                                        break;
                                    case 'dolce':
                                        curr.addClass('riga-dolce');
                                        break;
                                }
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
                    var nome = tr.children('td:nth-child(1)').find('input');
                    var categoria = tr.children('td:nth-child(2)').find('select');
                    var costo = tr.children('td:nth-child(3)').find('input');
					var id = tr.children('td:nth-child(8)').find('input');

                    if(nome.val() == ''){
                        nome.addClass('input-error');
                        
                        nome.keyup(function(){
                            nome.removeClass('input-error');
                        });
                        
                        return false;
                    } 
                    else if(categoria.val() == ''){

                        categoria.closest('.bootstrap-select').find('button').addClass('input-error');
                        
                        categoria.change(function(){
                            categoria.closest('.bootstrap-select').find('button').removeClass('input-error');
                        });
                        
                        return false;
                    }
                    else if(costo.val() == '' || costo.val()<=0 || costo.val()>99.99 ){

                        costo.addClass('input-error');
                        
                        costo.keyup(function(){
                            costo.removeClass('input-error');
                        });
                        
                        return false;
                    }
					else if(id.val() == '' || id.val()<=0 || !isNaN(id)){
                        id.addClass('input-error');
                        
                        id.keyup(function(){
                            id.removeClass('input-error');
                        });
                        
                        return false;
                    }
                    
                    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_portate.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'aggiorna',
                            nome: nome.val(),
                            nome_prec: nome_prec,
                            categoria: categoria.val(),
                            costo: costo.val(),
							id: id.val()
                        },
                        beforeSend: function(){
                        },
                        success: function(result){
                            /* Editing this row and want to save it */
                            var errore = false;
                            if(stringStartsWith(result, '#error#')) errore=true;

                            if(!errore){ 
                                saveRow(oTable, nRow);
                                oTable.fnSort( [ [ 7, "asc" ] ] );
                                nEditing = null;
                                
                                var curr = $(nRow);
                                curr.removeClass('riga-antipasto').removeClass('riga-primo').removeClass('riga-secondo').removeClass('riga-contorno').removeClass('riga-dolce')
                                     .removeClass('riga-paneecoperto').removeClass('riga-piadina').removeClass('riga-bruschetteecrostoni').removeClass('riga-bevanda');

                                switch(categoria.val().toLowerCase()) {
                                    case 'pane e coperto':
                                        curr.addClass('riga-paneecoperto');
                                        break;
                                    case 'piadina':
                                        curr.addClass('riga-piadina');
                                        break;
                                    case 'bruschette e crostoni':
                                        curr.addClass('riga-bruschetteecrostoni');
                                        break;
                                    case 'bevanda':
                                        curr.addClass('riga-bevanda');
                                        break;
                                    case 'antipasto':
                                        curr.addClass('riga-antipasto');
                                        break;
                                    case 'primo':
                                        curr.addClass('riga-primo');
                                        break;
                                    case 'secondo':
                                        curr.addClass('riga-secondo');
                                        break;
                                    case 'contorno':
                                        curr.addClass('riga-contorno');
                                        break;
                                    case 'dolce':
                                        curr.addClass('riga-dolce');
                                        break;
                                }


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
                    nome_prec = $(nRow).find('td:first').text();
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