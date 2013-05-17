(function () {
    var manager, ajax, ajaxData;

    module("conversations manager", {
        setup: function () {
            ajaxData = [];
            ajax = function (fig) {
                ajaxData.push(fig);
            };
            manager = new_conversations_manager({
                ajax: ajax,
                maxErrors: 1
            });
        }
    });

    test("get_available_conversations() : configuration", function () {
        manager.get_available_conversations();
        var data = ajaxData.pop();
        deepEqual(data.url, ROOT + "conversations/live", "url set to conversations/live");
        deepEqual(data.type, "GET", "request method set to GET.");
    });

    test("get_available_conversations() : success response", function () {
        manager.get_available_conversations();
        var data = ajaxData.pop();
        data.success([
            {
                id: "conversation_id_1",
                manager_id: "manager_id_1",
                username: "username_1",
                last_update_check: "last_update_check_1",
                last_id: "last_id_1"
            },
            {
                id: "conversation_id_2",
                manager_id: "manager_id_2",
                username: "username_2",
                last_update_check: "last_update_check_2",
                last_id: "last_id_2"
            }
        ]);

        deepEqual(
            manager.conversations_data(),
            {
                conversation_id_1: {
                    manager_id: "manager_id_1",
                    username: "username_1",
                    last_update_check: "last_update_check_1",
                    last_id: "last_id_1"
                },
                conversation_id_2: {
                    manager_id: "manager_id_2",
                    username: "username_2",
                    last_update_check: "last_update_check_2",
                    last_id: "last_id_2"
                }
            },
            "available conversations are stored"
        );
    });

    test("join_conversation(id)", function () {
        manager.get_available_conversations();
        var data = ajaxData.pop();
        data.success([{id: "foo"}]);
        manager.join_conversation("foo");
        deepEqual(manager.conversations_data(), {},
            "conversation removed from available conversations"
        );
        deepEqual(manager.joined_conversations(), [{id: "foo"}],
            "conversation moved to joined conversations"
        );
    });

    

}());