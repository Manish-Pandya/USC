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
    LinkingMaps: any[];
    callGetAll: boolean;

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
            this.LinkingMaps = [];
    }

    flagGetAll() {
        if (this.ChildUrl == window[this.ChildType].urlMapping.urlGetAll) {
            // flag that getAll will be called
            this.callGetAll = true;
        }
    }

}