class PrincipalInvestigator extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllPIs", "getPIById&id=", "savePI");

    User: User;
    UserMap: CompositionMapping;

    LabPersonnel: User[];
    LabPersonnelMap: CompositionMapping;

    Rooms: Room[];
    RoomMap: CompositionMapping;

    constructor() {
        super();
    }

    onFulfill(): void {
        this.hasGetAllPermission();
        super.onFulfill();

        // build compositionMapping
        this.UserMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "User", "getUserById&id=", "User", "User_id");
        this.LabPersonnelMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getAllUsers", "LabPersonnel", "Supervisor_id");
        let rumStringa: string = "getPropertyByName&type=" + this[DataStoreManager.classPropName] + "&property=rooms&id=" + this.UID;
        this.RoomMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Room", rumStringa, "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=PrincipalInvestigator&class2=Room");
    }

    hasGetAllPermission(): boolean {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            super.hasGetAllPermission(_.intersection(currentRoles, allowedRoles).length > 0);
        }
        return this._hasGetAllPermission;
    }

}