/*
var new_message = function (fig) {
    var that = {},
        ;

    return that;
};

var new_conversation = function (fig) {
    var that = {},
        id = fig.id,
        messages = [];

    that.get_id = function () {
        return id;
    };

    that.add_message

    return that;
};
*/


// - get available conversations
// - subscribe to conversation
// - send message to a conversation
// - get updates for all subscribed conversations


var new_conversations_manager = function (fig) {
    fig = fig || {};
    var that = {},
        ajax = fig.ajax || $.ajax,
        conversationRequests = [],
        activeConversations = [],

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
        };

    mixin_observer_publisher(that);

    that.get_available_conversations = function () {
        ajax(ajax_fig({
            url: ROOT + "conversations/live",
            type: "GET",
            //dataType: "text",
            success: function (response) {
                console.log("UPDATE RESPONSE : " + JSON.stringify(response));
            }
        }));
    };

    return that;
};;var conversationsManager = new_conversations_manager();


$('#get-available-conversations').click(function () {
	conversationsManager.get_available_conversations();
});