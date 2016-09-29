class Role extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllRoles", "getRoleById&id=", "saveRole");

    constructor() {
        super();
    }
}