var new_messenger = function (fig, my) {
    fig = fig || {};
    my = my || {};

    var that = new_base_messenger(fig, my),
        ajax = fig.ajax || $.ajax,
        conversationId,
        lastId,

        update = function () {
            if(my.isConnected) {
                console.log("Update Url : " + my.build_update_url([{id: conversationId, last_id: lastId}]) + "\n");
                ajax(my.ajax_fig({
                    url: my.build_update_url([{id: conversationId, last_id: lastId}]),
                    type: "GET",
                    dataType: "text",
                    success: function (response) {
                        console.log("Update Response : " + JSON.stringify(response) + "\n");
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
        console.log("Connect Data : " + JSON.stringify(connectData) + "\n");
        if(!my.isConnected) {
            my.isConnected = true;
            ajax(my.ajax_fig({
                url: ROOT + "conversations",
                type: "POST",
                data: connectData,
                //dataType: "text",
                success: function (response) {
                    console.log("Connect Response : " + JSON.stringify(response) + "\n");
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
            messageData.conversation_id = conversationId;
            my.messageQueue.push(messageData);
            sendMessages = my.messageQueue;
            
            console.log("Send Message Data : " + JSON.stringify(sendMessages) + "\n");

            my.messageQueue = [];
            my.isMessagePending = true;
            
            ajax(my.ajax_fig({
                url: ROOT + "conversations/messages",
                type: "POST",
                //dataType: "text",
                data: {messages: sendMessages},
                success: function (response) {
                    console.log("Send Message Response : " + JSON.stringify(response) + "\n");
                    lastId = response.id;
                    my.isMessagePending = false;
                }
            }));
        }
        else {
            console.log("Could Not send Message\n");
            my.messageQueue.push(messageData);
        }
    };

    return that;
};
