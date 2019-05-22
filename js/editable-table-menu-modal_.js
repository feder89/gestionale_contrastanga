var EditableTableModal = function () {

    var oTable;
    var nomemenu;

    return {

        //main function to initiate the module
        init: function () {
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

                /*get menu list */
                var select_html = undefined;
                $.ajax({
                    type: 'POST',
                    url: "ajax/ottieni_lista_portate.ajax.php",
                    dataType: "json",
                    timeout: 20000,
                    data : {},
                    beforeSend: function(){
                    },
                    success: function(result){
                        var errore = false;
                        var msg = '';
                        select_html = '<select data-live-search="true" class="selectpicker" data-size="10" id="portata_name" name="menu_name">';
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
                            jqTds[1].innerHTML = '';
                            jqTds[2].innerHTML = '';
                            jqTds[3].innerHTML = '<a class="edit" href="">Salva</a>';
                            jqTds[4].innerHTML = '<a class="cancel" href="">Annulla</a>';

                            $('#portata_name').selectpicker({
                              noneSelectedText:'Nessuna Portata selezionata',
                            });

                            $('#portata_name').on('changed.bs.select', function (e) {
                                 $.ajax({
                                    type: 'POST',
                                    url: "ajax/ottieni_lista_portate.ajax.php",
                                    dataType: "json",
                                    timeout: 20000,
                                    data : {
                                        'nome_portata': $('#portata_name').val()
                                    },
                                    beforeSend: function(){
                                    },
                                    success: function(result){
                                        var errore = false;
                                        var msg = '';
                                        var portataa;

                                        $.each(result, function(index, portata) {
                                            if (typeof portata['error'] !== 'undefined') { errore = true; msg = portata['error']; return false; }
                                            portataa = portata;
                                        });

                                        if(errore){
                                            notify_top(msg, 'Recupero info Portata');
                                        }
                                        else{
                                            jqTds[1].innerHTML = portataa['categoria'].charAt(0).toUpperCase() + portataa['categoria'].slice(1);
                                            jqTds[2].innerHTML = portataa['prezzo_finale'] +' &euro;';

                                            aData[0]= portataa['nome_portata'];
                                            aData[1]= jqTds[1].innerHTML;
                                            aData[2]= jqTds[2].innerHTML;
                                        }

                                        //if(stringStartsWith(result, '#error#')) errore=true;
                                        //notify_top(result, 'Recupero lista Menù');
                                    },
                                    error: function( jqXHR, textStatus, errorThrown ){
                                        notify_top("#error#Errore durante l'operazione, riprova a selezionare la portata o ricarica la pagina", 'Recupero info Portata'); 
                                    }
                                });
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

                /*get menu list */
                var select_html = undefined;
                $.ajax({
                    type: 'POST',
                    url: "ajax/ottieni_lista_portate.ajax.php",
                    dataType: "json",
                    async: false,
                    timeout: 20000,
                    data : {},
                    beforeSend: function(){
                    },
                    success: function(result){
                        var errore = false;
                        var msg = '';
                        var nomeportata = aData[0];
                        select_html = '<select data-live-search="true" class="selectpicker" data-size="10" id="portata_name" name="menu_name">';
                        select_html += '<option value=""></option>';

                        $.each(result, function(index, portata) {
                            if (typeof portata['error'] !== 'undefined') { errore = true; msg = portata['error']; return false; }
                            if(portata['nome_portata'] == nomeportata) select_html += '<option value="'+portata['nome_portata']+'" selected>'+portata['nome_portata']+'</option>';
                            else select_html += '<option value="'+portata['nome_portata']+'">'+portata['nome_portata']+'</option>';
                        });

                        select_html += '</select>';

                        if(errore){
                            notify_top(msg, 'Recupero lista Portate');
                        }
                        else{

                            jqTds[0].innerHTML = select_html;
                            jqTds[1].innerHTML = aData[1];
                            jqTds[2].innerHTML = aData[2];
                            jqTds[3].innerHTML = '<a class="edit" href="">Aggiorna</a>'; 
                            jqTds[4].innerHTML = '<a class="cancel" href="">Annulla</a>';

                            $('#portata_name').selectpicker({
                              noneSelectedText:'Nessuna Portata selezionato',
                            }); 

                            $('#portata_name').on('changed.bs.select', function (e) {
                                 $.ajax({
                                    type: 'POST',
                                    url: "ajax/ottieni_lista_portate.ajax.php",
                                    dataType: "json",
                                    timeout: 20000,
                                    data : {
                                        'nome_portata': $('#portata_name').val()
                                    },
                                    beforeSend: function(){
                                    },
                                    success: function(result){
                                        var errore = false;
                                        var msg = '';
                                        var portataa;

                                        $.each(result, function(index, portata) {
                                            if (typeof portata['error'] !== 'undefined') { errore = true; msg = portata['error']; return false; }
                                            portataa = portata;
                                        });

                                        if(errore){
                                            notify_top(msg, 'Recupero info Portata');
                                        }
                                        else{

                                            jqTds[1].innerHTML = portataa['categoria'].charAt(0).toUpperCase() + portataa['categoria'].slice(1);
                                            jqTds[2].innerHTML = portataa['prezzo_finale'] +' &euro;';

                                            var curr = $(jqTds[1]).parent('tr');

                                            curr.removeClass('riga-antipasto').removeClass('riga-primo').removeClass('riga-secondo').removeClass('riga-contorno').removeClass('riga-dolce')
                                                 .removeClass('riga-paneecoperto').removeClass('riga-piadina').removeClass('riga-bruschetteecrostoni').removeClass('riga-bevanda');

                                            switch(portataa['categoria']) {
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

                                        }

                                        //if(stringStartsWith(result, '#error#')) errore=true;
                                        //notify_top(result, 'Recupero lista Menù');
                                    },
                                    error: function( jqXHR, textStatus, errorThrown ){
                                        notify_top("#error#Errore durante l'operazione, riprova a selezionare la portata o ricarica la pagina", 'Recupero info Portata'); 
                                    }
                                });
                            });
                        }

                        //if(stringStartsWith(result, '#error#')) errore=true;
                        //notify_top(result, 'Recupero lista Menù');
                    },
                    error: function( jqXHR, textStatus, errorThrown ){
                        notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Portate'); 
                    }
                });
            }

            function saveRow(oTable, nRow) {

                 $(nRow).removeClass('nuovariga');

                var jqInputs = $('input', nRow);
                var select = $('select', nRow);

                oTable.fnUpdate(select[0].value, nRow, 0, false);
                oTable.fnUpdate('<a class="edit" href="">Modifica</a>', nRow, 3, false);
                oTable.fnUpdate('<a class="delete" href="">Cancella</a>', nRow, 4, false);
                oTable.fnDraw();
            }

            oTable = $('#modal-gest-table').dataTable({ 
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
                        'bSortable': false,
                        "bSearchable": false,
                        'aTargets': [3]
                    },
                    {
                        'bSortable': false,
                        "bSearchable": false,
                        'aTargets': [4]
                    }
                ],
                "aaSorting": [[ 1, "asc" ]] /* sort by first column */
            });

            jQuery('#modal-gest-table_wrapper .dataTables_filter input').addClass("form-control medium"); // modify table search input
            jQuery('#modal-gest-table_wrapper .dataTables_length select').addClass("form-control xsmall"); // modify table per page dropdown

            var nEditing = null;
            var nome_prec; /* questo serve per la modifica (devo conoscere il valore che c'era prima) */

            $('#modal-gest-table_new').click(function (e) {
                e.preventDefault();

                //cancella query ricerca
                if($('#modal-gest-table_filter input').val() != '') oTable.fnFilterClear();
                
                var aiNew = oTable.fnAddData(['','','',
                        '<a class="edit" href="" >Modifica</a>', '<a class="cancel" data-mode="new" href="">Cancella</a>'
                ]);
                var nRow = oTable.fnGetNodes(aiNew[0]);

                var curr = $(nRow);
                var all = curr.closest('table').find('tr');
                all.each(function(){
                    if($(this) != curr && $(this).hasClass('nuovariga')) oTable.fnDeleteRow($(this)[0]);
                    else restoreRow(oTable, $(this)[0]);
                });

                oTable.fnSort( [ [ 1, "asc" ] ] );

                editRow(oTable, nRow, true);
                nEditing = nRow;
            });

            $('#modal-gest-table a.delete').live('click', function (e) {
                e.preventDefault();
                
                var nRow = $(this).parents('tr')[0];
                var tr = $(this).closest('tr');
                var nome = tr.children('td:nth-child(1)');  

                if (confirm("Sei sicuro di voler cancellare la portata \""+nome.text()+"\" del menu "+nomemenu+"?") == false) {
                    return false;
                }
                //console.log(nome);              
                $.ajax({
                    type: 'POST',
                    url: "ajax/gestisci_portate_menu.ajax.php",
                    dataType: "text",
                    timeout: 20000,
                    data : {
                        operazione: 'cancella',
                        nome_portata: nome.text(),
                        nome_menu: nomemenu
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
         
            $('#modal-gest-table a.cancel').live('click', function (e) {
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
                    nEditing = null;
                }

            });

            $('#modal-gest-table a.edit').live('click', function (e) {
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

                    var nome = $('#portata_name').val();

                    if(nome == ''){
                        tr.find('.bootstrap-select button').addClass('input-error');
                        
                        $('#portata_name').change(function(){
                            $(this).parent().find('button').removeClass('input-error');
                        });
                        
                        return false;
                    } 

                    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_portate_menu.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'inserisci',
                            nome_menu: nomemenu,
                            nome_portata: nome
                        },
                        beforeSend: function(){
                        },
                        success: function(result){
                            /* Editing this row and want to save it */
                            var errore = false;
                            if(stringStartsWith(result, '#error#')) errore=true;

                            if(!errore){ 
                                saveRow(oTable, nRow);
                                oTable.fnSort( [ [ 1, "asc" ] ] );
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

                    var nome = $('#portata_name').val();

                    if(nome == ''){
                        tr.find('.bootstrap-select button').addClass('input-error');
                        
                        $('#portata_name').change(function(){
                            $(this).parent().find('button').removeClass('input-error');
                        });
                        
                        return false;
                    } 
                    
                    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_portate_menu.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'aggiorna',
                            nome_menu: nomemenu,
                            nome_portata_prec: nome_prec,
                            nome_portata: nome
                        },
                        beforeSend: function(){
                        },
                        success: function(result){
                            /* Editing this row and want to save it */
                            var errore = false;
                            if(stringStartsWith(result, '#error#')) errore=true;

                            if(!errore){ 
                                saveRow(oTable, nRow);
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
                    nome_prec = $(nRow).find('td:first').text();
                    modifyRow(oTable, nRow);
                    nEditing = nRow;
                }
            });
        },

        clear: function (){
            if( typeof oTable !== 'undefined'){
                var settings = $.fn.dataTableSettings;
                for ( var i=0; i < settings.length ; i++){
                    if ( settings[i].nTable === oTable[0] ){
                        //console.log(oTable);
                        oTable.fnClearTable();
                        oTable.fnSort( [ [ 1, "asc" ]] );
                    }
                }
            }
        },

        addRows: function (portate){
            $.each(portate, function(index, portata) {
                var aiNew = oTable.fnAddData([portata['nome_portata'],portata['categoria'],portata['prezzo_finale']+' &euro;',
                        '<a class="edit" href="javascript:;">Modifica</a>', '<a class="delete" href="javascript:;">Cancella</a>'
                ]);

                /* colora la riga */
                var nRow = oTable.fnGetNodes(aiNew[0]);
                var curr = $(nRow);

                var categoria = curr.find('td:nth-child(2)').addClass('categoria-portata').text();

                curr.removeClass('riga-antipasto').removeClass('riga-primo').removeClass('riga-secondo').removeClass('riga-contorno').removeClass('riga-dolce')
                     .removeClass('riga-paneecoperto').removeClass('riga-piadina').removeClass('riga-bruschetteecrostoni').removeClass('riga-bevanda');

                switch(categoria) {
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
            });
        },

        setMenuname: function( nomem){
            nomemenu = nomem;
        },
        draw: function(){
            oTable.fnDraw();
        },
        adjustColumns: function (){
            oTable.fnAdjustColumnSizing();
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