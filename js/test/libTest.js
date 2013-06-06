(function () {

    module("library");

    test("is_array", function () {
        deepEqual(is_array([]), true, "is array");
        deepEqual(is_array({}), false, "object, not array");
        deepEqual(is_array(), false, "no arguments passed, not array");
    });

    test("array_last", function () {
        deepEqual(array_last([1,2,3]), 3, "normal use");
        deepEqual(array_last([1]), 1, "returns element from array of length 1");
        deepEqual(array_last([], undefined, "empty array returns undefined"));
        raises(function () {
            array_last({});
        }, "not an array", "throws exception if not passed an array");
    });

}());
