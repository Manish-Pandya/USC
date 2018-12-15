<?php

class ReportsMessageMacros {

    public static function getResolvers(){
        $resolvers = array();

        // DepartmentDetailDto
        $resolvers[] = new MacroResolver(
            'DepartmentDetailDto',
            '[Chair Name]', 'Full name of the department chair',
            function(DepartmentDetailDto $departmentInfo){
                return $departmentInfo->getChair_name();
            }
        );

        $resolvers[] = new MacroResolver(
            'DepartmentDetailDto',
            '[Chair First Name]', 'First name of the department chair',
            function(DepartmentDetailDto $departmentInfo){
                return $departmentInfo->getChair_first_name();
            }
        );

        $resolvers[] = new MacroResolver(
            'DepartmentDetailDto',
            '[Chair Last Name]', 'Last name of the department chair',
            function(DepartmentDetailDto $departmentInfo){
                return $departmentInfo->getChair_last_name();
            }
        );

        $resolvers[] = new MacroResolver(
            'DepartmentDetailDto',
            '[Department Name]', 'Name of the Department',
            function(DepartmentDetailDto $departmentInfo){
                return $departmentInfo->getName();
            }
        );

        $resolvers[] = new MacroResolver(
            'LabInspectionSummaryContext',
            '[Report Year]', 'Year of the Summary Report',
            function(LabInspectionSummaryContext $context){
                return $context->report_year;
            }
        );

        $resolvers[] = new MacroResolver(
            'LabInspectionSummaryContext',
            '[Report Link]', 'URL of the Summary Report page',
            function(LabInspectionSummaryContext $context){
                return A_LabInspectionSummary_Processor::getReportLink(
                    $context->department_id,
                    $context->report_year
                );
            }
        );

        return $resolvers;
    }

}

?>