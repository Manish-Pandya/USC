var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var ibc;
(function (ibc) {
    var IBCMeeting = /** @class */ (function (_super) {
        __extends(IBCMeeting, _super);
        function IBCMeeting() {
            var _this = _super.call(this) || this;
            _this.Attendees = [];
            /*Room: Room;
            static RoomMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_ONE, "Room", "getRoomById&id=", "Room", "Room_id");*/
            _this.Meeting_date = "";
            _this.Location = "";
            _this.Agenda = "";
            return _this;
        }
        IBCMeeting.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        IBCMeeting.urlMapping = new UrlMapping("getAllIBCMeetings", "getIBCMeetingById&id=", "saveIBCMeeting");
        IBCMeeting.IBCProtocolRevisionMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCProtocolRevision", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCProtocolRevision&id={{UID}}", "IBCProtocolRevisions", "Meeting_id");
        IBCMeeting.AttendeesMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "User", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=User&id={{UID}}", "Attendees", "Meeting_id");
        return IBCMeeting;
    }(FluxCompositerBase));
    ibc.IBCMeeting = IBCMeeting;
})(ibc || (ibc = {}));
