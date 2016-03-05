$( document ).ready(function() {
    $( "#server-status" ).load( "/app.php/statusmini" );
    $( "#server-rvrfeed" ).load( "/app.php/statusrvrmini" );
});