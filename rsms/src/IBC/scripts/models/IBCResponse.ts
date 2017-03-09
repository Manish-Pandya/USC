namespace ibc {
    export class IBCResponse extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCResponses", "getIBCResponseById&id=", "saveIBCResponse");

        Answer_id: string;

        constructor() {
            super();
        }

        hasGetAllPermission(): boolean {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.IBC_CHAIR];
                super.hasGetAllPermission(_.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        }
    }
}