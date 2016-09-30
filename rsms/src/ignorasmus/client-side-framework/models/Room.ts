class Room extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");

    PrincipalInvestigators: PrincipalInvestigator[];
    PIMap: CompositionMapping;

    constructor() {
        super();
    }

    onFulfill(callback: Function = null, ...args): Function | void {
        this.hasGetAllPermission();
        // build compositionMapping
        this.PIMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getAllPIs", "PrincipalInvestigators", "Room_id", "Principal_investigator_id", "RoomPrincipalInvestigator", "getRelationships&class1=Room&class2=PrincipalInvestigator");

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