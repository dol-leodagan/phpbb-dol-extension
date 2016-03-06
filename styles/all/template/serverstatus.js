$( document ).ready(function() {
    $( "#server-status" ).load( "/app.php/status/mini" );
    $( "#server-rvrfeed" ).load( "/app.php/status/rvrmini" );
});