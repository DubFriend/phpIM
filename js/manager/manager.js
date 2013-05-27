
// - get available conversations
// - subscribe to conversation
// - send message to a conversation
// - get updates for all subscribed conversations



/*
UPDATE RESPONSE : {"b877e19894bb1155cb3e653019ec38fef9eba398db396fca3eca9fe57af4ab660":[{"id":"171","message":"BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB","time_stamp":null}]}
 manager.js:26
Update Url : /phpIM/index.php/conversations/updates/[
    {
        "id":"34b0f42bc0559f3a479a09dbdead8483da55e72dbc5188eb7bd706dfc407f8759",
        "manager_id":null,
        "username":"",
        "last_update_check":"2013-05-26 21:54:49",
        "last_id":"170"
    },
    {
        "id":"b877e19894bb1155cb3e653019ec38fef9eba398db396fca3eca9fe57af4ab660",
        "manager_id":null,
        "username":"",
        "last_update_check":"2013-05-26 21:55:18",
        "last_id":"171"
    },
    {
        "id":"315cffe78ce57f11ec7ddad0b09df5a7d165a41b0c5abc4b3c419ed9866452ba6",
        "manager_id":null,
        "username":"",
        "last_update_check":"2013-05-26 21:56:44",
        "last_id":null
    }
]   manager.js:20

COMPLETE : success lib.js:68
*/




/*
Update Url : /phpIM/index.php/conversations/updates/[
    {
        "id":"84930eb427aaae5fed909e7a75f3c760b91b6d8419f7fc317552d2e39c8a8703d",
        "manager_id":null,
        "username":"",
        "last_update_check":"2013-05-26 22:22:11",
        "last_id":"179"
    },
    {
        "id":"0f36b480a1c6565fcf04c21fa1aca1aa1a38e939b558311b38c180eeaf555b2a3",
        "manager_id":null,
        "username":"",
        "last_update_check":"2013-05-26 22:22:49",
        "last_id":null
    }
] manager.js:53

COMPLETE : success lib.js:68

UPDATE RESPONSE : {
    "0f36b480a1c6565fcf04c21fa1aca1aa1a38e939b558311b38c180eeaf555b2a3":[
        {
            "id":"180",
            "message":"AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA",
            "time_stamp":null
        }
    ],

    //why so many messages?
    "84930eb427aaae5fed909e7a75f3c760b91b6d8419f7fc317552d2e39c8a8703d":[
        {
            "id":"175",
            "message":"BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB",
            "time_stamp":null
        },
        {
            "id":"176",
            "message":"BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB",
            "time_stamp":null
        },
        {
            "id":"177",
            "message":"BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB",
            "time_stamp":null
        },
        {
            "id":"178",
            "message":"BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB",
            "time_stamp":null
        },
        {
            "id":"179",
            "message":"BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB",
            "time_stamp":null
        }
    ]
} manager.js:59


//endless repeat here ...

Update Url : /phpIM/index.php/conversations/updates/[
    {
        "id":"84930eb427aaae5fed909e7a75f3c760b91b6d8419f7fc317552d2e39c8a8703d",
        "manager_id":null,
        "username":"",
        "last_update_check":"2013-05-26 22:22:11",
        "last_id":"175"
    },
    {
        "id":"0f36b480a1c6565fcf04c21fa1aca1aa1a38e939b558311b38c180eeaf555b2a3",
        "manager_id":null,
        "username":"",
        "last_update_check":"2013-05-26 22:22:49",
        "last_id":"180"
    }
] manager.js:53

COMPLETE : success lib.js:68

UPDATE RESPONSE : {
    "0f36b480a1c6565fcf04c21fa1aca1aa1a38e939b558311b38c180eeaf555b2a3":[
        {
            "id":"180",
            "message":"AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA",
            "time_stamp":null
        }
    ]
} manager.js:59

Update Url : /phpIM/index.php/conversations/updates/[
    {
        "id":"84930eb427aaae5fed909e7a75f3c760b91b6d8419f7fc317552d2e39c8a8703d",
        "manager_id":null,
        "username":"",
        "last_update_check":"2013-05-26 22:22:11",
        "last_id":"175"
    },
    {
        "id":"0f36b480a1c6565fcf04c21fa1aca1aa1a38e939b558311b38c180eeaf555b2a3",
        "manager_id":null,
        "username":"",
        "last_update_check":"2013-05-26 22:22:49",
        "last_id":"180"
    }
] manager.js:53

COMPLETE : success lib.js:68

UPDATE RESPONSE : {"0f36b480a1c6565fcf04c21fa1aca1aa1a38e939b558311b38c180eeaf555b2a3":[{"id":"180","message":"AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA","time_stamp":null}]}
 manager.js:59
Update Url : /phpIM/index.php/conversations/updates/[{"id":"84930eb427aaae5fed909e7a75f3c760b91b6d8419f7fc317552d2e39c8a8703d","manager_id":null,"username":"","last_update_check":"2013-05-26 22:22:11","last_id":"175"},{"id":"0f36b480a1c6565fcf04c21fa1aca1aa1a38e939b558311b38c180eeaf555b2a3","manager_id":null,"username":"","last_update_check":"2013-05-26 22:22:49","last_id":"180"}] manager.js:53
COMPLETE : success
 lib.js:68
UPDATE RESPONSE : {"0f36b480a1c6565fcf04c21fa1aca1aa1a38e939b558311b38c180eeaf555b2a3":[{"id":"180","message":"AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA","time_stamp":null}]}


*/


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
