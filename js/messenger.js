var new_messenger = function (fig) {
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

    mixin_observer_publisher(that);

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
