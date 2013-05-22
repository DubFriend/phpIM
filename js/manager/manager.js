
// - get available conversations
// - subscribe to conversation
// - send message to a conversation
// - get updates for all subscribed conversations

var new_conversations_manager = function (fig, my) {
    fig = fig || {};
    my = my || {};
    var that = new_base_messenger(fig, my),
        ajax = fig.ajax || $.ajax,
        joinedConversations = [],
        availableConversations = {},

        is_conversation_joined = function (id) {
            return false;
        },

        update = function () {
            console.log("Update Url : " + my.build_update_url(joinedConversations));
            if(joinedConversations.length > 0) {
                ajax(my.ajax_fig({
                    url: my.build_update_url(joinedConversations),
                    type: "GET",
                    success: function (response) {
                        console.log("UPDATE RESPONSE : " + JSON.stringify(response));
                        var r, i, conversationId;
                        //update last_id's on available conversations.
                        for(r in response) {
                            if(availableConversations[r]) {
                                availableConversations[r].last_id = response[r][0].id;
                            }
                        }
                        
                        //copy updated last_id's from availableConversations to joinedConversations
                        for(i = 0; i < joinedConversations.length; i += 1) {
                            conversationId = joinedConversations[i].id;
                            if(conversationId) {
                                joinedConversations[i].last_id = availableConversations[conversationId].last_id;
                            }
                        }

                        if(my.isConnected) {
                            update();
                        }
                    }
                }));
            }
            else {
                if(my.isConnected) {
                    console.log("No Joined Conversations, No Update.");
                    setTimeout(function () {
                        update();
                    }, 1000);
                }
            }
        };


    mixin_observer_publisher(that);

    that.connect = function () {
        if(!my.isConnected) {
            my.isConnected = true;
            update();
        }
    };

    that.disconnect = function () {
        my.isConnected = false;
    };

    that.conversations_data = function () {
        return JSON.parse(JSON.stringify(availableConversations));
    };

    that.joined_conversations = function () {
        return JSON.parse(JSON.stringify(joinedConversations));
    };

    that.get_available_conversations = function () {
        ajax(my.ajax_fig({
            url: ROOT + "conversations/live",
            type: "GET",
            success: function (response) {
                var i;
                for(i = 0; i < response.length; i += 1) {
                    availableConversations[response[i].id] = response[i];
                }
                console.log("Available Conversations : " + JSON.stringify(availableConversations));
            }
        }));
    };

    that.join_conversation = function(id) {
        var conversation = JSON.parse(JSON.stringify(availableConversations[id]));
        if(!is_conversation_joined(id) && conversation) {
            conversation.id = id;
            joinedConversations.push(conversation);
        }
        console.log("Joined Conversations : " + JSON.stringify(joinedConversations));
    };

    that.send_message = function (messageData) {
        var sendMessages;
        if(my.isConnected && !my.isMessagePending) {
            console.log("Message Sending");
            if(my.messageQueue.length > 0) {
                my.messageQueue.push(messageData);
                sendMessages = my.messageQueue;
            }
            else {
                sendMessages = messageData;
            }

            my.messageQueue = [];
            my.isMessagePending = true;
            
            ajax(my.ajax_fig({
                url: ROOT + "conversations/messages",
                type: "POST",
                //dataType: "text",
                data: sendMessages,
                success: function (response) {
                    lastId = response.id;
                    my.isMessagePending = false;
                    console.log("MESSAGE RESPONSE : " + JSON.stringify(response));
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
