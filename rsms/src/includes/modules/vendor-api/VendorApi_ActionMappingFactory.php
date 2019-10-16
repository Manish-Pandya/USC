<?php

class VendorApi_ActionMappingFactory {

	public static function readActionConfig() {
		$mappings = new Verification_ActionMappingFactory();
		return array(
            "/campus" => new SecuredActionMapping("getAllCampuses", [])
		);
	}
}
?>
