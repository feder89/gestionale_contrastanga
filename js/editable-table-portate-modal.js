var EditableTableModal = function () {

    var oTable;
    var nomeportata;

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
                    url: "ajax/ottieni_lista_materieprime.ajax.php",
                    dataType: "json",
                    timeout: 20000,
                    data : {},
                    beforeSend: function(){
                    },
                    success: function(result){
                        var errore = false;
                        var msg = '';
                        select_html = '<select data-live-search="true" class="selectpicker" data-size="10" id="materiaprima_name" name="materiaprima_name">';
                        select_html += '<option value=""></option>';

                        $.each(result, function(index, materia) {
                            if (typeof materia['error'] !== 'undefined') { errore = true; msg = materia['error']; return false; }
                            select_html += '<option value="'+materia['nome_materia']+'">'+materia['nome_materia']+'</option>';
                        });

                        select_html += '</select>';

                        if(errore){
                            notify_top(msg, 'Recupero lista Materie Prime');
                            oTable.fnDeleteRow(nRow);
                        }
                        else{
                            jqTds[0].innerHTML = select_html;
                            jqTds[1].innerHTML = '';
                            jqTds[2].innerHTML = '<input step="any" min="0.00" max="99.99" type="number" class="form-control small num" value="">';
                            jqTds[3].innerHTML = '<a class="edit" href="">Salva</a>';
                            jqTds[4].innerHTML = '<a class="cancel" href="">Annulla</a>';

                            $('#materiaprima_name').selectpicker({
                              noneSelectedText:'Nessuna Materia Prima selezionata',
                            });

                            $('#materiaprima_name').on('changed.bs.select', function (e) {
                                 $.ajax({
                                    type: 'POST',
                                    url: "ajax/ottieni_lista_materieprime.ajax.php",
                                    dataType: "json",
                                    timeout: 20000,
                                    data : {
                                        'nome_materia': $('#materiaprima_name').val()
                                    },
                                    beforeSend: function(){
                                    },
                                    success: function(result){
                                        var errore = false;
                                        var msg = '';
                                        var materiaa;

                                        $.each(result, function(index, materia) {
                                            if (typeof materia['error'] !== 'undefined') { errore = true; msg = materia['error']; return false; }
                                            materiaa = materia;
                                        });

                                        if(errore){
                                            notify_top(msg, 'Recupero info Materia Prima');
                                        }
                                        else{
                                            jqTds[1].innerHTML = materiaa['genere'].charAt(0).toUpperCase() + materiaa['genere'].slice(1);

                                            aData[0]= materiaa['nome_portata'];
                                            aData[1]= jqTds[1].innerHTML;
                                        }

                                        //if(stringStartsWith(result, '#error#')) errore=true;
                                        //notify_top(result, 'Recupero lista Menù');
                                    },
                                    error: function( jqXHR, textStatus, errorThrown ){
                                        notify_top("#error#Errore durante l'operazione, riprova a selezionare la materia o ricarica la pagina", 'Recupero info Portata'); 
                                    }
                                });
                            });
                            
                            if(new_row) nRow.className = nRow.className + " nuovariga";   
                        }

                        //if(stringStartsWith(result, '#error#')) errore=true;
                        //notify_top(result, 'Recupero lista Menù');
                    },
                    error: function( jqXHR, textStatus, errorThrown ){
                        notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Materie Prime'); 
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
                    url: "ajax/ottieni_lista_materieprime.ajax.php",
                    dataType: "json",
                    async: false,
                    timeout: 20000,
                    data : {},
                    beforeSend: function(){
                    },
                    success: function(result){
                        var errore = false;
                        var msg = '';
                        var nomemateria = aData[0];
                        select_html = '<select data-live-search="true" class="selectpicker" data-size="10" id="materiaprima_name" name="materiaprima_name">';
                        select_html += '<option value=""></option>';

                        $.each(result, function(index, materia) {
                            if (typeof materia['error'] !== 'undefined') { errore = true; msg = materia['error']; return false; }
                            if(materia['nome_materia'] == nomemateria) select_html += '<option value="'+materia['nome_materia']+'" selected>'+materia['nome_materia']+'</option>';
                            else select_html += '<option value="'+materia['nome_materia']+'">'+materia['nome_materia']+'</option>';
                        });

                        select_html += '</select>';

                        if(errore){
                            notify_top(msg, 'Recupero lista Materie Prime');
                        }
                        else{

                            jqTds[0].innerHTML = select_html;
                            jqTds[1].innerHTML = aData[1];
                            jqTds[2].innerHTML = '<input step="any" min="0.00" max="99.99" type="number" class="form-control small num" value="'+parseFloat(aData[2])+'">';
                            jqTds[3].innerHTML = '<a class="edit" href="">Aggiorna</a>'; 
                            jqTds[4].innerHTML = '<a class="cancel" href="">Annulla</a>';

                            $('#materiaprima_name').selectpicker({
                              noneSelectedText:'Nessuna Materia Prima selezionata',
                            }); 

                            $('#materiaprima_name').on('changed.bs.select', function (e) {
                                 $.ajax({
                                    type: 'POST',
                                    url: "ajax/ottieni_lista_materieprime.ajax.php",
                                    dataType: "json",
                                    timeout: 20000,
                                    data : {
                                        'nome_materia': $('#materiaprima_name').val()
                                    },
                                    beforeSend: function(){
                                    },
                                    success: function(result){
                                        var errore = false;
                                        var msg = '';
                                        var materiaa;

                                        $.each(result, function(index, materia) {
                                            if (typeof materia['error'] !== 'undefined') { errore = true; msg = materia['error']; return false; }
                                            materiaa = materia;
                                        });

                                        if(errore){
                                            notify_top(msg, 'Recupero info Materia Prima');
                                        }
                                        else{

                                            jqTds[1].innerHTML = materiaa['genere'].charAt(0).toUpperCase() + materiaa['genere'].slice(1);
                                            /*
                                            var curr = $(jqTds[1]).parent('tr');

                                            curr.removeClass('riga-antipasto').removeClass('riga-primo').removeClass('riga-secondo').removeClass('riga-contorno').removeClass('riga-dolce');

                                            switch(materiaa['categoria']) {
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
                                            */

                                        }

                                        //if(stringStartsWith(result, '#error#')) errore=true;
                                        //notify_top(result, 'Recupero lista Menù');
                                    },
                                    error: function( jqXHR, textStatus, errorThrown ){
                                        notify_top("#error#Errore durante l'operazione, riprova a selezionare la materia prima o ricarica la pagina", 'Recupero info Materia Prima'); 
                                    }
                                });
                            });
                        }

                        //if(stringStartsWith(result, '#error#')) errore=true;
                        //notify_top(result, 'Recupero lista Menù');
                    },
                    error: function( jqXHR, textStatus, errorThrown ){
                        notify_top("#error#Errore durante l'operazione, ricarica la pagina", 'Recupero lista Materie Prime'); 
                    }
                });
            }

            function saveRow(oTable, nRow) {

                $(nRow).removeClass('nuovariga');

                var jqInputs = $('input', nRow);
                var select = $('select', nRow);

                oTable.fnUpdate(select[0].value, nRow, 0, false);
                oTable.fnUpdate(jqInputs[1].value+' kg', nRow, 2, false);
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
                        "bSearchable": true,
                        'aTargets': [1]
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

                if (confirm("Sei sicuro di voler cancellare la materia prima \""+nome.text()+"\" dalla portata "+nomeportata+"?") == false) {
                    return false;
                }
                //console.log(nome);              
                $.ajax({
                    type: 'POST',
                    url: "ajax/gestisci_materie_portata.ajax.php",
                    dataType: "text",
                    timeout: 20000,
                    data : {
                        operazione: 'cancella',
                        nome_materia: nome.text(),
                        nome_portata: nomeportata
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
                    /*
                    var curr = $(nRow);

                    curr.removeClass('riga-antipasto').removeClass('riga-primo').removeClass('riga-secondo').removeClass('riga-contorno').removeClass('riga-dolce');

                    switch(curr.find('td:nth-child(2)').text().toLowerCase()) {
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
                    */
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

                    var nome = $('#materiaprima_name');
                    var peso = tr.find('td:nth-child(3)').find('input');

                    if(nome.val() == ''){
                        nome.closest('td').find('.bootstrap-select button').addClass('input-error');
                        
                        $('#materiaprima_name').change(function(){
                            $(this).parent().find('button').removeClass('input-error');
                        });
                        
                        return false;
                    } 
                    else if(peso.val() == '' || peso.val()<=0 || peso.val()>99.99){
                        peso.addClass('input-error');
                        
                        peso.keyup(function(){
                            peso.removeClass('input-error');
                        });
                        
                        return false;
                    }

                    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_materie_portata.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'inserisci',
                            nome_portata: nomeportata,
                            nome_materia: nome.val(),
                            peso: peso.val()
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
                                /*
                                curr.removeClass('riga-antipasto').removeClass('riga-primo').removeClass('riga-secondo').removeClass('riga-contorno').removeClass('riga-dolce');

                                switch(curr.find('td:nth-child(2)').text().toLowerCase()) {
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
                                */
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

                    var nome = $('#materiaprima_name');
                    var peso = tr.find('td:nth-child(3)').find('input');

                    if(nome.val() == ''){
                        nome.closest('td').find('.bootstrap-select button').addClass('input-error');
                        
                        $('#materiaprima_name').change(function(){
                            $(this).parent().find('button').removeClass('input-error');
                        });
                        
                        return false;
                    } 
                    else if(peso.val() == '' || peso.val()<=0 || peso.val()>99.99){
                        peso.addClass('input-error');
                        
                        peso.keyup(function(){
                            peso.removeClass('input-error');
                        });
                        
                        return false;
                    }
                    
                    $.ajax({
                        type: 'POST',
                        url: "ajax/gestisci_materie_portata.ajax.php", 
                        dataType: 'text',
                        timeout: 20000,
                        data : {
                            operazione: 'aggiorna',
                            nome_portata: nomeportata,
                            nome_materia: nome.val(),
                            nome_materia_prec: nome_prec,
                            peso: peso.val()
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

        addRows: function (materie){
            $.each(materie, function(index, materia) {
                var aiNew = oTable.fnAddData([materia['nome_materia'],materia['genere'].charAt(0).toUpperCase() + materia['genere'].slice(1),materia['peso']+' kg',
                        '<a class="edit" href="javascript:;">Modifica</a>', '<a class="delete" href="javascript:;">Cancella</a>'
                ]);

                /* colora la riga */
                /*
                var nRow = oTable.fnGetNodes(aiNew[0]);
                var curr = $(nRow);
                var categoria = curr.find('td:nth-child(2)').addClass('categoria-portata').text();
                
                switch(categoria) {
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
                */
            });
        },

        setPortataname: function( nomep){
            nomeportata = nomep;
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