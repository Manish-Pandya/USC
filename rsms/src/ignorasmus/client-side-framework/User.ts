class User extends BaseModel {

    private Name: string;

    public setUrlMappings() {
        var mappings = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");
        super.setUrlMappings(mappings);
    }
}