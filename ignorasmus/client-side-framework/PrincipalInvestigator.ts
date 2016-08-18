class PrincipalInvestigator extends BaseModel {

    private User: User;
    private UserMap: CompositionMapping = new CompositionMapping("ONE_TO_ONE", "User", "getUserById&id="+this.Key_id, "User");
    public loadUser(): User {
        
        return this.User;
    }

    private labPersonnel: User[];
    public loadLabPersonnel() {
        return this.labPersonnel;
    }

    public setUrlMappings() {
        var mappings = new UrlMapping("getAllPis", "getPiById&id=", "savePI");
        super.setUrlMappings(mappings);
    }

}