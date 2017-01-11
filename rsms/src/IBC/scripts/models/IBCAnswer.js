var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var ibc;
(function (ibc) {
    var IBCAnswer = (function (_super) {
        __extends(IBCAnswer, _super);
        function IBCAnswer() {
            return _super.call(this) || this;
        }
        IBCAnswer.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return IBCAnswer;
    }(FluxCompositerBase));
    IBCAnswer.urlMapping = new UrlMapping("getAllAnswerss", "getAnswerById&id=", "saveAnswer");
    ibc.IBCAnswer = IBCAnswer;
})(ibc || (ibc = {}));
