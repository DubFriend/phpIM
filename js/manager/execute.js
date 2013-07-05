var conversationsManager = new_conversations_manager(),
    messengerView = new_messenger_view(),
    chatView = new_chatbox_view(),
    conversationsController = new_conversations_controller({
        conversationsManager: conversationsManager
    });

conversationsManager.subscribe(chatView);
conversationsManager.subscribe(conversationsController);

conversationsManager.connect();

$(document).ready(function () {
    conversationsController.init();
});
