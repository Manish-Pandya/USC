class PrincipalInvestigator extends BaseModel {

    static urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllPIs";
    static urlMapping = new UrlMapping("getAllPis", "getPiById&id=", "savePI");

    User: User;
    UserMap: CompositionMapping = new CompositionMapping("ONE_TO_ONE", "User", "getUserById&id=" + this.UID, "User", "User_id");

    constructor() {
        super();
    }

    public loadUser(): User {
        return this.User;
    }

    private LabPersonnel: User[];
    LabPersonnelMap: CompositionMapping = new CompositionMapping("ONE_TO_MANY", "User", "getUserById&id=" + this.UID, "LabPersonnel", "Supervisor_id");
    public loadLabPersonnel() {
        return (<any>this).labPersonnel;
    }

    private Rooms: Room[];
    //RoomMap: CompositionMapping = new CompositionMapping("MANY_TO_MANY", "Room", "getAllRooms", "Rooms", "Principal_investigator_id",  "Key_id")
    RoomMap: CompositionMapping = new CompositionMapping("MANY_TO_MANY", "Room", "getAllRooms", "Rooms", "Principal_investigator_id", "Room_id", "PrincipalInvestigatorRoom", "getRelationships&class1=" + this.ClassPropName + "&class2=Room");

}