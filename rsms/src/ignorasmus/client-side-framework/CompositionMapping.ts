class CompositionMapping {
    static ONE_TO_ONE: string = "ONE_TO_ONE";
    static ONE_TO_MANY: string = "ONE_TO_MANY";
    static MANY_TO_MANY: string = "MANY_TO_MANY";

    CompositionType: "ONE_TO_ONE" | "ONE_TO_MANY" | "MANY_TO_MANY";
    ChildType: string;
    ChildUrl: string;
    PropertyName: string;
    GerundName: string;
    GerundUrl: string;

    constructor(compositionType: "ONE_TO_ONE" | "ONE_TO_MANY" | "MANY_TO_MANY",
        childType: string,
        childUrl: string,
        propertyName: string,
        gerundName: string = null,
        gerundUrl: string = null) {
        this.CompositionType = compositionType;
            this.ChildType = childType;
            this.ChildUrl = childUrl;
            this.PropertyName = propertyName;
            if (this.CompositionType == CompositionMapping.ONE_TO_MANY) {
                if (!gerundName || !gerundUrl) {
                    throw new Error("You must provide a gerundName and gerundUrl to fullfill this ONE TO MANY compositional relationship");
                } else {
                    this.GerundName = gerundName;
                    this.GerundUrl = gerundUrl;
                }
            }
    }

}