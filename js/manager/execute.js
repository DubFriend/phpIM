var conversationsManager = new_conversations_manager();

conversationsManager.connect();

$(document).ready(function () {
    $('#get-available-conversations').click(function () {
        conversationsManager.get_available_conversations();
    });

    $('#join-conversation').click(function () {
    	var id = $('#conversation-id').val();
    	conversationsManager.get_available_conversations();
    	conversationsManager.join_conversation(id);
    });
});
