class CompositionMapping {
    COMPOSITION_TYPE: "ONE_TO_ONE" | "ONE_TO_MANY" | "MANY_TO_MANY";
    private ChildType: string;
    private ChildUrl: string;
    private PropertyName: string;
    private GerundName: string;
    private GerundUrl: string;

    constructor(compositionType: "ONE_TO_ONE" | "ONE_TO_MANY" | "MANY_TO_MANY",
        childType: string,
        childUrl: string,
        propertyName: string,
        gerundName: string,
        gerundUrl: string) {
            this.COMPOSITION_TYPE = compositionType;
            this.ChildType = childType;
            this.ChildUrl = childUrl;
            this.PropertyName = propertyName;
            if (this.COMPOSITION_TYPE == "ONE_TO_MANY") {
                if (!gerundName || !gerundUrl) {
                    throw new Error("You must provide a gerundName and gerundUrl to fullfill this ONE TO MANY compositional relationship");
                } else {
                    this.GerundName = gerundName;
                    this.GerundUrl = gerundUrl;
                }
            }
    }

}