var conversationsManager = new_conversations_manager();

$(document).ready(function () {
    $('#get-available-conversations').click(function () {
        conversationsManager.get_available_conversations();
    });
});