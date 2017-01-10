namespace ibc {
    export class IBCProtocolRevision extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllProtocolRevisions", "getProtocolRevisionById&id=", "saveProtocolRevision");

        PrimaryReviewers: User[];
        static PrimaryReviewersMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getAllUsers", "PimraryReviewers", "Revisions_id", "Reviewer_id", "IBCRevisionPrimaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRIMARY_REVIEWERS_RELATIONSHIP");

        PreliminaryReviewers: PrincipalInvestigator[];
        static PreliminaryReviewersMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getAllUsers", "PreliminaryReviewers", "Revisions_id", "Reviewer_id", "IBCRevisionPreliminaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRELIMINARY_REVIEWERS_RELATIONSHIP");


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