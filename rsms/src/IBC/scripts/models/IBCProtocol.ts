namespace ibc {
    export class IBCProtocol extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllProtocols", "getProtocolById&id=", "saveProtocol");

        Hazard: Hazard;
        static HazardMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Hazard", "getHazardById&id={{this.Hazard_id}}", "Hazard", "Hazard_id");

        Department: Department;
        static DepartmentMap = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Department", "getDepartmentById&id={{this.Department_id}}", "Department", "Department_id");

        PrincipalInvestigators: PrincipalInvestigator[] = [];
        static PIMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=PrincipalInvestigator&id={{UID}}", "PrincipalInvestigators", "Protocol_id", "Principal_investigator_id", "IBCProtocolPrincipalInvestigator", "getRelationships&class1=IBCProtocol&class2=PrincipalInvestigator");

        IBCProtocolRevisions: IBCProtocolRevision[];
        static RevisionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCProtocolRevision", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCProtocolRevisions&id={{UID}}", "IBCProtocolRevisions", "Protocol_id");

        IBCSections: IBCSection[];
        static SectionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCSection", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCSections&id={{UID}}", "IBCSections", "Hazard_id", "Hazard_id");
        

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