class User extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");

    constructor() {
        super();
    }
}