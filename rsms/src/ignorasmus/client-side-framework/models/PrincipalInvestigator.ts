namespace ignorasmus {
    export class PrincipalInvestigator extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllPIs", "getPIById&id=", "savePI");

        User: User;
        static UserMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "User", "getUserById&id=", "User", "User_id");

        LabPersonnel: User[];
        static LabPersonnelMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=LabPersonnel&id={{UID}}", "LabPersonnel", "Supervisor_id");

        Rooms: Room[];
        static RoomMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Room", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=rooms&id={{UID}}", "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=PrincipalInvestigator&class2=Room");

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
