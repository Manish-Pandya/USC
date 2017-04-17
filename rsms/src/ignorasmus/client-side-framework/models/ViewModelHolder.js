////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2017 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';
var ViewModelHolder = (function () {
    /*public addNeighbor<T extends BaseElement>(baseElement: T) {
        if (baseElement instanceof NodeElement) {
            this.nodeNeighbors[baseElement.id] = baseElement;
        } else if (baseElement instanceof EdgeElement) {
            this.edgeNeighbors[baseElement.id] = baseElement;
        }
    }*/
    //----------------------------------------------------------------------
    //
    //  Constructor
    //
    //----------------------------------------------------------------------
    function ViewModelHolder(data) {
        if (data === void 0) { data = null; }
        //----------------------------------------------------------------------
        //
        //  Properties
        //
        //----------------------------------------------------------------------
        this.data = null;
        this.data = data;
    }
<<<<<<< HEAD:rsms/src/ignorasmus/client-side-framework/models/ViewModelInstance.js
    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------
    ViewModelInstance.prototype.save = function () {
        return DataStoreManager.save(this.data);
    };
    ViewModelInstance.prototype.undo = function () {
        if (Array.isArray(this.data)) {
            this.data.forEach(function (value) {
                DataStoreManager.undo(value);
            });
        }
        else {
            DataStoreManager.undo(this.data);
        }
    };
    return ViewModelInstance;
=======
    return ViewModelHolder;
>>>>>>> ignorasmus:rsms/src/ignorasmus/client-side-framework/models/ViewModelHolder.js
}());
