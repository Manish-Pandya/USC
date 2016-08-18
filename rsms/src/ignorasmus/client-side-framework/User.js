var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var User = (function (_super) {
    __extends(User, _super);
    function User() {
        _super.apply(this, arguments);
    }
    User.prototype.setUrlMappings = function () {
        var mappings = new UrlMapping("getAllUsers", "getUserById&id=", "saveUser");
        _super.prototype.setUrlMappings.call(this, mappings);
    };
    return User;
}(BaseModel));
