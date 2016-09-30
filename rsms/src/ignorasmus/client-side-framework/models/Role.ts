class Role extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllRoles", "getRoleById&id=", "saveRole");

    constructor() {
        super();
    }

    onFulfill(callback: Function = null, ...args): Function | void {
        this.hasGetAllPermission();

        // build compositionMapping

        return super.onFulfill(callback, ...args);
    }

    hasGetAllPermission(): boolean {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            super.hasGetAllPermission(_.intersection(currentRoles, allowedRoles).length > 0);
        }
        return this._hasGetAllPermission;
    }
}