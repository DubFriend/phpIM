
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
            if(joinedConversations.length > 0) {
                ajax(my.ajax_fig({
                    url: my.build_update_url(joinedConversations),
                    type: "GET",
                    success: function (response) {
                        console.log("UPDATE RESPONSE : " + JSON.stringify(response));
                        if(my.isConnected) {
                            update();
                        }
                    }
                }));
            }
            else {
                console.log("No Joined Conversations, No Update.");
                setTimeout(function () {
                    update();
                }, 1000);
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
                console.log("UPDATE RESPONSE : " + JSON.stringify(response));
                var i;
                for(i = 0; i < response.length; i += 1) {
                    availableConversations[response[i].id] = response[i];
                }
            }
        }));
    };

    that.join_conversation = function(id) {
        var conversation = JSON.parse(JSON.stringify(availableConversations[id]));
        if(!is_conversation_joined(id) && conversation) {
            conversation.id = id;
            joinedConversations.push(conversation);
        }
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
;var conversationsManager = new_conversations_manager();

$(document).ready(function () {
    $('#get-available-conversations').click(function () {
        conversationsManager.get_available_conversations();
    });
});
