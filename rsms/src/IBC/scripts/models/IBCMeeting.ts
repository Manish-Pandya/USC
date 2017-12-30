namespace ibc {
    export class IBCMeeting extends FluxCompositerBase {

        static urlMapping = new UrlMapping("getAllIBCMeetings", "getIBCMeetingById&id=", "saveIBCMeeting");

        IBCProtocolRevisions: IBCProtocolRevision[];
        static IBCProtocolRevisionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCProtocolRevision", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCProtocolRevision&id={{UID}}", "IBCProtocolRevisions", "Meeting_id");

        Attendees: User[] = [];
        static AttendeesMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=User&id={{UID}}", "Attendees", "Meeting_id");

        /*Room: Room;
        static RoomMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Room", "getRoomById&id=", "Room", "Room_id");*/

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