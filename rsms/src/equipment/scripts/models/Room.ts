namespace equipment {
    export class Room extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");

        PrincipalInvestigators: PrincipalInvestigator[];
        static PIMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=PrincipalInvestigators&id={{UID}}", "PrincipalInvestigators", "Principal_investigator_id", "Room_id", "RoomPrincipalInvestigator", "getRelationships&class1=Room&class2=PrincipalInvestigator");

        constructor() {
            super();
        }

        hasGetAllPermission(): boolean {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.SAFETY_INSPECTOR, Constants.ROLE.NAME.ADMIN];
                super.hasGetAllPermission(_.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        }
    }
}