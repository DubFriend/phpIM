var get_message_data = function () {
        return {
            message: $('#phpIM-send-message [name="message"]').val()
        };
    },

    get_connect_data = function () {
        return {
            username: $('phpIM-connect [name="username"]').val()
        }
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
        messenger.disconnect();
    })

    $('#phpIM-connect').submit(function (e) {
        e.preventDefault();
        messenger.connect(get_connect_data());
    });

    $('#phpIM-send-message').submit(function (e) {
        e.preventDefault();
        if(messenger.is_connected()) {
            messenger.send(get_message_data());
        }
        else {
            console.log("Cannot send message, connection not established");
        }
    });

});