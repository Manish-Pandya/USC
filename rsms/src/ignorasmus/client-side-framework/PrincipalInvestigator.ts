class PrincipalInvestigator extends BaseModel {

    static urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllPIs";
    User: User;
    UserMap: CompositionMapping = new CompositionMapping("ONE_TO_ONE", "User", "getUserById&id=" + this.UID, "User", "User_id");
    public loadUser(): User {
        
        return this.User;
    }

    private labPersonnel: User[];
    LabPersonnelMap: CompositionMapping = new CompositionMapping("ONE_TO_MANY", "User", "getUserById&id=" + this.UID, "LabPersonnel", "Supervisor_id");
    public loadLabPersonnel() {
        return this.labPersonnel;
    }

    public setUrlMappings() {
        var mappings = new UrlMapping("getAllPis", "getPiById&id=", "savePI");
        super.setUrlMappings(mappings);
    }

}