class Room extends FluxCompositerBase {

    static urlAll = "http://erasmus.graysail.com/rsms/src/ajaxaction.php?action=getAllRooms";
    static urlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");

}