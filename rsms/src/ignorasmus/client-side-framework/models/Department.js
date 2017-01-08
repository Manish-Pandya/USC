var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var ignorasmus;
(function (ignorasmus) {
    var Department = (function (_super) {
        __extends(Department, _super);
        function Department() {
            return _super.call(this) || this;
        }
        Department.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return Department;
    }(FluxCompositerBase));
    Department.urlMapping = new UrlMapping("getAllDepartments", "getDepartmentById&id=", "saveDepartment");
    ignorasmus.Department = Department;
})(ignorasmus || (ignorasmus = {}));