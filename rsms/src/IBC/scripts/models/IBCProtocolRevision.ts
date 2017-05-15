namespace ibc {
    export class IBCProtocolRevision extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllProtocolRevisions", "getProtocolRevisionById&id=", "saveProtocolRevision");

        PrimaryReviewers: User[];
        static PrimaryReviewersMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getPropertyByName&id={{this.UID}}&property=PrimaryReviewers&type=IBCProtocolRevision", "PrimaryReviewers", "Revision_id", "Reviewer_id", "IBCRevisionPrimaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRIMARY_REVIEWERS_RELATIONSHIP");

        PreliminaryReviewers: User[];
        static PreliminaryReviewersMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getPropertyByName&id={{this.UID}}&property=PreliminaryReviewers&type=IBCProtocolRevision", "PreliminaryReviewers", "Revision_id", "Reviewer_id", "IBCRevisionPreliminaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRELIMINARY_REVIEWERS_RELATIONSHIP");

        IBCResponses: IBCResponse[];
        static IBCReponseMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCResponse", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCResponses&id={{UID}}", "IBCResponses", "Revision_id");

        responsesMapped: { [index: string]: ibc.IBCResponse[] } = {};
        getResponsesMapped(): { [index: string]: ibc.IBCResponse[] } {
            if (this.IBCResponses) {
                for (var n = 0; n < this.IBCResponses.length; n++) {
                    var response = this.IBCResponses[n];
                    //console.log(response);
                    if (!this.responsesMapped[response.Answer_id]) this.responsesMapped[response.Answer_id] = [];
                    this.responsesMapped[response.Answer_id].push(response);
                }
            }
            return this.responsesMapped;
        }

        IBCPreliminaryComments: IBCPreliminaryComment[];
        static IBCPreliminaryCommentMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCPreliminaryComment", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCPreliminaryComments&id={{UID}}", "IBCPreliminaryComments", "Revision_id");

        preliminaryCommentsMapped: { [index: string]: ibc.IBCPreliminaryComment[] } = {};
        getPreliminaryCommentsMapped(): { [index: string]: ibc.IBCPreliminaryComment[] } {
            if (this.IBCPreliminaryComments) {
                for (var n = 0; n < this.IBCPreliminaryComments.length; n++) {
                    var comment = this.IBCPreliminaryComments[n];
                    if (!this.preliminaryCommentsMapped[comment.Question_id]) this.preliminaryCommentsMapped[comment.Question_id] = [];
                    this.preliminaryCommentsMapped[comment.Question_id].push(comment);
                }
            }
            return this.preliminaryCommentsMapped;
        }

        IBCPrimaryComments: IBCPrimaryComment[];
        static IBCPrimaryCommentMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCPrimaryComment", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCPrimaryComments&id={{UID}}", "IBCPrimaryComments", "Revision_id");

        primaryCommentsMapped: { [index: string]: ibc.IBCPrimaryComment[] } = {};
        getPrimaryCommentsMapped(): { [index: string]: ibc.IBCPrimaryComment[] } {
            if (this.IBCPrimaryComments) {
                for (var n = 0; n < this.IBCPrimaryComments.length; n++) {
                    var comment = this.IBCPrimaryComments[n];
                    if (!this.primaryCommentsMapped[comment.Question_id]) this.primaryCommentsMapped[comment.Question_id] = [];
                    this.primaryCommentsMapped[comment.Question_id].push(comment);
                }
            }
            return this.primaryCommentsMapped;
        }

        constructor() {
            super();
        }

        onFulfill(): void {
            super.onFulfill();
            this.getResponsesMapped();
            this.getPreliminaryCommentsMapped();
            this.getPrimaryCommentsMapped();
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