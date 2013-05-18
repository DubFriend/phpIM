var AJAX_DATA_TYPE = "json",
    AJAX_TIMEOUT_MILLISECONDS = 60000,
    ROOT = "/phpIM/index.php/";
;if(typeof Object.prototype.create !== 'function') {
    Object.create = function (o) {
        var F = function () {};
        F.prototype = o;
        return new F();
    };
}

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
                        console.log("ERROR #" + numErrors + " : " + textStatus + " : " + errorThrown);
                        numErrors += 1;
                        if(numErrors > maxErrors) {
                            my.isConnected = false;
                        }
                    };
                }()),
                complete: function (jqXHR,  textStatus) {
                    console.log("COMPLETE : " + textStatus);
                    //isMessageSendPending = false;
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
                var url;
                if(lastId) {
                    url = ROOT + "conversations/" + conversationId + "/messages_since/" + lastId;
                }
                else {
                    url = ROOT + "conversations/" + conversationId;
                }
                ajax(my.ajax_fig({
                    //conversations/{conversationId}/messages_since/{lastId}/client
                    url: url,
                    type: "GET",
                    //dataType: "text",
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
                url: ROOT + "conversations/" + conversationId + "/messages",
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
;var get_message_data = function () {
        return {
            message: $('#phpIM-send-message [name="message"]').val()
        };
    },

    get_connect_data = function () {
        return {
            username: $('phpIM-connect [name="username"]').val()
        };
    },

    append_message = function (message) {
        $('#phpIM-message-area').append(
            "<p>" + JSON.stringify(message) + "</p>"
        );
    },
    
    messenger = new_messenger();

$(document).ready(function () {

    $('#phpIM-disconnect').click(function (e) {
        e.preventDefault();
        console.log("Disconnect");
        messenger.disconnect();
    });

    $('#phpIM-connect').submit(function (e) {
        e.preventDefault();
        messenger.connect(get_connect_data());
    });

    $('#phpIM-send-message').submit(function (e) {
        e.preventDefault();
        console.log("Send Message : " + JSON.stringify(get_message_data()));
        messenger.send_message(get_message_data());
    });

});
