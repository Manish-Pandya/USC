namespace ibc {
    export class IBCMeeting extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCMeetings", "getIBCMeetingById&id=", "saveIBCMeeting");

        IBCProtocolRevisions: IBCProtocolRevision[];
        static IBCProtocolRevisionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCProtocolRevision", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCProtocolRevision&id={{UID}}", "IBCProtocolRevisions", "Meeting_id");

        Attendees: User[];
        static AttendeesMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=User&id={{UID}}", "Attendees", "Meeting_id", "Attendee_id", "IBCMeetingAttendees", "getRelationships&class1=IBCMeeting&class2=User");

        Meeting_date: string = "";

        Location: string = "";

        Agenda: string = "";

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