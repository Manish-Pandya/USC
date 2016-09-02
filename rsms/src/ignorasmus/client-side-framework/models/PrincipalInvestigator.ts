class PrincipalInvestigator extends FluxCompositerBase {

    static urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllPIs";
    static urlMapping = new UrlMapping("getAllPIs", "getPiById&id=", "savePI");

    User: User;
    UserMap: CompositionMapping;

    constructor() {
        super();
    }

    public loadUser(): User {
        return this.User;
    }

    private LabPersonnel: User[];
    LabPersonnelMap: CompositionMapping;
    public loadLabPersonnel() {
        return (<any>this).labPersonnel;
    }

    private Rooms: Room[];
    RoomMap: CompositionMapping;

    onFulfill(callback: Function = null): Function {
        this.UserMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "User", "getUserById&id=" + this.UID, "User", "User_id");
        this.LabPersonnelMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getAllUsers", "LabPersonnel", "Supervisor_id");
        this.RoomMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Room", "getAllRooms", "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=" + this.TypeName + "&class2=Room");
        
        return super.onFulfill(callback);
    }

}