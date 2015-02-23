angular.module('00RsmsAngularOrmApp')
	.filter('dateToISO', function() {
	  return function(input,object,propertyName) {
	  		if(!input)return "N/A";
			// Split timestamp into [ Y, M, D, h, m, s ]
			var t = input.split(/[- :]/);
			// Apply each element to the Date function
			if(object && propertyName){
				if(t[0]=="0000")return "N/A";
				var d = new Date(t[0], t[1], t[2]);
				input = d.getMonth() + '/' + d.getDate() + '/' + d.getFullYear();
				object["view_"+propertyName] = input;
				console.log(object["view_"+propertyName]);
			}

			return input
	  };
	})
