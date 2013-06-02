var conversationsManager = new_conversations_manager(),
    messengerView = new_messenger_view(),
    chatView = new_chatbox_view(),
    conversationsController = new_conversations_controller({
        conversationsManager: conversationsManager
    });

//conversationsManager.subscribe(messengerView);

conversationsManager.subscribe(chatView);

conversationsManager.connect();

$(document).ready(function () {
    conversationsController.init();
    /*$('#get-available-conversations').click(function () {
        conversationsManager.get_available_conversations();
    });

    $('#join-conversation').click(function () {
        var id = $('#conversation-id').val();
        conversationsManager.get_available_conversations();
        conversationsManager.join_conversation(id);
    });*/
});
