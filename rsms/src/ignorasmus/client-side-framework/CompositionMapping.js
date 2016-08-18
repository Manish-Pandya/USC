var CompositionMapping = (function () {
    function CompositionMapping(compositionType, childType, childUrl, propertyName, gerundName, gerundUrl) {
        if (gerundName === void 0) { gerundName = null; }
        if (gerundUrl === void 0) { gerundUrl = null; }
        this.CompositionType = compositionType;
        this.ChildType = childType;
        this.ChildUrl = childUrl;
        this.PropertyName = propertyName;
        if (this.CompositionType == CompositionMapping.ONE_TO_MANY) {
            if (!gerundName || !gerundUrl) {
                throw new Error("You must provide a gerundName and gerundUrl to fullfill this ONE TO MANY compositional relationship");
            }
            else {
                this.GerundName = gerundName;
                this.GerundUrl = gerundUrl;
            }
        }
    }
    CompositionMapping.ONE_TO_ONE = "ONE_TO_ONE";
    CompositionMapping.ONE_TO_MANY = "ONE_TO_MANY";
    CompositionMapping.MANY_TO_MANY = "MANY_TO_MANY";
    return CompositionMapping;
}());
