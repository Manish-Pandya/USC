abstract class BaseModel{
    private urlMappings: UrlMapping;
    protected getUrlMappings(): UrlMapping { return this.urlMappings;}
    protected setUrlMappings(mappings: UrlMapping): void { this.urlMappings = mappings; }

    protected UID: number;
    protected ClassPropName: string;

    contruct() {
        if (!this.urlMappings) {
            throw new Error("You forgot to set URL mappings for this class.  The framework can't get instances of it from the server");
        }

        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }

        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.ClassPropName = this[DataStoreManager.classPropName];
        }
    }
}