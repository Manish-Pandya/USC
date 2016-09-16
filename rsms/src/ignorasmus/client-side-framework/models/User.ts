class User extends FluxCompositerBase {

    static urlMapping = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");

    constructor() {
        super();
    }
}