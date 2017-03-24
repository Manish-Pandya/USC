////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2017 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

class ViewModelInstance {
    //----------------------------------------------------------------------
    //
    //  Properties
    //
    //----------------------------------------------------------------------

    data: FluxCompositerBase | FluxCompositerBase[] = null;

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
    
    constructor(data: FluxCompositerBase | FluxCompositerBase[] = null) {
        this.data = data;
    }

    //----------------------------------------------------------------------
    //
    //  Methods
    //
    //----------------------------------------------------------------------

    save(): Promise<FluxCompositerBase> | Promise<FluxCompositerBase[]> {
        return DataStoreManager.save(this.data);
    }

    undo(): void {
        if (Array.isArray(this.data)) {
            this.data.forEach((value) => {
                DataStoreManager.undo(value);
            })
        } else {
            DataStoreManager.undo(this.data);
        }
    }

}