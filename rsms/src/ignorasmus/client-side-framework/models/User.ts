class User extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");
    static RoleMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Role", "getAllRoles", "Roles", "User_id", "Role_id", "UserRole", "getRelationships&class1=User&class2=Role");

    constructor() {
        super();
    }
}