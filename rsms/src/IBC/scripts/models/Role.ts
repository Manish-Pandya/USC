namespace ibc {
    export class Role extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllRoles", "getRoleById&id=", "saveRole");

        Name: string;

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