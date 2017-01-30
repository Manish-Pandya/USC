'use strict';
angular
    .module('EquipmentModule')
    .factory('applicationControllerFactory', function applicationControllerFactory($rootScope, $q, $modal, convenienceMethods) {
    var af = $rootScope.af = this;
    //give us access to this factory in all views. Because that's cool.
    af.getViewMap = function (current) {
        var viewMap = [
            {
                Name: 'equipment',
                Label: 'Equipment Center',
                Dashboard: false
            },
            {
                Name: 'equipment.autoclaves',
                Label: 'Autoclaves',
                Dashboard: true
            },
            {
                Name: 'equipment.bio-safety-cabinets',
                Label: 'Biological Safety Cabinets',
                Dashboard: true
            },
            {
                Name: 'equipment.chem-fume-hoods',
                Label: 'Chemical Fume Hoods',
                Dashboard: true
            },
            {
                Name: 'equipment.lasers',
                Label: 'Lasers',
                Dashboard: true
            },
            {
                Name: 'equipment.x-ray',
                Label: 'X-Ray Machines',
                Dashboard: true
            }
        ];
        var i = viewMap.length;
        while (i--) {
            if (current.name == viewMap[i].Name) {
                return viewMap[i];
            }
        }
    };
    af.setSelectedView = function (view) {
        $rootScope.selectedView = view;
    };
    af.save = function (viewModel) {
        $rootScope.error = null;
        return $rootScope.saving = DataStoreManager.save(viewModel);
    };
    /********************************************************************
    **
    **      HANDY FUNCTIONS
    **
    ********************************************************************/
    af.getDate = function (dateString) {
        console.log(dateString);
        console.log(Date.parse(dateString));
        var seconds = Date.parse(dateString);
        console.log(seconds);
        //if( !dateString || isNaN(dateString) )return;
        var t = new Date(1970, 0, 1);
        t.setTime(seconds);
        console.log(t);
        return t;
    };
    af.getIsExpired = function (dateString) {
        console.log(dateString);
        console.log(Date.parse(dateString));
        var seconds = Date.parse(dateString);
        console.log(new Date().getTime());
        console.log(seconds < new Date().getTime());
        return seconds < new Date().getTime();
    };
    return af;
});
