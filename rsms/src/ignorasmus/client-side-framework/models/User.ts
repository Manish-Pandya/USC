class User extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");

    RoleMap: CompositionMapping;

    constructor() {
        super();
    }

    onFulfill(): void {
        this.hasGetAllPermission();
        super.onFulfill();

        // build compositionMapping
        this.RoleMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Role", "getAllRoles", "Roles", "User_id", "Role_id", "UserRole", "getRelationships&class1=User&class2=Role");
    }

    hasGetAllPermission(): boolean {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            super.hasGetAllPermission(_.intersection(currentRoles, allowedRoles).length > 0);
        }
        return this._hasGetAllPermission;
    }
}