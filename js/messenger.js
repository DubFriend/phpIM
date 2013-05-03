var new_messenger = function (fig) {
    fig = fig || {};
    var that = {},
        ajax = fig.ajax || $.ajax,
        messageQueue = [],
        isConnected = false, //set to true immediately after connect called
        conversationId,
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
                ajax(ajax_fig({
                    url: ROOT + "conversations/" + conversationId,
                    type: "GET",
                    success: function (response) {
                        console.log("CONNECT RESPONSE : " + JSON.stringify(response));
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
                url: ROOT + "conversations/" + conversationId,
                type: "POST",
                data: sendMessages,
                success: function (response) {
                    isMessagePending = false;
                    console.log("MESSAGE RESPONSE : " + JSON.stringify(response));
                }
            }));
        }
        else {
            messageQueue.push(messageData);
        }
    };

    return that;
};
