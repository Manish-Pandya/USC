class User extends BaseModel {

    private Name: string;
    static urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllUsers";

    public setUrlMappings() {
        var mappings = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");
        super.setUrlMappings(mappings);
    }
}