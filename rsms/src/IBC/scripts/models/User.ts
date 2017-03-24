namespace ibc {
    export class User extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");

        Roles: Role[];
        static RoleMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Role", "getAllRoles", "Roles", "User_id", "Role_id", "UserRole", "getRelationships&class1=User&class2=Role");

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