//NOTE take care to limit two simultaneous connections (long polling is holding up one)
var new_messenger = function (fig) {
    fig = fig || {};
    var that = {},
        ajax = fig.ajax || $.ajax,
        isActive = false,
        isMessageSendPending = false,
        conversationId = undefined,
        numErrors = 0,
        //conversation = [],
        messageQueue = [],
        ajax_fig = function (fig) {
            var i,
                config = {
                    cache: false,
                    timeout: 60000, //milliseconds
                    dataType: "text",
                    //dataType: "json",
                    beforeSend: function () {
                        console.log("BEFORE SEND");
                        messageQueue = [];
                        isMessageSendPending = true;
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("ERROR #" + numErrors + " : " + textStatus + " : " + errorThrown);
                        numErrors += 1;
                        if(numErrors > 2) {
                            isActive = false;
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
            console.log("UPDATE");
            if(isActive) {
                ajax(ajax_fig({
                    url: ROOT + "conversations/" + conversationId,
                    type: "GET",
                    beforeSend: function () {},//dont clear message queue on the long-poll connection
                    complete: function () {},
                    success: function (response) {
                        console.log("UPDATE RESPONSE : " + JSON.stringify(response));
                        setTimeout(update, 3000);
                    }
                }));
            }
        };

    that.connect = function (connectData) {
        console.log("CONNECT : " + JSON.stringify(connectData));
        if(!isActive) {
            isActive = true;
            ajax(ajax_fig({
                url: ROOT + "conversations",
                type: "POST",
                data: connectData,
                success: function (response) {
                    console.log("CONNECT RESPONSE : " + JSON.stringify(response));
                    conversationId = 4;//response.id;
                    update();
                }
            }));
        }
    };

    that.disconnect = function () {
        console.log("DISCONNECT");
        isActive = false;
    };

    that.is_connected = function () {
        return isActive;
    };

    that.send = function (messageData) {
        console.log("SEND : " + JSON.stringify(messageData));
        if(isActive && conversationId && isMessageSendPending === false) {
            ajax(ajax_fig({
                url: ROOT + "conversations/" + conversationId,
                type: "POST",
                data: messageData,
                success: function (response) {
                    console.log("SEND RESPONSE : " + JSON.stringify(response));
                }
            }));
        }
        else {
            messageQueue.push(messageData);
        }
    };

    return that;
};
