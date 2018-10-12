<?php

class Reports_ActionMappingFactory extends ActionMappingFactory {

	public static function readActionConfig() {
		$mappings = new Reports_ActionMappingFactory();

		return $mappings->getConfig();
    }

	public function getConfig() {
        return array(
            // Lab Inspections Summary
            'getInspectionsSummaryReport' => new ActionMapping('getInspectionsSummaryReport', '', '', '')
        );
    }
}
?>