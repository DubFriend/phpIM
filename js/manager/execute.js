var conversationsManager = new_conversations_manager();


$('#get-available-conversations').click(function () {
	conversationsManager.get_available_conversations();
});