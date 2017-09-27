var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var ibc;
(function (ibc) {
    var IBCPreliminaryComment = /** @class */ (function (_super) {
        __extends(IBCPreliminaryComment, _super);
        function IBCPreliminaryComment() {
            var _this = _super.call(this) || this;
            _this.Text = "";
            return _this;
        }
        IBCPreliminaryComment.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        IBCPreliminaryComment.urlMapping = new UrlMapping("getAllIBCPreliminaryComments", "getIBCPreliminaryCommentById&id=", "saveIBCPreliminaryComment");
        IBCPreliminaryComment.UserMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "User", "getUserById&id=", "User", "Created_user_id");
        return IBCPreliminaryComment;
    }(FluxCompositerBase));
    ibc.IBCPreliminaryComment = IBCPreliminaryComment;
})(ibc || (ibc = {}));
