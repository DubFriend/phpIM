var new_chatbox_view = function () {

    var that = {},
        chatTemplate = '' +
        "<div id='phpIM-conversation-{{id}}'>" +
            "<p class='small'><b>id: </b>{{id}}<p>" +
            "<div class='phpIM-message-area well'></div>" +
            "<form class='phpIM-send-message'>" +
                "<fieldset>" +
                    "<div>" +
                        "<textarea " +
                            "name='message' " +
                            "placeholder='Your message here.'>" +
                        "</textarea>" +
                    "</div>" +
                    "<input " +
                        "type='submit' " +
                        "class='btn btn-primary' " +
                        "value='Send'" +
                    "/>" +
                "</fieldset>" +
            "</form>" +
        "</div>",

        messagesTemplate = '' +
        "{{#messages}}" +
            "<div class='phpIM-message'>" +
                "<p class='message'>" +
                    "<b>{{username}} </b>" +
                    "{{message}}" +
                    "<span class='time-stamp'>{{time_stamp}}</span>" +
                "</p>" +
            "</div>" +
        "{{/messages}}",

        availableTemplate = '' +
        '{{#available}}' +
            '<div class="phpIM-available well">' +
                '<p class="small"><b>id:</b>{{id}}</p>' +
                '<p><b>username:</b> {{username}}</p>' +
                //'<p>last_update_check : {{last_update_check}}</p>' +
                '<button id="phpIM-join-{{id}}" class="btn btn-info">' +
                    'Join' +
                '</button>' +
            '</div>' +
        '{{/available}}';

        render_conversation = function (data) {
            console.log("Chatbox Id : " + JSON.stringify(data));
            $('#phpIM-conversations').append(
                Mustache.render(chatTemplate, data)
            );
        },

        render_messages = function (id, messages) {
            console.log("Messages Data : id : " + id + " : messages : " + JSON.stringify(messages));
            var $messageBox = $('#phpIM-conversation-' + id + " .phpIM-message-area");
            $messageBox.append(Mustache.render(
                messagesTemplate, {messages: messages}
            ));
            $messageBox.scrollTop($messageBox.prop('scrollHeight'));
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
            render_conversation(data.newConversation);
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

        if(data.username) {
            $('#username-label').html(data.username);
        }
    };

    return that;
};

var new_conversations_controller = function (fig) {
    var that = mixin_observer_publisher({}),
        conversationsManager = fig.conversationsManager,
        username,

        //note: needs server side implentation
        get_message_data = function (id) {
            return {
                conversation_id: id,
                message: $('#phpIM-conversation-' + id + ' [name="message"]').val(),
                username: username
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

            $('#phpIM-set-username').click(function (e) {
                e.preventDefault();
                username = $('#phpIM-username').val();
                that.publish({username: username});
            });

            $('#phpIM-start-conversation').click(function (e) {
                e.preventDefault();
                if(username) {
                    conversationsManager.start_conversation({ username: username });
                }
                else {
                    alert("you must set your username.");
                }
            });
        };

        that.update = function (data) {
            if(data.availableConversations) {
                var i, available = object_values(data.availableConversations);
                for(i = 0; i < available.length; i += 1) {
                    (function (i) {
                        var id = available[i].id;
                        $('#phpIM-join-' + id).click(function () {
                            if(username) {
                                conversationsManager.join_conversation(id);
                            }
                            else {
                                alert("you must set your username.");
                            }
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
                            if(availableConversations[r] && lastResponse) {
                                availableConversations[r].last_id = lastResponse.id;
                            }
                        }

                        //copy updated last_id's from availableConversations to joinedConversations
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
            }
        }));
    };

    that.join_conversation = function(id) {
        var conversation = JSON.parse(JSON.stringify(availableConversations[id]));
        if(!is_conversation_joined(id) && conversation) {
            conversation.id = id;
            joinedConversations.push(conversation);
            that.publish({newConversation: conversation});
        }
        console.log("Joined Conversations : " + JSON.stringify(joinedConversations) + "\n");
    };

    that.send_message = function (messageData) {
        var sendMessages,
            publishFormat = { messages: {} };

        //optimistically display message to user right away.
        //possibly inconsistent data, but gives better user experience.
        publishFormat.messages[messageData.conversation_id] = [{
            message: messageData.message,
            username: messageData.username
        }];

        that.publish(publishFormat);

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
                data: {messages: sendMessages},
                //dataType: "text",
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
