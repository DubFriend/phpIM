(function () {
    var messenger, ajax, ajaxData;

    module("messenger", {
        setup: function () {
            ajaxData = [];
            ajax = function (fig) {
                ajaxData.push(fig);
            };
            messenger = new_messenger({
                ajax: ajax,
                maxErrors: 1
            });
        }
    });

//----------------------------------- connect ------------------------------------------------

    test("messenger.connect({connectData})", function () {
        deepEqual(messenger.is_connected(), false, "messenger.is_connected false before connect");
        messenger.connect({username: "bob"});
        deepEqual(messenger.is_connected(), true, "messenger.is_connected true after connect");
        
        var data = ajaxData.pop();
        deepEqual(
            {
                url: data.url,
                type: data.type,
                data: data.data
            },
            {
                url: ROOT + "conversations",
                type: "POST",
                data: {username: "bob"}
            },
            "correct ajax config"
        );

        data.success({id: 4});
        deepEqual(messenger.id(), 4, "response sets id");
    });

    test("messenger.disconnect()", function () {
        messenger.connect();
        deepEqual(messenger.is_connected(), true, "messenger.is_connected true before disconnect");
        messenger.disconnect();
        deepEqual(messenger.is_connected(), false, "messenger.is_connected false after disconnect");
    });


// -------------------------------- update --------------------------------------------
    
    test("update", function () {
        messenger.connect();
        var connectData = ajaxData.pop();
        connectData.success({id: 3});
        var updateData = ajaxData.pop();
        deepEqual(
            {
                url: updateData.url,
                type: updateData.type
            },
            {
                url: ROOT + "conversations/3",
                type: "GET"
            },
            "correct ajax config"
        );

        updateData.success({id: 3});
        var secondUpdateData = ajaxData.pop();
        deepEqual(
            {
                url: secondUpdateData.url,
                type: secondUpdateData.type
            },
            {
                url: ROOT + "conversations/3",
                type: "GET"
            },
            "update called again on update success"
        ); 
    });

    test("update not called if is not connected", function () {
        messenger.connect();
        var data = ajaxData.pop();
        messenger.disconnect();
        data.success({id: 3});
        deepEqual(ajaxData.pop(), undefined, "update not called");
    });

// -------------------------------- send_message ---------------------------------------

    test("messenger.send_message({messageData})", function () {

        messenger.connect();
        ajaxData.pop().success({id: 3});

        deepEqual(
            messenger.is_message_pending(), false,
            "message pending flag is false before message sent"
        );

        messenger.send_message({message: "foo"});
        
        var data = ajaxData.pop();
        
        deepEqual(
            {
                url: data.url,
                type: data.type,
                data: data.data
            },
            {
                url: ROOT + "conversations/3/messages",
                type: "POST",
                data: {message: "foo"}
            },
            "correct ajax config"
        );

        deepEqual(
            messenger.is_message_pending(), true,
            "message pending flag is true after message sent"
        );

        data.success({"id": 4});

        deepEqual(messenger.is_message_pending(), false,
            "message pending flag set to false after response recieved"
        );
    });

    test("messenger.send_message : not connected", function () {
        messenger.send_message({message: "foo"});
        deepEqual(ajaxData.length, 0, "ajax not executed if not connected");
        deepEqual(messenger.message_queue_length(), 1, "message added to the queue");
    });

    test("messenger.send_message : connection started but not complete", function () {
        messenger.connect(); //ajax call 1
        var ajaxDataLength = ajaxData.length;
        messenger.send_message({message: "foo"}); //no ajax, connection not yet complete
        deepEqual(ajaxData.length, ajaxDataLength, "ajax not called");
        deepEqual(messenger.message_queue_length(), 1, "message added to the queue");
    });

    test("messenger.send_message : message allready pending", function () {
        messenger.connect();
        ajaxData.pop().success({id: 3});
        messenger.send_message();
        
        var ajaxDataLength = ajaxData.length;
        messenger.send_message({message: "foo"}); //no ajax, allready pending
        deepEqual(ajaxData.length, ajaxDataLength, "ajax not executed if allready pending");
        
        deepEqual(messenger.message_queue_length(), 1, "message added to message queue");
    });

    test("messenger.send_message : sends queued messages", function () {
        messenger.connect();
        messenger.send_message({message: "queued message"});
        ajaxData.pop().success({id: 1});
        messenger.send_message({message: "send message"});
        var data = ajaxData.pop();
        deepEqual(
            {data: data.data},
            {data: [{message: "queued message"}, {message: "send message"}]},
            "queued messages added to request"
        );
        deepEqual(messenger.message_queue_length(), 0, "message queue reset");
    });

    test("max errors reached", function () {
        messenger.connect();
        var data = ajaxData.pop();
        data.error();
        deepEqual(messenger.is_connected(), true, "still connected after one error");
        data.error();
        deepEqual(messenger.is_connected(), false, "disonnect after maxErrors are reached");
    });

}());
