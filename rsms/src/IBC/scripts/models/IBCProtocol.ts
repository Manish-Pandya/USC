class IBCProtocol extends FluxCompositerBase {

    static urlMapping: UrlMapping = new UrlMapping("getAllProtocols", "getProtocolById&id=", "saveProtocol");

    Hazard: Hazard;
    static HazardMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Hazard", "getHazardById&id=", "Hazard", "Hazard_id");

    Department: Department;
    static DepartmentMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Department", "getDepartmentById&id=", "Department", "Department_id");

    PrincipalInvestigators: PrincipalInvestigator[];
    static PIMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getAllPIs", "PrincipalInvestigators", "Protocol_id", "Principal_investigator_id", "IBCProtocolPrincipalInvestigator", "getRelationships&class1=IBCProtocol&class2=PrincipalInvestigator");

    IBCProtocolRevisions: IBCProtocolRevision[];
    static RevisionMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCProtocolRevision", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCProtocolRevision&id={{UID}}", "IBCProtocolRevisions", "Protocol_id");

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