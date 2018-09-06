console.log("Create filtersApp module");
var _dateToIsoFn = function(input,object,propertyName){
	if(!input)return "N/A";
	// Split timestamp into [ Y, M, D, h, m, s ]
	var t = input.split(/[- :]/);
	// Apply each element to the Date function
	var d = new Date(t[0], t[1], t[2]);
	input = d.getMonth() + '/' + d.getDate() + '/' + d.getFullYear();
	if(object && propertyName){
		object["view_"+propertyName] = input;
		console.log(object["view_"+propertyName]);
	}
	if(t[0]=="0000")return "N/A";
	return input;
};

angular.module('filtersApp', []);
angular.module('filtersApp').filter('dateToIso', function() {
    return _dateToIsoFn;
});

angular.module('filtersApp').filter('dateToISO', function(){
    return _dateToIsoFn;
});

angular.module('filtersApp').filter('emptyNA', function(){
    return function(input){
        if( input == "N/A" ){
            return '';
        }

        return input;
    };
});

angular.module('filtersApp').filter('splitAtPeriod', function() {
    return function(input) {
        if(!input)return "N/A";
        // Split timestamp into [ Y, M, D, h, m, s ]
        var split = input.split('.');
        // Apply each element to the Date function
        var string = '';
        var i = split.length;
        while(i--){
            string +=  split[i] + ' ';
        }

        return string;
    };
});
