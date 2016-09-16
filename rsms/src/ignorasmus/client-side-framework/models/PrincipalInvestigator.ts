class PrincipalInvestigator extends FluxCompositerBase {

    static urlMapping = new UrlMapping("getAllPIs", "getPIById&id=", "savePI");

    User: User;
    static UserMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "User", "getUserById&id=", "User", "User_id");

    LabPersonnel: User[];
    static LabPersonnelMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getAllUsers", "LabPersonnel", "Supervisor_id");

    Rooms: Room[];
    static RoomMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Room", "getAllRooms", "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=PrincipalInvestigator&class2=Room");

    constructor() {
        super();
    }

    onFulfill(callback: Function = null, ...args): Function | void {
        return super.onFulfill(callback, ...args);
    }

}