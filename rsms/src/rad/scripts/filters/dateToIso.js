angular.module('00RsmsAngularOrmApp')
	.filter('dateToISO', function() {
	  return function(input,object,propertyName) {
	  		if(!input)return "N/A";
            if(object && propertyName && object["view_"+propertyName] && object["view_"+propertyName].indexOf("/") > -1)return object["view_"+propertyName];
          
			// Split timestamp into [ Y, M, D, h, m, s ]
			var t = input.split(/[- :]/);
			// Apply each element to the Date function
			var d = new Date(t[0], t[1], t[2]);
            console.log(d);
			input = d.getMonth() + '/' + d.getDate() + '/' + d.getFullYear();
            console.log(input);
			if(object && propertyName){
				object["view_"+propertyName] = input;
			}
			if(t[0]=="0000")return "N/A";
			return input
	  };
	})
