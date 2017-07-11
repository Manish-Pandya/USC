namespace ibc {
    export class PrincipalInvestigator extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCPIs", "getPIById&id=", "savePI");

        User: User;
        static UserMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "User", "getUserById&id=", "User", "User_id");


        /**
         * I don't think we'll need these props, so that should speed up loading a lot
         * we can load them on deman when we need them
         */
        LabPersonnel: User[];
        static LabPersonnelMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=LabPersonnel&id={{UID}}", "LabPersonnel", "Supervisor_id");

        Rooms: Room[];
        static RoomMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Room", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=rooms&id={{UID}}", "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=PrincipalInvestigator&class2=Room");

        Protocols: IBCProtocol[];
        static ProtocolMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCProtocol", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=Protocols&id={{UID}}", "Protocols", "Principal_investigator_id", "Protocol_id");

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
