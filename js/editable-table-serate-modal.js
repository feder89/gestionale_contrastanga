var EditableTableModal = function () {

    var oTable;
    var data_serata_act;

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
                    url: "ajax/ottieni_lista_menu.ajax.php",
                    dataType: "json",
                    timeout: 20000,
                    data : {},
                    beforeSend: function(){
                    },
                    success: function(result){
                        var errore = false;
                        var msg = '';
                        select_html = '<select data-live-search="true" class="selectpicker" data-size="10" id="menu_name" name="menu_name">';
                        select_html += '<option value=""></option>';

                        $.each(result, function(index, menu) {
                            if (typeof menu['error'] !== 'undefined') { errore = true; msg = menu['error']; return false; }
                            select_html += '<option value="'+menu['nome_menu']+'">'+menu['nome_menu']+'</option>';
                        });

                        select_html += '</select>';

                        if(errore){
                            notify_top(msg, 'Recupero lista Menù');
                            oTable.fnDeleteRow(nRow);
                        }
                        else{
                            jqTds[0].innerHTML = select_html;
                            jqTds[1].innerHTML = '';
                            jqTds[2].innerHTML = '';
                            jqTds[3].innerHTML = '<a class="edit" href="">Salva</a>';
                            jqTds[4].innerHTML = '<a class="cancel" href="">Annulla</a>';

                            $('#menu_name').selectpicker({
                              noneSelectedText:'Nessun menù selezionato',
                            });

                            $('#menu_name').on('changed.bs.select', function (e) {
                                 $.ajax({
                                    type: 'POST',
                                    url: "ajax/ottieni_lista_menu.ajax.php",
                                    dataType: "json",
                                    timeout: 20000,
                                    data : {
                                        'nome_menu': $('#menu_name').val()
                                    },
                                    beforeSend: function(){
                                    },
                                    success: function(result){
                                        var errore = false;
                                        var msg = '';
                                        var menuu;

                                        $.each(result, function(index, menu) {
                                            if (typeof menu['error'] !== 'undefined') { errore = true; msg = menu['error']; return false; }
                                            menuu = menu;
                                        });

                                        if(errore){
                                            notify_top(msg, 'Recupero info Menù');
                                        }
                                        else{

                                            jqTds[1].innerHTML = ( menuu['fisso'] == 1 ? 'Si' : 'No');
                                            jqTds[2].innerHTML = (menuu['prezzo_fisso'] != null ? menuu['prezzo_fisso'].replace('.', ',') +' &euro;' : '-');

                                            aData[0]= menuu['nome_menu'];
                                            aData[1]= jqTds[1].innerHTML;
                                            aData[2]= jqTds[2].innerHTML;

                                            console.log(aData);

                                        }

                                        //if(stringStartsWith(result, '#error#')) errore=true;
                                        //notify_top(result, 'Recupero lista Menù');
                                    },
                                    error: function( jqXHR, textStatus, errorThrown ){
                                        notify_top("#error#Errore durante l'operazione, riprova a selezionare il menù o ricarica la pagina", 'Recupero info Menù'); 
                                    }
                                });
                            });
                            
                            if(new_row) nRow.className = nRow.className + " nuovariga";   
                        }

                        //if(stringStartsWith(result, '#error#')) errore=true;
                        //notify_top(result, 'Recupero lista Menù');
                    },
                    error: function( jqXHR, textStatus, errorThrown ){
                        notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Menù'); 
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
                    url: "ajax/ottieni_lista_menu.ajax.php",
                    dataType: "json",
                    async: false,
                    timeout: 20000,
                    data : {},
                    beforeSend: function(){
                    },
                    success: function(result){
                        var errore = false;
                        var msg = '';
                        var nomemenu = aData[0];
                        select_html = '<select data-live-search="true" class="selectpicker" data-size="10" id="menu_name" name="menu_name">';
                        select_html += '<option value=""></option>';

                        $.each(result, function(index, menu) {
                            if (typeof menu['error'] !== 'undefined') { errore = true; msg = menu['error']; return false; }
                            if(menu['nome_menu'] == nomemenu) select_html += '<option value="'+menu['nome_menu']+'" selected>'+menu['nome_menu']+'</option>';
                            else select_html += '<option value="'+menu['nome_menu']+'">'+menu['nome_menu']+'</option>';
                        });

                        select_html += '</select>';

                        if(errore){
                            notify_top(msg, 'Recupero lista Menù');
                        }
                        else{
                            jqTds[0].innerHTML = select_html;
                            jqTds[1].innerHTML = aData[1];
                            jqTds[2].innerHTML = aData[2];
                            jqTds[3].innerHTML = '<a class="edit" href="">Aggiorna</a>'; 
                            jqTds[4].innerHTML = '<a class="cancel" href="">Annulla</a>';

                            $('#menu_name').selectpicker({
                              noneSelectedText:'Nessun menù selezionato',
                            }); 

                            $('#menu_name').on('changed.bs.select', function (e) {
                                 $.ajax({
                                    type: 'POST',
                                    url: "ajax/ottieni_lista_menu.ajax.php",
                                    dataType: "json",
                                    timeout: 20000,
                                    data : {
                                        'nome_menu': $('#menu_name').val()
                                    },
                                    beforeSend: function(){
                                    },
                                    success: function(result){
                                        var errore = false;
                                        var msg = '';
                                        var menuu;

                                        $.each(result, function(index, menu) {
                                            if (typeof menu['error'] !== 'undefined') { errore = true; msg = menu['error']; return false; }
                                            menuu = menu;
                                        });

                                        if(errore){
                                            notify_top(msg, 'Recupero info Menù');
                                        }
                                        else{

                                            jqTds[1].innerHTML = ( menuu['fisso'] == 1 ? 'Si' : 'No');
                                            jqTds[2].innerHTML = (menuu['prezzo_fisso'] != null ? menuu['prezzo_fisso'].replace('.', ',') +' &euro;' : '-');

                                        }

                                        //if(stringStartsWith(result, '#error#')) errore=true;
                                        //notify_top(result, 'Recupero lista Menù');
                                    },
                                    error: function( jqXHR, textStatus, errorThrown ){
                                        notify_top("#error#Errore durante l'operazione, riprova a selezionare il menù o ricarica la pagina", 'Recupero info Menù'); 
                                    }
                                });
                            });
                        }

                        //if(stringStartsWith(result, '#error#')) errore=true;
                        //notify_top(result, 'Recupero lista Menù');
                    },
                    error: function( jqXHR, textStatus, errorThrown ){
                        notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Menù'); 
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
                        'bSortable': false,
                        "bSearchable": false,
                        'aTargets': [1]
                    },
                    {
                        'bSortable': false,
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
                "aaSorting": [[ 0, "asc" ]] /* sort by first column */
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

                oTable.fnSort( [ [ 0, "asc" ] ] );

                editRow(oTable, nRow, true);
                nEditing = nRow;
            });

            $('#modal-gest-table a.delete').live('click', function (e) {
                e.preventDefault();
                
                var nRow = $(this).parents('tr')[0];
                var tr = $(this).closest('tr');
                var nome = tr.children('td:nth-child(1)');  

                if (confirm("Sei sicuro di voler cancellare il menù \""+nome.text()+"\" della serata del "+data_serata_act+"?") == false) {
                    return false;
                }
                //console.log(nome);              
                $.ajax({
                    type: 'POST',
                    url: "ajax/gestisci_menu_serata.ajax.php",
                    dataType: "text",
                    timeout: 20000,
                    data : {
                        operazione: 'cancella',
                        nome_menu: nome.text(),
                        data_serata: data_serata_act
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

                    var nome = $('#menu_name').val();

                    if(nome == ''){
                        tr.find('.bootstrap-select button').addClass('input-error');
                        
                        $('#menu_name').change(function(){
                            $(this).parent().find('button').removeClass('input-error');
                        });
                        
                        return false;
                    } 
                    
                    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_menu_serata.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'inserisci',
                            nome_menu: nome,
                            data_serata: data_serata_act
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

                    var nome = $('#menu_name').val();

                    if(nome == ''){
                        tr.find('.bootstrap-select button').addClass('input-error');
                        
                        $('#menu_name').change(function(){
                            $(this).parent().find('button').removeClass('input-error');
                        });
                        
                        return false;
                    } 
                    
                    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_menu_serata.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'aggiorna',
                            nome_menu: nome,
                            nome_menu_prec: nome_prec,
                            data_serata: data_serata_act
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
                        oTable.fnSort( [ [ 0, "asc" ] ] );
                    }
                }
            }
        },

        addRows: function (menus){
            $.each(menus, function(index, menu) {
                oTable.fnAddData([menu['nome_menu'],( menu['fisso'] == 1 ? 'Si' : 'No'),(menu['prezzo_fisso'] != null ? menu['prezzo_fisso'].replace('.', ',') +' &euro;' : '-'),
                        '<a class="edit" href="javascript:;">Modifica</a>', '<a class="delete" href="javascript:;">Cancella</a>'
                ]);
            });
        },

        setDate: function( date){
            data_serata_act = date;
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