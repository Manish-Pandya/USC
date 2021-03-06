namespace ibc {
    export class Department extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllDepartments", "getDepartmentById&id=", "saveDepartment");

        constructor() {
            super();
        }

        hasGetAllPermission(): boolean {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                super.hasGetAllPermission(_.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        }
    }
}