namespace ibc {
    export class IBCPreliminaryComment extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCPreliminaryComments", "getIBCPreliminaryCommentById&id=", "saveIBCPreliminaryComment");

        Revision_id: string;

        Question_id: string;

        text: string = "";

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