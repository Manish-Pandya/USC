class CompositionMapping {
    static ONE_TO_ONE: "ONE_TO_ONE" = "ONE_TO_ONE";
    static ONE_TO_MANY: "ONE_TO_MANY" = "ONE_TO_MANY";
    static MANY_TO_MANY: "MANY_TO_MANY" = "MANY_TO_MANY";

    //temp values for erasmus.  add to global config as optional param
    DEFAULT_MANY_TO_MANY_PARENT_ID: string = "ParentId";
    DEFAULT_MANY_TO_MANY_CHILD_ID: string = "ChildId";

    CompositionType: "ONE_TO_ONE" | "ONE_TO_MANY" | "MANY_TO_MANY";
    ChildType: string;
    ChildUrl: string;
    PropertyName: string;
    GerundName: string;
    GerundUrl: string;
    ChildIdProp: string;
    ParentIdProp: string;
    callGetAll: boolean;

    /**
     *
     * Models the relationship between classes, providing URIs to fetch child objects
     *
     * Instances of this utility class should be contructed by your classes in onFullfill or later
     *
     * @param compositionType
     * @param childType
     * @param childUrl
     * @ex "getPropertyByName&type=" + this[DataStoreManager.classPropName] + "&property=rooms&id=" + this.UID
     * @param propertyName
     * @param childIdProp
     * @param parentIdProp
     * @param gerundName
     * @param gerundUrl
     */
    constructor(compositionType: "ONE_TO_ONE" | "ONE_TO_MANY" | "MANY_TO_MANY",
        childType: string,
        childUrl: string,
        propertyName: string,
        childIdProp: string,
        parentIdProp: string = null,
        gerundName: string = null,
        gerundUrl: string = null
    ) {
        this.CompositionType = compositionType;
        this.ChildType = childType;
        this.ChildUrl = childUrl;
        this.PropertyName = propertyName;
        this.ChildIdProp = childIdProp;

        if (parentIdProp) {
            this.ParentIdProp = parentIdProp
        } else {
            this.ParentIdProp = DataStoreManager.uidString;
        }
        if (this.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (!gerundName || !gerundUrl) {
                throw new Error("You must provide a gerundName and gerundUrl to fullfill this MANY TO MANY compositional relationship");
            } else {
                this.GerundName = gerundName;
                this.GerundUrl = gerundUrl;
            }
        }
        
    }

}


abstract class FluxCompositerBase {
    static urlMapping: UrlMapping = new UrlMapping("foot", "", "");

    UID: number;
    TypeName: string;
    viewModelWatcher: FluxCompositerBase | null;

    private thisClass: Function;

    protected _allCompMaps: CompositionMapping[];
    get allCompMaps(): CompositionMapping[] {
        if (!this._allCompMaps) {
            this._allCompMaps = [];
            for (var instanceProp in this.thisClass) {
                if (this.thisClass[instanceProp] instanceof CompositionMapping) {
                    var cm: CompositionMapping = this.thisClass[instanceProp];
                    if (cm.ChildUrl == window[cm.ChildType].urlMapping.urlGetAll) {
                        // flag that getAll will be called
                        cm.callGetAll = true;
                    }
                    this._allCompMaps.push(cm);
                }
            }
        }
        return this._allCompMaps;
    }

    private fixUrl(str: string): string {
        var pattern: RegExp = /\{\{\s*([a-zA-Z_\-&\$\[\]][a-zA-Z0-9_\-&\$\[\]\.]*)\s*\}\}/g;

        str = str.replace(pattern, (sub: string): string => {
            sub = sub.match(/\{\{(.*)\}\}/)[1];
            var thing = sub.split(".");
            sub = thing[0];
            for (var n: number = 1; n < thing.length; n++) {
                sub += "['" + thing[n] + "']";
            }
            if (thing.length > 1) {
                sub = eval(sub);
            }

            return this[sub];
        });
        console.log(str);
        return str;
    }

    getCompMapFromProperty(property: string): CompositionMapping | null {
        var cms = this.allCompMaps;
        var l = cms.length;
        for (let i = 0; i < l; i++) {
            let cm: CompositionMapping = cms[i];
            console.log(cm.PropertyName, property);
            if (cm.PropertyName == property) return cm;
        }
        return;
    }

    constructor() {
        if (!FluxCompositerBase.urlMapping) {
            console.log( new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server") );
        }
        this.thisClass = (<any>this).constructor;
    }

    onFulfill(): void {
        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }
        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.TypeName = this[DataStoreManager.classPropName];
        }
    }

    doCompose(compMaps: CompositionMapping[] | boolean): void {
        if (this[DataStoreManager.classPropName] == "PrincipalInvestigator" && this.UID == 1) {
            console.log(this["Rooms"] && this["Rooms"].length, "do comp called for lydia");
        }

        if (compMaps) {
            if (Array.isArray(compMaps)) {
                // compose just properties in array...
                var len: number = (<CompositionMapping[]>compMaps).length;
                for (let i: number = 0; i < len; i++) {
                    if (this.allCompMaps.indexOf(compMaps[i]) > -1) {
                        InstanceFactory.getChildInstances(compMaps[i], this);
                    }
                }
            } else {
                // compose all compmaps...
                var len: number = this.allCompMaps.length;
                for (let i: number = 0; i < len; i++) {
                    InstanceFactory.getChildInstances(this.allCompMaps[i], this);
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
        return this._hasGetAllPermission;
    }

}