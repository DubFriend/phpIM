

function addmsg(type, msg){
    /* Simple helper to add a div.
    type is the name of a CSS class (old/new/error).
    msg is the contents of the div */
    $("#messages").append(
        "<div class='msg "+ type +"'>"+ msg +"</div>"
    );
}

function waitForMsg(){
    /* This requests the url "msgsrv.php"
    When it complete (or errors)*/
    $.ajax({
        type: "GET",
        url: "msgsrv.php",

        async: true, /* If set to non-async, browser shows page as "Loading.."*/
        cache: false,
        timeout:50000, /* Timeout in ms */

        success: function(data){ /* called when request to barge.php completes */
            addmsg("new", data); /* Add response to a .msg div (with the "new" class)*/
            setTimeout(
                waitForMsg, /* Request next message */
                1000 /* ..after 1 seconds */
            );
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
            addmsg("error", textStatus + " (" + errorThrown + ")");
            setTimeout(
                waitForMsg, /* Try again after.. */
                15000); /* milliseconds (15seconds) */
        }
    });
};

$(document).ready(function(){
    waitForMsg(); /* Start the inital request */
});