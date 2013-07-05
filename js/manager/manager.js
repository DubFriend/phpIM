var new_chatbox_view = function () {
    var that = {},
        chatTemplate = '' +
        "<div id='phpIM-conversation-{{conversationId}}'>" +
            "<div class='phpIM-message-area'>" +
            "</div>" +

            "<h3>{{conversationId}}</h3>" +

            "<form class='phpIM-send-message'>" +
                "<textarea name='message' placeholder='message'></textarea>" +
                "<input type='submit' class='btn btn-primary' value='send'/>" +
            "</form>" +
        "</div>",

        messagesTemplate = '' +
        "{{#messages}}" +
            "<div class='phpIM-message'>" +
                "<p>id : {{id}}</p>" +
                "<p>message : {{message}}</p>" +
                "<p>time stamp : {{time_stamp}}</p>" +
            "</div>" +
        "{{/messages}}",

        availableTemplate = '' +
        '{{#available}}' +
            '<div class="phpIM-available">' +
                '<p>id : {{id}}</p>' +
                '<p>username : {{username}}</p>' +
                '<p>last_update_check : {{last_update_check}}</p>' +
                '<button id="phpIM-join-{{id}}" class="btn btn-small btn-info">' +
                    'Join' +
                '</button>' +
            '</div>' +
        '{{/available}}';

        render_conversation = function (id) {
            console.log("Chatbox Id : " + JSON.stringify(id));
            $('#phpIM-conversations').append(Mustache.render(
                chatTemplate, {conversationId: id}
            ));
        },

        render_messages = function (id, messages) {
            console.log("Messages Data : id : " + id + " : messages : " + JSON.stringify(messages));
            $('#phpIM-conversation-' + id).append(Mustache.render(
                messagesTemplate, {messages: messages}
            ));
        };

        render_available = function (conversations) {
            console.log("Available Data : " + JSON.stringify(conversations));
            $('#phpIM-available').html(Mustache.render(
                availableTemplate, {available: conversations}
            ));
        };

    that.update = function (data) {
        console.log("Chatbox View Data : " + JSON.stringify(data));
        if(data.newConversation) {
            render_conversation(data.newConversation.id);
        }
        if(data.messages && data.messages instanceof Object) {
            var conversationId;
            for(conversationId in data.messages) {
                if(data.messages[conversationId] instanceof Array) {
                    render_messages(conversationId, data.messages[conversationId]);
                }
                else {
                    console.log("Update Message Data : " + JSON.stringify(data.messages[conversationId]));
                }
            }
        }
        if(data.availableConversations) {
            render_available(object_values(data.availableConversations));
        }
    };

    return that;
};

var new_conversations_controller = function (fig) {
    var that = {},
        conversationsManager = fig.conversationsManager,

        //note: needs server side implentation
        get_message_data = function (id) {
            return {
                conversation_id: id,
                message: $('#phpIM-conversation-' + id + ' [name="message"]').val()
            };
        },

        bind_conversation = function (id) {
            console.log("Bind Conversation id : " + id);
            $('#phpIM-conversation-' + id + " form.phpIM-send-message").submit(function (e) {
                e.preventDefault();
                conversationsManager.send_message(get_message_data(id));
            });
        };

        that.init = function () {
            $('#get-available-conversations').click(function (e) {
                e.preventDefault();
                conversationsManager.get_available_conversations();
            });

            $('#phpIM-start-conversation').click(function (e) {
                e.preventDefault();
                conversationsManager.start_conversation(
                    { username: $('#phpIM-username').val() }
                );
            });
            /*
            $('#join-conversation').click(function () {
                var id = $('#conversation-id').val();
                conversationsManager.get_available_conversations();
                conversationsManager.join_conversation(id);
                bind_conversation(id);
            });
            */
        };

        that.update = function (data) {
            if(data.availableConversations) {
                var i, available = object_values(data.availableConversations);
                for(i = 0; i < available.length; i += 1) {
                    (function (i) {
                        var id = available[i].id;
                        $('#phpIM-join-' + id).click(function () {
                            conversationsManager.join_conversation(id);
                            bind_conversation(id);
                        });
                    }(i));
                }
            }
        };

    return that;
};


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
                that.publish({ availableConversations: availableConversations });
                console.log("Available Conversations : " + JSON.stringify(availableConversations) + "\n");
            }
        }));
    };

    that.start_conversation = function (connectData) {
        ajax(my.ajax_fig({
            url: ROOT + "conversations",
            type: "POST",
            data: connectData,
            success: function (response) {
                console.log("Connect Response : " + JSON.stringify(response) + "\n");
                that.get_available_conversations();
                //that.join_conversation(response.id);
            }
        }));
    };

/*
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
*/
    that.join_conversation = function(id) {
        var conversation = JSON.parse(JSON.stringify(availableConversations[id]));
        if(!is_conversation_joined(id) && conversation) {
            that.publish({ newConversation: { id: id } });
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
                sendMessages = [messageData];
            }

            my.messageQueue = [];
            my.isMessagePending = true;

            ajax(my.ajax_fig({
                url: ROOT + "conversations/messages",
                type: "POST",
                //dataType: "text",
                data: {messages: sendMessages},
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
