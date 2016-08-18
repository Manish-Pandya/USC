var CompositionMapping = (function () {
    function CompositionMapping(compositionType, childType, childUrl, propertyName, gerundName, gerundUrl) {
        this.COMPOSITION_TYPE = compositionType;
        this.ChildType = childType;
        this.ChildUrl = childUrl;
        this.PropertyName = propertyName;
        if (this.COMPOSITION_TYPE == "ONE_TO_MANY") {
            if (!gerundName || !gerundUrl) {
                throw new Error("You must provide a gerundName and gerundUrl to fullfill this ONE TO MANY compositional relationship");
            }
            else {
                this.GerundName = gerundName;
                this.GerundUrl = gerundUrl;
            }
        }
    }
    return CompositionMapping;
}());
