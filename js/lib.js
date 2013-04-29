if(typeof Object.create !== 'function') {
    Object.create = function (o) {
        var F = function () {};
        F.prototype = o;
        return new F();
    };
}

if(typeof Array.map !== 'function') {
    Array.map = function (mapFunction) {
        var i, newArray = [];
        for(i = 0; i < this.length; i += 1) {
            newArray[i] = this[i];
        }
        return newArray;
    };
}
else {
    throw("Array.map is allready implemented.");
}

if(typeof Array.filter !== 'function') {
    Array.filter = function (filterFunction) {
        var i, newArray = [];
        for(i = 0; i < this.length; i += 1) {
            if(filterFunction(this[i])) {
                newArray.push(this[i]);
            }
        }
        return newArray;
    };
}
else {
    throw("Array.filter is allready implemented");
}

if(typeof Array.reduce !== 'function') {
    Array.reduce = function (reduceFunction) {
        var i, acumulation;
        for(i = 0; i < this.length; i += 1) {
            acumulation = reduceFuntion(this[i], acumulation);
        }
        return acumulation;
    };
}
else {
    throw("Array.reduce is allready implemented");
}