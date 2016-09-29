abstract class FluxCompositerBase {
    static urlMapping: UrlMapping = new UrlMapping("foot", "", "");

    UID: number;
    TypeName: string;
    viewModelWatcher: FluxCompositerBase | null;

    private thisClass: Function;

    constructor() {
        if (!FluxCompositerBase.urlMapping) {
            console.log( new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server") );
        }
        this.thisClass = (<any>this).constructor;
        for (var instanceProp in this.thisClass) {
            if (this.thisClass[instanceProp] instanceof CompositionMapping) {
                this.thisClass[instanceProp].flagGetAll();
            }
        }
    }

    onFulfill(callback: Function = null, ...args): Function | void {
        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }
        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.TypeName = this[DataStoreManager.classPropName];
        }
        
        return callback ? callback(args) : null;
    }

    doCompose(compMaps: CompositionMapping[] | boolean): void {
        var allCompMaps: CompositionMapping[] = [];
        for (var instanceProp in this.thisClass) {
            if (this.thisClass[instanceProp] instanceof CompositionMapping) {
                allCompMaps.push(this.thisClass[instanceProp]);
            }
        }
        //console.log(allCompMaps);

        if (compMaps) {
            if (Array.isArray(compMaps)) {
                // compose just properties in array...
                var len: number = (<CompositionMapping[]>compMaps).length;
                for (let i: number = 0; i < len; i++) {
                    if (allCompMaps.indexOf(compMaps[i]) > -1) {
                        InstanceFactory.getChildInstances(compMaps[i], this);
                    } else {
                        console.log(new Error("compMap not found for property " + this.TypeName + "." + compMaps[i].PropertyName));
                    }
                }
            } else {
                // compose all compmaps...
                var len: number = allCompMaps.length;
                for (let i: number = 0; i < len; i++) {
                    InstanceFactory.getChildInstances(allCompMaps[i], this);
                }
            }
        }
    }

    protected _hasGetAllPermission: boolean | null = null;
    hasGetAllPermission(evaluator: any = false): boolean {
        if (this._hasGetAllPermission == null) {
            if (typeof evaluator == "function") {
                this._hasGetAllPermission = evaluator();
            } else {
                this._hasGetAllPermission = evaluator;
            }
        }
        console.log(this.thisClass.name + " has getAll permission:", this._hasGetAllPermission);
        return this._hasGetAllPermission;
    }

}