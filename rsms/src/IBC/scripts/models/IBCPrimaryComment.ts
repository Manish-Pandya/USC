namespace ibc {
    export class IBCPrimaryComment extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCPrimaryComments", "getIBCPrimaryCommentById&id=", "saveIBCPrimaryComment");

        Revision_id: string;

        Question_id: string;

        Text: string = "";

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