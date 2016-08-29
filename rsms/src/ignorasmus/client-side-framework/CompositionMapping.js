var CompositionMapping = (function () {
    function CompositionMapping(compositionType, childType, childUrl, propertyName, childIdProp, parentIdProp, gerundName, gerundUrl) {
        if (parentIdProp === void 0) { parentIdProp = null; }
        if (gerundName === void 0) { gerundName = null; }
        if (gerundUrl === void 0) { gerundUrl = null; }
        //temp values for erasmus.  add to global config as optional param
        this.DEFAULT_MANY_TO_MANY_PARENT_ID = "ParentId";
        this.DEFAULT_MANY_TO_MANY_CHILD_ID = "ChildId";
        this.CompositionType = compositionType;
        this.ChildType = childType;
        if (childUrl == window[childType].urlMapping.urlGetAll) {
            // flag that getAll will be called
            this.callGetAll = true;
        }
        this.ChildUrl = childUrl;
        this.PropertyName = propertyName;
        this.ChildIdProp = childIdProp;
        if (parentIdProp) {
            this.ParentIdProp = parentIdProp;
        }
        else {
            this.ParentIdProp = DataStoreManager.uidString;
        }
        if (this.CompositionType == CompositionMapping.MANY_TO_MANY) {
            if (!gerundName || !gerundUrl) {
                throw new Error("You must provide a gerundName and gerundUrl to fullfill this MANY TO MANY compositional relationship");
            }
            else {
                this.GerundName = gerundName;
                this.GerundUrl = gerundUrl;
            }
        }
        this.LinkingMaps = [];
    }
    CompositionMapping.ONE_TO_ONE = "ONE_TO_ONE";
    CompositionMapping.ONE_TO_MANY = "ONE_TO_MANY";
    CompositionMapping.MANY_TO_MANY = "MANY_TO_MANY";
    return CompositionMapping;
}());
