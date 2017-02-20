namespace ibc {
    export class IBCProtocolRevision extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllProtocolRevisions", "getProtocolRevisionById&id=", "saveProtocolRevision");

        PrimaryReviewers: User[];
        static PrimaryReviewersMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getPropertyByName&id={{this.UID}}&property=PrimaryReviewers&type=IBCProtocolRevision", "PrimaryReviewers", "Revisions_id", "Reviewer_id", "IBCRevisionPrimaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRIMARY_REVIEWERS_RELATIONSHIP");

        PreliminaryReviewers: User[];
        static PreliminaryReviewersMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getPropertyByName&id={{this.UID}}&property=PreliminaryReviewers&type=IBCProtocolRevision", "PreliminaryReviewers", "Revisions_id", "Reviewer_id", "IBCRevisionPreliminaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRELIMINARY_REVIEWERS_RELATIONSHIP");

        IBCResponses: IBCResponse[];
        static IBCReponseMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCResponse", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCReponses&id={{UID}}", "IBCResponses", "Protocol_id");


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