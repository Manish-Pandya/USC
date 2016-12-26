namespace equipment {
    export class EquipmentInspection extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllEquipmentInspections", "getEquipmentInspectionById&id=", "saveEquipmentInspection");

        Room: Room;
        static RoomMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Room", "getRoomById&id=", "Room", "Room_id");

        PrincipalInvestigators: PrincipalInvestigator[];
        static PIMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=principalInvestigators&id={{UID}}", "PrincipalInvestigator",  "Inspection_id", "Principal_investigator_id", "PrincipalInvestigatorEquipmentInspection", "getRelationships&class1=EquipmentInspection&class2=PrincipalInvestigator");

        constructor() {
            super();
        }

        onFulfill(): void {
            super.onFulfill();
            this.getChildUrl(EquipmentInspection.RoomMap);
        }

        hasGetAllPermission(): boolean {
            if (this._hasGetAllPermission == null) {
                //var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                super.hasGetAllPermission(true);
            }

            return this._hasGetAllPermission;
        }

    }
}
