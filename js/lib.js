if(typeof Object.prototype.create !== 'function') {
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
