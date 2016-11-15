class IBCProtocol extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllProtocols", "getProtocolById&id=", "saveProtocol");

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