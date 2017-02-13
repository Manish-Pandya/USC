namespace ibc {
    export class IBCPossibleAnswer extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllPossibleAnswers", "getPossibleAnswerById&id=", "savePossibleAnswer");

        IBCResponses: IBCResponse[];
        static ResponseMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCResponse", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=Responses&id={{UID}}", "IBCResponses", "Answer_id");

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