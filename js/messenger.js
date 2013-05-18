var new_messenger = function (fig, my) {
    fig = fig || {};
    my = my || {};

    var that = new_base_messenger(fig, my),
        ajax = fig.ajax || $.ajax,
        conversationId,
        lastId,

        update = function () {
            if(my.isConnected) {
                
                //if(lastId) {
                //    url = ROOT + "conversations/" + conversationId + "/messages_since/" + lastId;
                //}
                //else {
                //    url = ROOT + "conversations/" + conversationId;
                //}

                console.log("update url : " + my.build_update_url([{id: conversationId, last_id: lastId}]));
                console.log("lastId : " + lastId);
                ajax(my.ajax_fig({
                    //conversations/{conversationId}/messages_since/{lastId}/client
                    url: my.build_update_url([{id: conversationId, last_id: lastId}]),
                    type: "GET",
                    dataType: "text",
                    success: function (response) {
                        console.log("UPDATE RESPONSE : " + JSON.stringify(response));
                        if(my.updateTimeoutTime > 0) {
                            setTimeout(update, my.updateTimeoutTime);
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

    that.get_conversation_id = function () {
        return conversationId;
    };

    that.connect = function (connectData) {
        if(!my.isConnected) {
            my.isConnected = true;
            ajax(my.ajax_fig({
                url: ROOT + "conversations",
                type: "POST",
                data: connectData,
                success: function (response) {
                    console.log("CONNECT RESPONSE : " + JSON.stringify(response));
                    conversationId = response.id;
                    update();
                }
            }));
        }
    };

    that.disconnect = function () {
        my.isConnected = false;
    };


    that.send_message = function (messageData) {
        var sendMessages;
        if(my.isConnected && conversationId && !my.isMessagePending) {
            console.log("Message Sending");
            
            messageData.conversation_id = conversationId;

            my.messageQueue.push(messageData);

            sendMessages = my.messageQueue;
            //if(my.messageQueue.length > 0) {
            //    my.messageQueue.push(messageData);
            //    sendMessages = my.messageQueue;
            //}
            //else {
            //    sendMessages = messageData;
            //}

            console.log("Message Data : " + JSON.stringify(sendMessages));

            my.messageQueue = [];
            my.isMessagePending = true;

            console.log(JSON.stringify(sendMessages));
            
            ajax(my.ajax_fig({
                //url: ROOT + "conversations/" + conversationId + "/messages",
                url: ROOT + "conversations/messages",
                type: "POST",
                //dataType: "text",
                data: {messages: sendMessages},
                success: function (response) {

                    lastId = response.id;
                    my.isMessagePending = false;
                    console.log("MESSAGE RESPONSE : " + JSON.stringify(response));
                    console.log("Message REsponse Id : " + JSON.stringify(response.id));
                }
            }));
        }
        else {
            console.log("Could Not send Message");
            my.messageQueue.push(messageData);
        }
    };

    return that;
};
