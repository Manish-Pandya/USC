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
var equipment;
(function (equipment) {
    var Campus = /** @class */ (function (_super) {
        __extends(Campus, _super);
        function Campus() {
            return _super.call(this) || this;
        }
        Campus.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        Campus.urlMapping = new UrlMapping("getAllCampuses", "getCampusById&id=", "");
        return Campus;
    }(FluxCompositerBase));
    equipment.Campus = Campus;
})(equipment || (equipment = {}));
