class IBCSection extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllSections", "getSectionById&id=", "saveSection");

    constructor() {
        super();
    }

    hasGetAllPermission(): boolean {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            super.hasGetAllPermission(_.intersection(currentRoles, allowedRoles).length > 0);
        }
        return this._hasGetAllPermission;
    }
}