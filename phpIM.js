var AJAX_DATA_TYPE = "json",
    AJAX_TIMEOUT_MILLISECONDS = 60000,
    ROOT = "/phpIM/index.php/",
    MANAGER_CHECK_JOINED_TIMEOUT = 10000;
;if(typeof Object.prototype.create !== 'function') {
    Object.create = function (o) {
        var F = function () {};
        F.prototype = o;
        return new F();
    };
}

//instanceof doesnt work in iframes.  This is apparently better.
//http://perfectionkills.com/instanceof-considered-harmful-or-how-to-write-a-robust-isarray/
var is_array = function (o) {
    return Object.prototype.toString.call(o) === '[object Array]';
};

//returns the last element from a numerical array
var array_last = function (array) {
    if(is_array(array)) {
        if(array.length > 0) {
            return array[array.length - 1];
        }
        else {
            return undefined;
        }
    }
    else {
        throw "not an array";
    }
};

//gives passed object a publishers observer pattern
var mixin_observer_publisher = function (object) {
    var subscribers = [];

    object.subscribe = function (subscriber) {
        var i;
        subscribers.push(subscriber);
    };

    object.unsubscribe = function (subscriber) {
        var i;
        for(i = 0; i < subscribers.length; i += 1) {
            if(subscribers[i] === subscriber) {
                subscribers.splice(i, 1);
                return true;
            }
        }
        return false;
    };

    object.publish = function (data) {
        var i;
        for(i = 0; i < subscribers.length; i += 1) {
            subscribers[i].update(data);
        }
    };

    return object;
};

//base for messenger and manager classes.
var new_base_messenger = function (fig, my) {
    var that = {};
    
    my.isConnected = false;
    my.messageQueue = [];
    my.isMessagePending = false;
    my.updateTimeoutTime = fig.updateTimeoutTime || 0;

    my.ajax_fig = function (ajaxFig) {
        var i,
            config = {
                cache: false,
                timeout: AJAX_TIMEOUT_MILLISECONDS, //milliseconds
                dataType: AJAX_DATA_TYPE,
                //dataType: "text",
                error: (function () {
                    var numErrors = 0,
                        maxErrors = fig.maxErrors || 3;

                    return function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("ERROR #" + numErrors + " : " + textStatus + " : " + errorThrown + "\n");
                        numErrors += 1;
                        if(numErrors > maxErrors) {
                            my.isConnected = false;
                        }
                    };
                }()),
                complete: function (jqXHR,  textStatus) {
                    console.log("COMPLETE : " + textStatus + "\n");
                    if(my.messageQueue.length > 0) {
                        that.send(my.messageQueue);
                    }
                }
            };

        for(i in ajaxFig) {
            config[i] = ajaxFig[i];
        }

        return config;
    };

    my.build_update_url = function (conversations) {
        return ROOT + "conversations/updates/" + JSON.stringify(conversations);
    };

    return that;
};


var new_messenger_view = function () {
    var that = {},
        messageTemplate = '' +
        '<h3>{{conversationId}}</h3>' +
        '{{#messages}}' +
        '<div class="message">' +
            '<p>{{message}}</p>' +
            '<p>{{id}}</p>' +
            '<p>{{time_stamp}}</p>' +
        '</div>' +
        '{{/messages}}';

    that.update = function (data) {
        console.log("View Data : " + JSON.stringify(data));

        var messages = data.messages,
            conversationId;

        if(messages) {
            //TODO, handle multiple conversation areas.
            for(conversationId in messages) {
                if(messages[conversationId] instanceof Array) {
                    $('#phpIM-message-area').append(Mustache.render(
                        messageTemplate, 
                        {
                            "conversationId": conversationId,
                            "messages": messages[conversationId]
                        }
                    ));
                }
            }
        }
    };

    return that;
};
;var new_messenger = function (fig, my) {
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
                    //dataType: "text",
                    success: function (response) {
                        console.log("Update Response : " + JSON.stringify(response) + "\n");
                        that.publish({messages: response});

                        if(response[conversationId]) {
                            lastId = array_last(response[conversationId]).id;
                        }

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
;var get_message_data = function () {
        return { message: $('#phpIM-send-message [name="message"]').val() };
    },

    get_connect_data = function () {
        return { username: $('#phpIM-connect [name="username"]').val() };
    },

    append_message = function (message) {
        $('#phpIM-message-area').append("<p>" + JSON.stringify(message) + "</p>");
    },
    
    messenger = new_messenger(),

    messageView = new_messenger_view();
    

messenger.subscribe(messageView);

$(document).ready(function () {
    $('#phpIM-disconnect').click(function (e) {
        e.preventDefault();
        console.log("Disconnect\n");
        messenger.disconnect();
    });

    $('#phpIM-connect').submit(function (e) {
        e.preventDefault();
        messenger.connect(get_connect_data());
    });

    $('#phpIM-send-message').submit(function (e) {
        e.preventDefault();
        console.log("Send Message : " + JSON.stringify(get_message_data()) + "\n");
        messenger.send_message(get_message_data());
    });
});
