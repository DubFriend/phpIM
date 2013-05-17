
// - get available conversations
// - subscribe to conversation
// - send message to a conversation
// - get updates for all subscribed conversations


var new_conversations_manager = function (fig) {
    fig = fig || {};
    var that = {},
        ajax = fig.ajax || $.ajax,
        joinedConversations = [],
        availableConversations = {},
        isConnected = false,
        messageQueue = [],

        ajax_fig = function (fig) {
            var i,
                config = {
                    cache: false,
                    timeout: AJAX_TIMEOUT_MILLISECONDS, //milliseconds
                    dataType: AJAX_DATA_TYPE,
                    //dataType: "text",
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("ERROR #" + numErrors + " : " + textStatus + " : " + errorThrown);
                    },
                    complete: function (jqXHR,  textStatus) {
                        console.log("COMPLETE : " + textStatus);
                    }
                };

            for(i in fig) {
                config[i] = fig[i];
            }

            return config;
        },

        is_conversation_joined = function (id) {
            return false;
        },

        // ROOT + conversations/updates/id,lastId/id,lastId/...
        build_update_url = function () {
            var i,
                url = ROOT + "conversations/updates";

            for(i = 0; i < joinedConversations.length; i += 1) {
                url += "/" + (joinedConversations[i].id || "null") + "," +
                             (joinedConversations[i].last_id || "null");
            }
            return url;
        },

        update = function () {
            if(joinedConversations.length > 0) {
                ajax(ajax_fig({
                    url: build_update_url(),
                    type: "GET",
                    success: function (response) {
                        console.log("UPDATE RESPONSE : " + JSON.stringify(response));
                        if(isConnected) {
                            update();
                        }
                    }
                }));
            }
            else {
                setTimeout(function () {
                    update();
                }, 1000);
            }
        };


    mixin_observer_publisher(that);

    that.connect = function () {
        if(!isConnected) {
            isConnected = true;
            update();
        }
    };

    that.disconnect = function () {
        isConnected = false;
    };

    that.conversations_data = function () {
        //this effectively does a deep copy, keeping availableConversations read-only
        return JSON.parse(JSON.stringify(availableConversations));
    };

    that.joined_conversations = function () {
        return JSON.parse(JSON.stringify(joinedConversations));
    };

    that.get_available_conversations = function () {
        ajax(ajax_fig({
            url: ROOT + "conversations/live",
            type: "GET",
            success: function (response) {
                console.log("UPDATE RESPONSE : " + JSON.stringify(response));
                var i;
                for(i = 0; i < response.length; i += 1) {
                    availableConversations[response[i].id] = response[i];
                    delete(response[i].id);
                }
            }
        }));
    };

    that.join_conversation = function(id) {
        if(!is_conversation_joined(id) && availableConversations[id]) {
            availableConversations[id].id = id;
            joinedConversations.push(availableConversations[id]);
            delete(availableConversations[id]);
        }
    };

    that.send_message = function (messageData, conversationId) {
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