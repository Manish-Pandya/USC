<?php

class Reports_ActionMappingFactory extends ActionMappingFactory {

	public static function readActionConfig() {
		$mappings = new Reports_ActionMappingFactory();

		return $mappings->getConfig();
    }

	public function getConfig() {
        return array(
            // Lab Inspections Summary
            'getInspectionsSummaryReport' => new SecuredActionMapping('getInspectionsSummaryReport', $this::$ROLE_GROUPS["REPORTS_ALL"], 'ChairReportSecurity::userCanViewSummaryReport'),
            'getDepartmentInfo'           => new SecuredActionMapping('getDepartmentInfo', $this::$ROLE_GROUPS["REPORTS_ALL"], 'ChairReportSecurity::userIsChairOfDepartment'),
            'getAllAvailableDepartments'  => new ActionMapping('getAllAvailableDepartments', '', '', $this::$ROLE_GROUPS["REPORTS_ALL"])
        );
    }
}
?>