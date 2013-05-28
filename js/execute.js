var get_message_data = function () {
        return { message: $('#phpIM-send-message [name="message"]').val() };
    },

    get_connect_data = function () {
        return { username: $('#phpIM-connect [name="username"]').val() };
    },

    append_message = function (message) {
        $('#phpIM-message-area').append("<p>" + JSON.stringify(message) + "</p>");
    },
    
    messenger = new_messenger();

    var messageView = new_messenger_view();
    
    messenger.subscribe(messageView);

    messageView.update({ 
        messages: {
            "conversation_id":[{
                id:"test_id",
                message: "test message",
                time_stamp: "test_time_stamp"
            }]
        }
    });

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
