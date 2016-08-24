abstract class BaseModel {
    static urlMapping: UrlMapping = new UrlMapping("foot", "", "");

    protected UID: number;
    protected ClassPropName: string;

    contruct() {
        if (!BaseModel.urlMapping) {
            console.log( new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server") );
        }

        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }

        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.ClassPropName = this[DataStoreManager.classPropName];
        }
    }
}