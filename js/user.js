//NOTE take care to limit two simultaneous connections (long polling is holding up one)
var new_send_chat_message = function (fig) {
    var fig = fig || {},
        ajax = fig.ajax || $.ajax,
        conversationId = undefined,
        isConnectionActive = false,
        isMessageSendPending = false,
        numErrors = 0,
        messageQueue = [],

        ajax_fig = function (fig) {
            var i,
                config = {
                    cache: false,
                    timeout: 60000, //milliseconds
                    dataType: "text",
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        numErrors += 1;
                        console.log("Error #" + numErrors + " : " + textStatus + " : " + errorThrown);
                    }
                };

            for(i in fig) {
                config[i] = fig[i];
            }

            return config;
        },

        first_message = function (messageData) {
            isConnectionActive = true;
            
            ajax(ajax_fig({
                url: ROOT + "conversations",
                type: "POST",
                data: messageData,
                success: function (response) {
                    console.log("FIRST MESSAGE RESPONSE : " + JSON.stringify(response));
                    conversationId = response.id;
                    if(messageQueue.length > 0) {
                        established_message(messageQueue);
                        messageQueue = [];
                    }
                    if(isConnectionActive) {
                        update_request();
                    }
                }
            }));
        },
        
        //long polling request
        update_request = function () {
            ajax(ajax_fig({
                url: ROOT + "conversations/" + conversationId,
                type: "GET",
                success: function (response) {
                    console.log("UPDATE RESPONSE : " + JSON.stringify(response));
                    if(isConnectionActive) {
                        update_request();
                    }
                }
            }));
        },

        established_message = function (messageData) {
            ajax(ajax_fig({
                url: ROOT + "conversations/" + conversationId,
                type: "POST",
                data: messageData,
                success: function (response) {
                    console.log("ESTABLISHED MESSAGE RESPONSE : " + JSON.stringify(response));
                    if(messageQueue.length > 0) {
                        established_message(messageQueue);
                        messageQueue = [];
                    }
                }
            }));
        },

        send_message = function (messageData) {
            messageQueue.push(messageData);
            if(conversationId !== undefined) {
                if(!isMessageSendPending) {
                    established_message(messageQueue);
                    messageQueue = [];
                }
            }
            else if (!isConnectionActive) {
                first_message(messageQueue);
                messageQueue = [];
            }
        };

    return send_message;
};

var send_message = new_send_chat_message();

send_message({message: "message..."});

/*
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

    //TODO : need to deal with multiple requests, exiting, post vs. get
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
*/