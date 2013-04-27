$(document).ready(function () {
    "use strict";

    
    var conversationId = undefined;

    $('#phpIM-form').submit(function (e) {
        e.preventDefault();
        var messageData = get_message_data();
        append_message(messageData);
        send_message(messageData);
    });

    var get_message_data = function () {
        return {
            "id": conversationId,
            "message": $('#phpIM-form [name="message"]').val()
        };
    };

    var append_message = function (message) {
        $('#phpIM-message-area').append(
            "<p>" + JSON.stringify(message) + "</p>"
        );
    };

    //long poll
    var send_message = function (message) {
        var url = conversationId ? ROOT + "conversations/" + conversationId : ROOT + "conversations";
        console.log("SEND DATA : " + JSON.stringify(message));
        $.ajax({
            type: "POST",
            url: url,
            cache: false,
            timeout: 60000, //timeout in milliseconds
            data: message,
            success: function (data) {
                send_message(message);
                console.log("SUCCESS : " + JSON.stringify(data));
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("ERROR : " + textStatus + " : " + errorThrown);
            }
        });
    };

    console.log("foo");
});
