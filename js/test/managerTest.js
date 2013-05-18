(function () {
    var manager, ajax, ajaxData, my;

    module("conversations manager", {
        setup: function () {
            ajaxData = [];
            my = {};
            ajax = function (fig) {
                ajaxData.push(fig);
            };
            manager = new_conversations_manager({
                ajax: ajax,
                maxErrors: 1
            }, my);
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
                    id: "conversation_id_1",
                    manager_id: "manager_id_1",
                    username: "username_1",
                    last_update_check: "last_update_check_1",
                    last_id: "last_id_1"
                },
                conversation_id_2: {
                    id: "conversation_id_2",
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
        data.success([{id: "foo", other: "bar"}]);
        manager.join_conversation("foo");
        //deepEqual(manager.conversations_data(), {foo: {id: "foo", other:"bar"}},
        //    "conversation removed from available conversations"
        //);
        deepEqual(manager.joined_conversations(), [{id: "foo", other: "bar"}],
            "conversation moved to joined conversations"
        );
    });

    test("connect()", function () {
        manager.get_available_conversations();
        var conversations = [{id: "id", last_id: "last_id"}, {id: "id2", last_id: "last_id2"}];
        ajaxData.pop().success(conversations);
        manager.join_conversation("id");
        manager.join_conversation("id2");

        manager.connect();
        var data = ajaxData.pop();
        deepEqual(data.url, ROOT + "conversations/updates/" + JSON.stringify(conversations), "url is set");
        deepEqual(data.type, "GET", "http method type is set");

        data.success({data: "response_data"});
        var secondData = ajaxData.pop();
        ok(secondData, "update is called recursively on success response");
    });

    test("send_message(messageData)", function () {
        manager.connect();
        var messageData = {conversation_id: "foo", data: "bar"};
        manager.send_message(messageData);
        var data = ajaxData.pop();
        deepEqual(data.url, ROOT + "conversations/messages", "url set");
        deepEqual(data.type, "POST", "http method type set");
        deepEqual(data.data, messageData, "post data is set");
        deepEqual(my.isMessagePending, true, "messagePending flag set to true");
    });

}());