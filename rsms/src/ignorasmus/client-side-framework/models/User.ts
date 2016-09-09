class User extends FluxCompositerBase {

    static urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllUsers";
    static urlMapping = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");

    constructor() {
        super();
    }
}