var AJAX_DATA_TYPE = "json",
    AJAX_TIMEOUT_MILLISECONDS = 60000,
    ROOT = "/phpIM/index.php/";
;if(typeof Object.prototype.create !== 'function') {
    Object.create = function (o) {
        var F = function () {};
        F.prototype = o;
        return new F();
    };
}
;var new_messenger = function (fig) {
    fig = fig || {};
    var that = {},
        ajax = fig.ajax || $.ajax,
        messageQueue = [],
        isConnected = false, //set to true immediately after connect called
        conversationId,
        lastId,
        isMessagePending = false,
        numErrors = 0,
        maxErrors = fig.maxErrors || 3,
        updateTimeoutTime = fig.updateTimeoutTime || 0,
        subscribers = [],

        subscribe = function (subscriber) {
            var i;
            subscribers.push(subscriber);
        },

        un_subscribe = function (subscriber) {
            var i;
            for(i = 0; i < subscribers.length; i += 1) {
                if(subscribers[i] === subscriber) {
                    subscribers.splice(i, 1);
                    return true;
                }
            }
            return false;
        },

        publish = function (data) {
            var i;
            for(i = 0; i < subscribers.length; i += 1) {
                subscriber.update(data);
            }
        },

        ajax_fig = function (fig) {
            var i,
                config = {
                    cache: false,
                    timeout: AJAX_TIMEOUT_MILLISECONDS, //milliseconds
                    dataType: AJAX_DATA_TYPE,
                    //dataType: "text",
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("ERROR #" + numErrors + " : " + textStatus + " : " + errorThrown);
                        numErrors += 1;
                        if(numErrors > maxErrors) {
                            isConnected = false;
                        }
                    },
                    complete: function (jqXHR,  textStatus) {
                        console.log("COMPLETE : " + textStatus);
                        isMessageSendPending = false;
                        if(messageQueue.length > 0) {
                            that.send(messageQueue);
                        }
                    }
                };

            for(i in fig) {
                config[i] = fig[i];
            }

            return config;
        },

        update = function () {
            if(isConnected) {
                var url;
                if(lastId) {
                    url = ROOT + "conversations/" + conversationId + "/messages_since/" + lastId;
                }
                else {
                    url = ROOT + "conversations/" + conversationId;
                }
                ajax(ajax_fig({
                    //should be conversations/{conversationId}/messages_since/{lastId}/client
                    url: url,
                    type: "GET",
                    //dataType: "text",
                    success: function (response) {
                        console.log("UPDATE RESPONSE : " + JSON.stringify(response));
                        
                        //publish(response);
                        if(updateTimeoutTime > 0) {
                            setTimeout(update, updateTimeoutTime);
                        }
                        else {
                            update();
                        }
                    },
                    complete: undefined
                }));
            }
        };

    //included to give feedback in unit tests.
    that.is_connected = function () { return isConnected; };
    that.is_message_pending = function () { return isMessagePending; };
    that.message_queue_length = function () { return messageQueue.length; };
    that.id = function () { return conversationId; };

    that.connect = function (connectData) {
        isConnected = true;

        ajax(ajax_fig({
            url: ROOT + "conversations",
            type: "POST",
            data: connectData,
            success: function (response) {
                console.log("CONNECT RESPONSE : " + JSON.stringify(response));
                conversationId = response.id;
                update();
            }
        }));
    };

    that.disconnect = function () {
        isConnected = false;
    };

    that.send_message = function (messageData) {
        var sendMessages;
        if(isConnected && conversationId && !isMessagePending) {
            console.log("Message Sending");
            if(messageQueue.length > 0) {
                messageQueue.push(messageData);
                sendMessages = messageQueue;
            }
            else {
                sendMessages = messageData;
            }

            messageQueue = [];
            isMessagePending = true;
            
            ajax(ajax_fig({
                url: ROOT + "conversations/" + conversationId + "/messages",
                type: "POST",
                //dataType: "text",
                data: sendMessages,
                success: function (response) {
                    lastId = response.id;
                    isMessagePending = false;
                    console.log("MESSAGE RESPONSE : " + JSON.stringify(response));
                }
            }));
        }
        else {
            console.log("Could Not send Message");
            messageQueue.push(messageData);
        }
    };

    return that;
};
;var get_message_data = function () {
        return {
            message: $('#phpIM-send-message [name="message"]').val()
        };
    },

    get_connect_data = function () {
        return {
            username: $('phpIM-connect [name="username"]').val()
        };
    },

    append_message = function (message) {
        $('#phpIM-message-area').append(
            "<p>" + JSON.stringify(message) + "</p>"
        );
    },
    
    messenger = new_messenger();

$(document).ready(function () {

    $('#phpIM-disconnect').click(function (e) {
        e.preventDefault();
        console.log("Disconnect");
        messenger.disconnect();
    });

    $('#phpIM-connect').submit(function (e) {
        e.preventDefault();
        messenger.connect(get_connect_data());
    });

    $('#phpIM-send-message').submit(function (e) {
        e.preventDefault();
        if(messenger.is_connected()) {
            console.log("Send Message : " + JSON.stringify(get_message_data()));
            messenger.send_message(get_message_data());
        }
        else {
            console.log("Cannot send message, connection not established");
        }
    });

});
