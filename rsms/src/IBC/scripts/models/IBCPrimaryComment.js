var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var ibc;
(function (ibc) {
    var IBCPrimaryComment = (function (_super) {
        __extends(IBCPrimaryComment, _super);
        function IBCPrimaryComment() {
            var _this = _super.call(this) || this;
            _this.Text = "";
            return _this;
        }
        IBCPrimaryComment.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return IBCPrimaryComment;
    }(FluxCompositerBase));
    IBCPrimaryComment.urlMapping = new UrlMapping("getAllIBCPrimaryComments", "getIBCPrimaryCommentById&id=", "saveIBCPrimaryComment");
    ibc.IBCPrimaryComment = IBCPrimaryComment;
})(ibc || (ibc = {}));
