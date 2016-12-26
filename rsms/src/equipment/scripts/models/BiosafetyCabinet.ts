namespace equipment {
    export class BioSafetyCabinet extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllBioSafetyCabinets", "getBiosafetyCabinetById&id=", "saveBiosafetyCabinet");

        EquipmentInspections: EquipmentInspection[];
        static EquipmentInspectionMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "EquipmentInspection", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=EquipmentInspections&id={{UID}}", "EquipmentInspections", "Equipment_id");

        constructor() {
            super();
        }

        onFulfill(): void {
            super.onFulfill();
            this.getChildUrl(BioSafetyCabinet.EquipmentInspectionMap);
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
