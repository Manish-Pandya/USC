namespace ibc {
    export class IBCEmailGen extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCEmails", "getIBCEmailGenById&id=", "saveIBCEmailGen");

        Corpus: string;

        Subject: string;

        Title: string;

        constructor() {
            super();
        }

        hasGetAllPermission(): boolean {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                super.hasGetAllPermission(_.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        }
    }
}