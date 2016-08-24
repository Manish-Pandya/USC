var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var Room = (function (_super) {
    __extends(Room, _super);
    function Room() {
        _super.apply(this, arguments);
    }
    Room.urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllRooms";
    Room.urlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");
    return Room;
}(BaseModel));
