

var new_chatbox_view = function () {
    var that = {},
        chatTemplate = '' +
        "<div id='phpIM-conversation-{{conversationId}}'>" +
            "<div class='phpIM-message-area'>" +
            "</div>" +
            
            "<h3>{{conversationId}}</h3>" +

            "<form class='phpIM-send-message'>" +
                "<textarea name='message' placeholder='message'></textarea>" +
                "<input type='submit' value='send'/>" +
            "</form>" +
        "</div>",

        messagesTemplate = '' +
        "{{#messages}}" +
            "<div class='phpIM-message'>" +
                "<p>id : {{id}}</p>" +
                "<p>message : {{message}}</p>" +
                "<p>time stamp : {{time_stamp}}</p>" +
            "</div>" +
        "{{/messages}}";


    that.render_conversation = function (id) {
        console.log("Chatbox Id : " + JSON.stringify(id));
        $('#phpIM-conversations').append(Mustache.render(
            chatTemplate, {conversationId: id}
        ));
    };

    that.render_messages = function (id, messages) {
        console.log("Messages Data : id : " + id + " : messages : " + JSON.stringify(messages));
        $('#phpIM-conversation-' + id).append(Mustache.render(
            messagesTemplate, {messages: messages}
        ));
    };

    that.update = function (data) {
        console.log("Chatbox View Data : " + JSON.stringify(data));
        var id;
        if(data.newConversation) {
            that.render_conversation(data.newConversation.id);
        }
        if(data.messages) {
            var conversationId;
            for(conversationId in data.messages) {
                that.render_messages(conversationId, data.messages[conversationId]);
            }
        }
    };

    return that; 
};



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
                        console.log("UPDATE RESPONSE : " + JSON.stringify(response) + "\n");

                        that.publish({messages:response});
                        
                        var r, i, conversationId, lastResponse;
                        //update last_id's on available conversations.
                        for(r in response) {
                            lastResponse = response[r][response[r].length - 1];
                            if(availableConversations[r] && lastResponse/*response[r][0]*/) {
                                availableConversations[r].last_id = lastResponse.id;//response[r][0].id;
                            }
                        }
                        
                        //copy updated last_id's from availableConversations to joinedConversations
                        //for(i = 0; i < joinedConversations.length; i += 1) {
                        //iterate backwords to avoid reindexing issue with Array.splice()
                        for(i = joinedConversations.length - 1; i >= 0; i -= 1) {
                            conversationId = joinedConversations[i].id;
                            if(conversationId && availableConversations[conversationId]) {
                                joinedConversations[i].last_id = availableConversations[conversationId].last_id;
                            }
                            else if(!availableConversations[conversationId]) {
                                joinedConversations.splice(i, 1);
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
                    console.log("No Joined Conversations, No Update.\n");
                    setTimeout(function () {
                        update();
                    }, MANAGER_CHECK_JOINED_TIMEOUT);
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
                availableConversations = {};
                for(i = 0; i < response.length; i += 1) {
                    availableConversations[response[i].id] = response[i];
                }
                console.log("Available Conversations : " + JSON.stringify(availableConversations) + "\n");
            }
        }));
    };

    that.join_conversation = function(id) {
        var conversation = JSON.parse(JSON.stringify(availableConversations[id]));
        if(!is_conversation_joined(id) && conversation) {
            
            that.publish({
                newConversation: {
                    id: id
                }
            });

            conversation.id = id;
            joinedConversations.push(conversation);
        }
        console.log("Joined Conversations : " + JSON.stringify(joinedConversations) + "\n");
    };

    that.send_message = function (messageData) {
        var sendMessages;
        if(my.isConnected && !my.isMessagePending) {
            console.log("Message Sending\n");
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
                    console.log("Message Response : " + JSON.stringify(response) + "\n");
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
