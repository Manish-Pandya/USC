////////////////////////////////////////////////////////////////////////////////
//
//  Copyright(C) 2017 Neighsayer/Harshmellow, Inc.
//  All Rights Reserved.
//
////////////////////////////////////////////////////////////////////////////////
'use strict';

class ViewModelHolder {
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


}