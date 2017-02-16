namespace ibc {
    export class IBCPossibleAnswer extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCPossibleAnswers", "getIBCPossibleAnswerById&id=", "saveIBCPossibleAnswer");

        IBCResponses: IBCResponse[];
        static ResponseMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCResponse", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCResponses&id={{UID}}", "IBCResponses", "Answer_id");

        //IBCResponses: IBCResponse[];
        //static ResponseMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "IBCResponse", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=PrincipalInvestigator&id={{UID}}", "PrincipalInvestigators", "Protocol_id", "Principal_investigator_id", "IBCProtocolPrincipalInvestigator", "getRelationships&class1=IBCProtocol&class2=PrincipalInvestigator");
        
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