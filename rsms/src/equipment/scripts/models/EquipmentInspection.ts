namespace equipment {
    export class EquipmentInspection extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllEquipmentInspections", "getEquipmentInspectionById&id=", "saveEquipmentInspection");

        Room: Room;
        static RoomMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Room", "getRoomById&id=", "Room", "Room_id");

        PrincipalInvestigators: PrincipalInvestigator[];
        static PIMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=principalInvestigators&id={{UID}}", "PrincipalInvestigators",  "Inspection_id", "Principal_investigator_id", "PrincipalInvestigatorEquipmentInspection", "getRelationships&class1=EquipmentInspection&class2=PrincipalInvestigator");

        Is_active: boolean;
        Equipment_class: string;
        Equipment_id: string | number;
        Comments: string;
        Frequency: string;
        Room_id: string | number;
        Certification_date: string;
        Fail_date: string;
        Due_date:string;
        Status: string;

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
