namespace ibc {
    export class IBCSection extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCSections", "getIBCSectionById&id=", "saveIBCSection");

        IBCQuestions: IBCQuestion[];
        static QuestionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCQuestion", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCQuestions&id={{UID}}", "IBCQuestions", "Section_id");

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