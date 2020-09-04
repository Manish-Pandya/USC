<?php

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
abstract class RadCrud extends GenericCrud {

	/**
	 * TOTALS parcel use amounts in a container, grouped by isotope
	 * param:  $useAmounts Array of ParcelUseAmounts
	 * return array of douples (isotopename: currie level)
	 *
	 */
	public function sumUsages($useAmounts){
		$LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

		$isotopeAmounts = array();

		// collect use amounts PER ISOTOPE
		$LOG->debug("Collect isotope contents from use amounts");
		$isotopeDao = new IsotopeDAO();
		foreach($useAmounts as $amount){
			$LOG->debug("  $amount");

			// A use amount may reference one or more isotopes
			// We need to collect all isotopes and their relative percentage of the amount
			// Once we have all the parts, we can calculate the per-isotope values for this Amount
			$isotopes_percents = [];

			// Amount is related to a miscellaneous waste,
			//  or a single isotope
			// This amount is comprised 100% of a single Isotope
            if($amount->getIsotope_id() != null){
				$isotope = $isotopeDao->getById($amount->getIsotope_id());
				$isotopes_percents[] = [$isotope, 100];

				$LOG->debug("  Amount references a single isotope");
			}

			// else amount is related to multiple isotopes
			else {
				$LOG->debug("  Amount references multiple isotopes");

				// use -> parcel -> parcelauth => auths -> isotopes
				$useDao = new GenericDAO(new ParcelUse());
                $use = $useDao->getById($amount->getParcel_use_id());

                $parcelDao = new GenericDAO(new Parcel());
				$parcel = $parcelDao->getById($use->getParcel_id());

				$parcelauths = $parcel->getParcelAuthorizations();
				foreach($parcelauths as $parcelauth){
					$isotopes_percents[] = [$parcelauth->getIsotope(), $parcelauth->getPercentage()];
				}
			}

			$LOG->debug("  Amount includes " . count($isotopes_percents) . " isotopes:");
			foreach($isotopes_percents as $ip){
				$isotope = $ip[0];
				$percentage = $ip[1];

				$LOG->debug("   : $percentage% $isotope");

				$isotopeValue = $amount->getCurie_level() * ($percentage / 100);
				$isotopeName = $isotope->getName();
				$isotopeId   = $isotope->getKey_id();
				$isMass   = $isotope->getIs_mass();

				// Apply isotope values to our collection
				if(!array_key_exists($isotopeName, $isotopeAmounts)){
					// Add empty entry for our collection
					$isotopeAmount = new IsotopeAmountDTO();
					$isotopeAmount->setIsotope_name($isotopeName);
					$isotopeAmount->setIs_mass($isMass);
					$isotopeAmount->setCurie_level(0);

					$isotopeAmounts[$isotopeName] = $isotopeAmount;
				}

				// Add value to the existing entry
				$isotopeAmounts[$isotopeName]->addCuries($isotopeValue);
			}
		}

		return array_values($isotopeAmounts);
	}

}
?>
