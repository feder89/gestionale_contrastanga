function notify_top(message, title){
    var errore = false;
    if(stringStartsWith(message, '#error#')) errore=true;

    $.notify({
        // options
        icon: 'glyphicon glyphicon-'+( errore ? 'remove' : 'ok'),
        message: message.replace('#error#' ,''),
        title: '<b>'+title+'</b><br>'
    },{
        // settings
        mouse_over: 'pause',
        offset: {
            y:5,
            x:5
        },
        type: ( errore ? 'danger' : 'success'),
        animate: {
            enter: 'animated fadeInDown',
            exit: 'animated fadeOutUp'
        },
        'z_index': 1060
    });
}

function stringStartsWith (string, prefix) {
    return string.substring(0, prefix.length) == prefix;
}
