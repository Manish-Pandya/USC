'use strict';

angular
    .module('actionFunctionsModule', [])
    .factory('reportsActionFunctionsFactory', function reportsActionFunctionsFactory(){
        var af = {};

        af.getAllAvailableDepartments = function getAllAvailableDepartments(){
            return XHR.GET('getAllAvailableDepartments')
                .then( departments => {
                    console.debug("Retrieved departments:", departments);
                    return departments;
                });
        };

        af.getDepartmentInfo = function getDepartmentInfo(deptId){
            var action = "getDepartmentInfo";
            if( deptId ){
                action += "&department_id=" + deptId;
            }

            return XHR.GET(action)
                .then( department => {
                    console.debug("Retrieved department", deptId, department);
                    return department;
                });
        };

        af.getInspectionSummaryReport = function getInspectionSummaryReport(year, dept){
            var action = "getInspectionsSummaryReport&year=" + year + "&department_id=" + dept;
            return XHR.GET(action)
                .then( report => {
                    console.debug("Retrieved Inspections Summary Report", year, dept, report);
                    return report;
                });
        };

        return af;
    });