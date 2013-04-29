(function () {
    var messenger,
        ajax,
        ajaxData = [];
    module("messenger", {
        setup: function () {
            ajax = function (fig) {
                ajaxData.push(fig);
            };

            $('#qunit-fixture').append(
                /*'<div id="phpIM-connect">' +
                    '<input type="text" name="username" value="username value"/>' +
                '</div>' +*/
                /*'<div id="phpIM-message">' +
                    '<input type="text" name="message" value="message value"/>' +
                '</div>'*/
            );

            messenger = new_messenger({ajax: ajax});
        }
    });

    test("establish connection", function () {
        messenger.connect({username: "foo"});
        var data = ajaxData.pop();
        deepEqual(data.url, ROOT + "conversations", "url set");
        deepEqual(data.type, "POST", "type set");
        deepEqual(data.data, {username: "foo"}, "username value set");
    });

    test("send message, connection not established", function () {
        messenger.send({message: "foo"});
        var data = ajaxData.pop();
        deepEqual(ajaxData.pop(), undefined, "ajax call not made");
    });

    test("send message, connection established", function () {
        messenger.connect({username: "foo"});
        var connectAjaxData = ajaxData.pop();
        connectAjaxData.success({id: 3});

        messenger.send({message: "bar"});
        var sendAjaxData = ajaxData.pop();
        deepEqual(sendAjaxData.url, ROOT + "conversations/3", "url set");
        deepEqual(sendAjaxData.type, "POST", "type set");
        deepEqual(sendAjaxData.data, {message: "bar"}, "form data set");
    });

}());