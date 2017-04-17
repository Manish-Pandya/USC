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