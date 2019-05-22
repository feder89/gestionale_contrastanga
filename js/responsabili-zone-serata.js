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
	        url: "ajax/responsabili_zone_serate.ajax.php",
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
      	/*var tavoli= new Array();
		$("#editable-sample tbody tr").each(function( index ) {
		    var checkbox = $(this).find('input[type="checkbox"]');
		    if(checkbox.attr("checked")) tavoli.push($(this).find("td:nth-child(2)").text());
		});
		if(tavoli.length <=0 ) return false;*/
        var zone= new Array();
        $("#editable-sample tbody tr").each(function( index ) {
		    var checkbox = $(this).find('input[type="checkbox"]');
		    if(checkbox.attr("checked")) zone.push($(this).find("td:nth-child(2)").text());
		});
		if(zone.length <=0 ) return false;

	    $.ajax({
	        type: 'POST',
	        url: "ajax/responsabili_zone_serate.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'inserisci-init',
	            zone: zone,
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
					    var zona = $(this).find('td:nth-child(2)').text();
					    if($.inArray(zona, zone) != -1){
					    	$(this).addClass('selected-green');
					    	$(this).find('td:nth-child(3)').text(select.val());
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
      	var zone= new Array();
		$("#editable-sample tbody tr").each(function( index ) {
		    var checkbox = $(this).find('input[type="checkbox"]');
		    if(checkbox.attr("checked")) zone.push($(this).find("td:nth-child(2)").text());
		});
		if(zone.length <=0 ) return false;

	    $.ajax({
	        type: 'POST',
	        url: "ajax/responsabili_zone_serate.ajax.php",
	        dataType: "text",
	        timeout: 20000,
	        data : {
	            operazione: 'disassocia-init',
	            zone: zone
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
					    var zona = $(this).find('td:nth-child(2)').text();
					    if($.inArray( zona, zone) != -1){
					    	$(this).removeClass('selected-green');
					    	$(this).find('td:nth-child(3)').text("");
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