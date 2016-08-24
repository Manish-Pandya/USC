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
    User.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllUsers";
    User.urlMapping = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");
    return User;
}(BaseModel));
