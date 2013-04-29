(function () {
    
    module("library");

    test("Object.create(object)", function () {
        var foo = {a: "dataA"},
            bar = Object.create(foo);
        bar.b = "dataB";
        deepEqual(bar, {a: "dataA", b: "dataB"});
    });

    test("Array.map(fn(val){...})", function () {
        var a = [1, 2, 3],
            b = a.map(function (val) { return val * 2; });
        deepEqual(a, [1,2,3], "original array unmutated");
        deepEqual(b, [2,4,6], "map successfull");
    });

    test("Array.filter(fn(val){...})", function () {
        var a = ['a','b','c'],
            b = a.filter(function (val) { return val !== 'b'; });
        deepEqual(a, ['a', 'b', 'c'], "original array unmutated");
        deepEqual(b, ['a', 'c'], "filter successfull");
    });

    test("Array.reduce(fn(val, acc){...})", function () {
        var a = [1,2,3],
            b = a.reduce(function (val, accumulator) {
                return accumulator += val;
            });
        deepEqual(a, [1,2,3], "original array unmutated");
        deepEqual(b, 6, "reduce successfull");
    });

}());
