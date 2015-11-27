'use strict';

//generic model to be "extended" by "POJOs"

//constructor
var PIHazardRoomDto = function(){};
PIHazardRoomDto.prototype = {
    Class: "PIHazardRoomDto"
}

//inherit from and extend GenericModel
extend(PIHazardRoomDto, GenericModel);
