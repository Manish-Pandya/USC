namespace equipment {
    export class Campus extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllCampuses", "getCampusById&id=", "");
        constructor() {
            super();
        }

        onFulfill(): void {
            super.onFulfill();
        this.getChildUrl(BioSafetyCabinet.EquipmentInspectionMap);
    }

    hasGetAllPermission(): boolean {
        if (this._hasGetAllPermission == null) {
            //var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            super.hasGetAllPermission(true);
        }

        return this._hasGetAllPermission;
    }

}
}
