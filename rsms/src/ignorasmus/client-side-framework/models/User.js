var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var User = (function (_super) {
    __extends(User, _super);
    function User() {
        _super.call(this);
    }
    User.urlMapping = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");
    User.RoleMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "Role", "getAllRoles", "Roles", "User_id", "Role_id", "UserRole", "getRelationships&class1=User&class2=Role");
    return User;
}(FluxCompositerBase));
