namespace ibc {
    export class IBCQuestion extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllIBCQuestions", "getIBCQuestionById&id=", "saveIBCQuestion");

        IBCPossibleAnswers: IBCPossibleAnswer[];
        static PossibleAnswerMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCPossibleAnswer", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCPossibleAnswers&id={{UID}}", "IBCPossibleAnswers", "Question_id");

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