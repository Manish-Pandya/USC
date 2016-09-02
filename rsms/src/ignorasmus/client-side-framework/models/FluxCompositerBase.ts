abstract class FluxCompositerBase {
    static urlMapping: UrlMapping = new UrlMapping("foot", "", "");

    UID: number;
    TypeName: string;

    contruct() {
        if (!FluxCompositerBase.urlMapping) {
            console.log( new Error("You forgot to set URL mappings for this class. The framework can't get instances of it from the server") );
        }
    }

    onFulfill(callback: Function =  null, ...args): Function | void {
        if (DataStoreManager.uidString && this[DataStoreManager.uidString]) {
            this.UID = this[DataStoreManager.uidString];
        }

        if (DataStoreManager.classPropName && this[DataStoreManager.classPropName]) {
            this.TypeName = this[DataStoreManager.classPropName];
        }

        return callback ? callback(args) : null;
    }

    doCompose(compMap:CompositionMapping = null): void {
        if (compMap) {
            // compose just this property...

        } else {
            // compose all compmaps...

        }
    }

}