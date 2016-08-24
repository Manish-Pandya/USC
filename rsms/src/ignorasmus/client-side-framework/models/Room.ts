class Room extends BaseModel {

    static urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllRooms";
    static urlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");

    private Name: string;

}