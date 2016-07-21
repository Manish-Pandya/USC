////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2016 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
//abstract specifies singleton in ts 1.x (ish)
var InstanceFactory = (function () {
    function InstanceFactory() {
    }
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    InstanceFactory.getClassNames = function (basePath) {
        if (basePath === void 0) { basePath = ""; }
        if (!this._classNames) {
            this._classNames = [];
            var scripts = document.getElementsByTagName('script');
            if (scripts && scripts.length > 0) {
                for (var i in scripts) {
                    if (scripts[i].src && scripts[i].src.indexOf(basePath) > -1) {
                        var pathArray = scripts[i].src.split("/");
                        var className = pathArray.pop().split(".")[0];
                        if (this._classNames.indexOf(className) == -1) {
                            this._classNames.push(className);
                        }
                    }
                }
            }
        }
        return this._classNames;
    };
    InstanceFactory.createInstance = function (className) {
        switch (className) {
            case "User":
                return new User();
            case "PrincipalInvestigator":
                return new PrincipalInvestigator();
            case "Room":
                return new Room();
            default:
                throw new Error("No such class as " + className);
        }
    };
    InstanceFactory.compose = function (type) {
        var instance;
        switch (type) {
            case "realSpecific":
                // junk stuff here
                break;
            default:
                // do stuff to make composit class
                return instance;
        }
    };
    InstanceFactory.copyProperties = function (target) {
        var sources = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            sources[_i - 1] = arguments[_i];
        }
        sources.forEach(function (source) {
            Object.defineProperties(target, Object.getOwnPropertyNames(source).reduce(function (descriptors, key) {
                descriptors[key] = Object.getOwnPropertyDescriptor(source, key);
                /*
                PUMP IN OBSERVERS AT GRANULAR LEVEL HERE BECAUSE IT WILL BE PERFORMANT EVEN IF DAVID DOESN'T THINK WE NEED IT TO BE
                if (!actualModelThing.flatWatcherMap.indexOf(viewModelThing.uid)) {
                        actualModelThing.
                        actualModelThing.Watchers.push();
                }
                */
                return descriptors;
            }, {}));
        });
        return target;
    };
    InstanceFactory.testCopy = function () {
        console.log("A", this.A);
        console.log("B", this.B);
        console.log("C", this.C);
        this.copyProperties(this.A, this.B, this.C);
        console.log("after copy");
        console.log("A", this.A);
        console.log("B", this.B);
        console.log("C", this.C);
        this.C.thing = "C Poot";
        console.log("after setting C to 'C Poot'");
        console.log("A", this.A);
        console.log("B", this.B);
        console.log("C", this.C);
        console.log("after setting A to 'A Poot'");
        this.A.thing = "A Poot";
        console.log("A", this.A);
        console.log("B", this.B);
        console.log("C", this.C);
        console.log("Suck it, fools! It ain't a reference! array.reduce does a shallow copy, at the least. Deep copy test pending.");
    };
    InstanceFactory.affixWatchers = function () {
        var masterPi = new PrincipalInvestigator();
        masterPi.testProperty = "test";
        masterPi.observers = [];
        masterPi.testProperty = "updated";
        var i = 0;
        var childPis = [];
        for (i; i < 10000; i++) {
            childPis[i] = new PrincipalInvestigator();
            masterPi.observers[i] = childPis[i];
        }
        masterPi.watch("testProperty", function (it, oldValue, newValue) {
            console.log(it, oldValue, newValue);
            for (var i = 0; i < masterPi.observers.length; i++) {
                masterPi.observers[i]["testProperty"] = masterPi["testProperty"];
            }
        });
        masterPi.testProperty = "updated";
        console.log(childPis[100].testProperty);
    };
    InstanceFactory.Howdy = "Hello World";
    InstanceFactory.A = { thing: "I'm A" };
    InstanceFactory.B = { thing: "I'm B", butt: "I'm an ass" };
    InstanceFactory.C = { thing: "I'm C" };
    return InstanceFactory;
}());
