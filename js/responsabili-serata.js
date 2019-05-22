$(document).ready(function(){

	$('.selectpicker').selectpicker({
        noneSelectedText:'Nessun Responsabile selezionato'
    });

  	$('#bottone-continua').on('click', function(e){	
      	//controllo valori
      	var options= new Object();
      	var err=false;
		$("#editable-sample tbody tr").each(function( index ) {

		});

	    $.ajax({
	        type: 'POST',
	        url: "ajax/responsabili_serate.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'check'
	        },
	        beforeSend: function(){
	        },
	        success: function(result){
	            var errore = false;
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore) notify_top(result, 'Inserimento Responsabili Serata'); 
	            else{ 
	            	if($("#bottone-continua").hasClass('continua')) window.location.href = "gestione_piatti_serata.php?procedura=init";
	            	else window.location.href = "index.php";
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Responsabili Serata'); 
	        }   

		});
      
	    e.preventDefault();
	    return false;
  	});

  	$('#bottone_associa').on('click', function(e){	

  		var select = $("#select_responsabile");
  		var err=false;
  		if(select.val() == ''){
            select.closest('.bootstrap-select').find('button').addClass('input-error');
                    
            select.change(function(){
                $(this).closest('.bootstrap-select').find('button').removeClass('input-error');
            });
            err= true;
        }
        if(err) return false;

      	//controllo valori
      	var tavoli= new Array();
		$("#editable-sample tbody tr").each(function( index ) {
		    var checkbox = $(this).find('input[type="checkbox"]');
		    if(checkbox.attr("checked")) tavoli.push($(this).find("td:nth-child(2)").text());
		});
		if(tavoli.length <=0 ) return false;

	    $.ajax({
	        type: 'POST',
	        url: "ajax/responsabili_serate.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'inserisci-init',
	            tavoli: tavoli,
	            responsabile: select.val()
	        },
	        beforeSend: function(){
	        },
	        success: function(result){
	            var errore = false;
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore) notify_top(result, 'Inserimento Responsabili Serata'); 
	            else {
	            	//mostra responsabile nella tabella
	            	$("#editable-sample tbody tr").each(function( index ) {
	            		$(this).find('input[type="checkbox"]').attr('checked', false);
					    var tavolo = $(this).find('td:nth-child(2)').text();
					    if($.inArray( tavolo, tavoli) != -1){
					    	$(this).addClass('selected-green');
					    	$(this).find('td:nth-child(4)').text(select.val());
					    }
					});
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Responsabili Serata'); 
	        }   

		});
      
	    e.preventDefault();
	    return false;
  	});

  	$('#bottone_disassocia').on('click', function(e){	

      	//controllo valori
      	var tavoli= new Array();
		$("#editable-sample tbody tr").each(function( index ) {
		    var checkbox = $(this).find('input[type="checkbox"]');
		    if(checkbox.attr("checked")) tavoli.push($(this).find("td:nth-child(2)").text());
		});
		if(tavoli.length <=0 ) return false;

	    $.ajax({
	        type: 'POST',
	        url: "ajax/responsabili_serate.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'disassocia-init',
	            tavoli: tavoli
	        },
	        beforeSend: function(){
	        },
	        success: function(result){
	            var errore = false;
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore) notify_top(result, 'Rimozione Responsabili Serata'); 
	            else {
	            	//mostra responsabile nella tabella
	            	$("#editable-sample tbody tr").each(function( index ) {
	            		$(this).find('input[type="checkbox"]').attr('checked', false);
					    var tavolo = $(this).find('td:nth-child(2)').text();
					    if($.inArray( tavolo, tavoli) != -1){
					    	$(this).removeClass('selected-green');
					    	$(this).find('td:nth-child(4)').text("");
					    }
					});
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	            notify_top("#error#Errore durante l'operazione", 'Rimozione Responsabili Serata'); 
	        }   

		});
      
	    e.preventDefault();
	    return false;
  	});

  	$('#bottone_associa_mod').on('click', function(e){	
  		var select = $("#select_responsabile");
  		var err=false;
  		if(select.val() == ''){
            select.closest('.bootstrap-select').find('button').addClass('input-error');
                    
            select.change(function(){
                $(this).closest('.bootstrap-select').find('button').removeClass('input-error');
            });
            err= true;
        }
        if(err) return false;

      	//controllo valori
      	var tavoli= new Array();
		$("#editable-sample tbody tr").each(function( index ) {
		    var checkbox = $(this).find('input[type="checkbox"]');
		    if(checkbox.attr("checked")) tavoli.push($(this).find("td:nth-child(2)").text());
		});
		if(tavoli.length <=0 ) return false;

	    $.ajax({
	        type: 'POST',
	        url: "ajax/responsabili_serate.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'inserisci_mod',
	            tavoli: tavoli,
	            responsabile: select.val()
	        },
	        beforeSend: function(){
	        },
	        success: function(result){
	            var errore = false;
	            if(stringStartsWith(result, '#error#')) errore=true;

	            if(errore) notify_top(result, 'Inserimento Responsabili Serata'); 
	            else {
	            	//mostra responsabile nella tabella
	            	$("#editable-sample tbody tr").each(function( index ) {
	            		$(this).find('input[type="checkbox"]').attr('checked', false);
					    var tavolo = $(this).find('td:nth-child(2)').text();
					    if($.inArray( tavolo, tavoli) != -1){
					    	$(this).addClass('selected-green');
					    	$(this).find('td:nth-child(4)').text(select.val());
					    }
					});
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	            notify_top("#error#Errore durante l'operazione", 'Inserimento Responsabili Serata'); 
	        }   

		});
      
	    e.preventDefault();
	    return false;
  	});

  	$('#bottone_disassocia_mod').on('click', function(e){	

      	//controllo valori
      	var tavoli= new Array();
		$("#editable-sample tbody tr").each(function( index ) {
		    var checkbox = $(this).find('input[type="checkbox"]');
		    if(checkbox.attr("checked")) tavoli.push($(this).find("td:nth-child(2)").text());
		});
		if(tavoli.length <=0 ) return false;

	    $.ajax({
	        type: 'POST',
	        url: "ajax/responsabili_serate.ajax.php",
	        dataType: "json",
	        timeout: 20000,
	        data : {
	            operazione: 'disassocia-mod',
	            tavoli: tavoli
	        },
	        beforeSend: function(){
	        },
	        success: function(result){

	        	var msg = '';
	            var errore = false;
	            var tav_new = new Array();

	            $.each(result, function(index, ris) {
	                if (typeof ris['error'] != 'undefined') { errore = true; msg = ris['error']; return false; }
	                tav_new.push(ris);
	            });

	            if(errore) notify_top(msg, 'Rimozione Responsabili Serata'); 
	            else {
	            	//mostra responsabile nella tabella
	            	$("#editable-sample tbody tr").each(function( index ) {
	            		$(this).find('input[type="checkbox"]').attr('checked', false);
					    var tavolo = $(this).find('td:nth-child(2)').text();
					    var tavolo_obj;
					    $.each(tav_new, function(index, tav) {
			                if (tav['tavolo'] == tavolo) { tavolo_obj = tav; }
			            });
			            if( typeof tavolo_obj == 'undefined' ){ return true;  } // skip to next iteration

			            if( tavolo_obj['eliminato'] == 0){
			            	notify_top(tavolo_obj['msg'], 'Rimozione Responsabili Serata'); 
			            }
			            else{
			            	$(this).find('td:nth-child(4)').text(tavolo_obj['nuovo_responsabile']);
			            }
					    	
					    
					});
	            }
	        },
	        error: function( jqXHR, textStatus, errorThrown ){
	            notify_top("#error#Errore durante l'operazione", 'Rimozione Responsabili Serata'); 
	        }   

		});
      
	    e.preventDefault();
	    return false;
  	});
});

function stringStartsWith (string, prefix) {
    return string.substring(0, prefix.length) == prefix;
}

function twoDigits(d) {
    if(0 <= d && d < 10) return "0" + d.toString();
    if(-10 < d && d < 0) return "-0" + (-1*d).toString();
    return d.toString();
}