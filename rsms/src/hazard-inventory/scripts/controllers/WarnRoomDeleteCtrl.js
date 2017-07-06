(function () {
    'use strict';

    angular
        .module('app')
        .controller('WarnRoomDeleteCtrl', WarnRoomDeleteCtrl);

    WarnRoomDeleteCtrl.$inject = ['$location']; 

    function WarnRoomDeleteCtrl($location) {
        /* jshint validthis:true */
        var vm = this;
        vm.title = 'WarnRoomDeleteCtrl';

        activate();

        function activate() { }
    }
})();
