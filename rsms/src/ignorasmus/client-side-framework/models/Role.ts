namespace ignorasmus {
    export class Role extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllRoles", "getRoleById&id=", "saveRole");

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