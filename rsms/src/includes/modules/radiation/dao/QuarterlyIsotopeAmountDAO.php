<?php

class QuarterlyIsotopeAmountDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new QuarterlyIsotopeAmount());
    }

	/**
	 * Gets the sum of ParcelUseAmounts for a given authorization for a given date range with a given waste type
	 *
	 * @param string $startDate mysql timestamp formatted date representing beginning of the period
	 * @param string $enddate mysql timestamp formatted date representing end of the period
	 * @param integer $wasteTypeId Key id of the appropriate waste type
	 * @return int $sum
	 */
	public function getUsageAmounts( $piId, $isotopeId, $startDate, $endDate, $wasteTypeId ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // TODO: Generate this statment to only look at the required type
        /** Subquery to retrieve all used waste containers (which are no longer in a lab) */
        $sql_select_all_waste = "SELECT
                all_waste.key_id AS container_id,
                all_waste.waste_type_id AS waste_type_id
            FROM (
                SELECT key_id, pickup_id, 3 as waste_type_id FROM scint_vial_collection UNION
                SELECT key_id, pickup_id, 5 as waste_type_id FROM waste_bag UNION
                SELECT key_id, pickup_id, 1 as waste_type_id FROM carboy_use_cycle
            ) AS all_waste
            INNER JOIN pickup ON (all_waste.pickup_id IS NOT NULL AND pickup.key_id = all_waste.pickup_id AND pickup.status != 'REQUESTED')
            UNION SELECT key_id, 4 as waste_type_id FROM other_waste_container other WHERE other.close_date IS NOT NULL";

        $sql_select_usages = "SELECT
                (amt.curie_level * (pauth.percentage / 100)) as curie_level,
                amt.waste_type_id,
                COALESCE(amt.waste_bag_id, amt.scint_vial_collection_id, amt.carboy_id, amt.other_waste_container_id) as amt_container_id,
                p.principal_investigator_id,
                auth.isotope_id

            -- based on ParcelUseAmount
            FROM parcel_use_amount amt
            -- Join to Amount's ParcelUse
            LEFT JOIN parcel_use pu            ON amt.parcel_use_id = pu.key_id
            -- Join to Use's Parcel
            LEFT JOIN parcel p                 ON pu.parcel_id = p.key_id
            -- Join with parcel's ParcelAuths
            LEFT JOIN parcel_authorization pauth ON pauth.parcel_id = p.key_id
            -- Join with Authorizations
            LEFT JOIN authorization auth       ON auth.key_id = pauth.authorization_id
            -- Join to Authorization's PIAuthorization
            LEFT JOIN pi_authorization pia     ON auth.pi_authorization_id = pia.key_id
            -- Join with all containers
            INNER JOIN ($sql_select_all_waste) wastes ON (
                   (wastes.waste_type_id = 5 AND wastes.container_id = amt.waste_bag_id)
                OR (wastes.waste_type_id = 3 AND wastes.container_id = amt.scint_vial_collection_id)
                OR (wastes.waste_type_id = 1 AND wastes.container_id = amt.carboy_id)
                OR (wastes.waste_type_id = 4 AND wastes.container_id = amt.other_waste_container_id)
            )

            WHERE pu.is_active = 1
                AND pia.principal_investigator_id = ?
                AND auth.isotope_id = ?
                AND (
                    (pu.date_used BETWEEN ? AND ? AND pu.date_used != '0000-00-00 00:00:00')
                    OR (pu.date_transferred BETWEEN ? AND ? AND pu.date_transferred != '0000-00-00 00:00:00')
                )
                AND amt.waste_type_id = ?";

        $sql = "SELECT COALESCE(SUM(curie_level), 0) FROM ($sql_select_usages) usages";

        if( $LOG->isTraceEnabled() ){
            $LOG->trace($sql);
        }

		$stmt = DBConnection::prepareStatement($sql);
		$stmt->bindValue(1, $piId);
        $stmt->bindValue(2, $isotopeId);
		$stmt->bindValue(3, $startDate);
		$stmt->bindValue(4, $endDate);
        $stmt->bindValue(5, $startDate);
		$stmt->bindValue(6, $endDate);
		$stmt->bindValue(7, $wasteTypeId);
        $stmt->execute();

        $total = $stmt->fetch(PDO::FETCH_NUM);
		$sum = $total[0]; // 0 is the first array. here array is only one.
		//if($sum == NULL)$sum = 0;

		// 'close' the statment
        $stmt = null;

        $LOG->debug("Calculated usage of type $wasteTypeId of Isotope #$isotopeId for PI #$piId from [$startDate to $endDate]: $sum");

		return $sum;
	}

    /**
     * Gets the sum of Parcels ordered in or ordered before a given period
     *
     * @param string $startDate mysql timestamp formatted date representing beginning of the period
     * @param string $enddate mysql timestamp formatted date representing end of the period
     * @return int $sum
     */
	public function getStartingAmount( $piId, $isotopeId, $startDate = null ){
        $l = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
		$sql = "SELECT SUM( (parcel.quantity * (parcel_authorization.percentage / 100)) ) as iso_total
                FROM parcel parcel
                JOIN parcel_authorization parcel_authorization ON parcel_authorization.parcel_id = parcel.key_id
                WHERE `parcel_authorization`.`authorization_id` IN (
                    select key_id from authorization where principal_investigator_id = ? AND isotope_id = ?
                )";

        $l->debug("Get PI $piId's starting amount of isotope $isotopeId");

        if($startDate != null){
            $sql .= " AND (parcel.arrival_date < ? OR parcel.transfer_in_date < ?)";
        }

        $l->debug($sql);
        $stmt = DBConnection::prepareStatement($sql);
		$stmt->bindValue(1, $piId);
        $stmt->bindValue(2, $isotopeId);

        if($startDate != null) {
            // Bind date(s)
            $stmt->bindValue(3, $startDate);
            $stmt->bindValue(4, $startDate);
        }

        if ( $stmt->execute() ) {

            $total = $stmt->fetch(PDO::FETCH_NUM);
            //$l->fatal($total);

            $sum = $total[0]; // 0 is the first array. here array is only one.

            //get the total waste disposed for this authorization and subtract
            if($startDate != null){
                $sql = "SELECT SUM( (use_amount.curie_level * (parcel_authorization.percentage / 100)) ) as iso_total
                        FROM `parcel_use_amount` use_amount
                        LEFT JOIN parcel_use parcel_use
                        ON use_amount.parcel_use_id = parcel_use.key_id
                        LEFT JOIN parcel parcel
                        ON parcel_use.parcel_id = parcel.key_id
                        LEFT JOIN parcel_authorization parcel_authorization
                        ON parcel.key_id = parcel_authorization.parcel_id
                        LEFT JOIN waste_bag f
                        ON use_amount.waste_bag_id = f.key_id
                        LEFT JOIN carboy_use_cycle g
                        ON use_amount.carboy_id = g.key_id
                        LEFT JOIN scint_vial_collection h
                        ON use_amount.scint_vial_collection_id = h.key_id
                        LEFT OUTER JOIN pickup i
                        ON f.pickup_id = i.key_id
                        OR g.pickup_id = i.key_id
                        OR h.pickup_id = i.key_id
                        AND i.status != 'Requested'
				        WHERE parcel_authorization.authorization_id IN (select key_id from authorization where principal_investigator_id = ? AND isotope_id = ?)
                        AND parcel_use.is_active = 1
				        AND (((parcel_use.date_used < ? AND parcel_use.date_used != '0000-00-00 00:00:00')
                        AND (f.pickup_id IS NOT NULL OR g.pickup_id IS NOT NULL OR h.pickup_id IS NOT NULL ))
				        OR (parcel_use.date_transferred < ? AND parcel_use.date_transferred != '0000-00-00 00:00:00'))";

                $stmt = DBConnection::prepareStatement($sql);
                $stmt->bindValue(1, $piId);
                $stmt->bindValue(2, $isotopeId);
                $stmt->bindValue(3, $startDate);
                $stmt->bindValue(4, $startDate);
                if ( $stmt->execute() ) {
                    $totalDisposals = $stmt->fetch(PDO::FETCH_NUM);
                    $disp = $totalDisposals[0];
                    $sum = $sum - $disp;
                }
            }


            if($sum == NULL)$sum = 0;


        }else{
			// 'close' the statment
			$stmt = null;

            $error = $stmt->errorInfo();
			$result = new QueryError($error);
			$l->error('Returning QueryError with message: ' . $result->getMessage());
            return $result;
		}

		// 'close' the statment
		$stmt = null;

        $l->debug("Calculated starting amount of $isotopeId for PI $piId" . ($startDate ? " (constrained to $startDate)" : '') . " is: $sum");
		return $sum;
	}

	/**
	 * Gets the sum of Parcels transfered in or ordered durring a given period
	 *
	 * @param string $startDate mysql timestamp formatted date representing beginning of the period
	 * @param string $enddate mysql timestamp formatted date representing end of the period
	 * @param bool $hasTransferDate true if we are looking for parcels with an transfer_in_date (those that count as transfer), false if those without one (parcels that count as orders), or null for all parcels
	 * @return int $sum
	 */
	public function getTransferAmounts( $piId, $isotopeId, $startDate, $endDate, $hasTransferDate = null ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // get the PI's total quantity of a single isotope
		$sql = "SELECT SUM( (parcel.quantity * (parcel_authorization.percentage / 100)) ) as iso_total
				FROM `parcel`
                JOIN `parcel_authorization`
                  ON `parcel_authorization`.`parcel_id` = `parcel`.`key_id`

				WHERE `parcel_authorization`.`authorization_id` IN (
                    SELECT auth.key_id
                    FROM authorization auth JOIN pi_authorization pia ON auth.pi_authorization_id = pia.key_id
                    WHERE pia.principal_investigator_id = ? AND auth.isotope_id = ?
                )";

        if($hasTransferDate == true){
            // Transfers
            $sql .= " AND transfer_in_date BETWEEN ? AND ?";
        }
        elseif($hasTransferDate != true){
            // Orders
            $sql .= " AND transfer_in_date IS NULL AND `arrival_date` BETWEEN ? AND ?";
        }

        $sql .= " GROUP BY parcel_authorization.authorization_id";

        $LOG->debug($sql);

		// Get the db connection
		$stmt = DBConnection::prepareStatement($sql);
		$stmt->bindValue(1, $piId);
        $stmt->bindValue(2, $isotopeId);
		$stmt->bindValue(3, $startDate);
		$stmt->bindValue(4, $endDate);

		$stmt->execute();

		$total = $stmt->fetch(PDO::FETCH_NUM);
		$sum = $total[0]; // 0 is the first array. here array is only one.
		if($sum == NULL)$sum = 0;

		// 'close' the statment
		$stmt = null;

        $LOG->debug("Calculated " . ($hasTransferDate ? 'transfer ' : 'ordered ') . "amounts of isotope #$isotopeId for PI #$piId between [$startDate and $endDate] is: $sum");
		return $sum;
	}

    /**
     * Gets the sum of Parcels transfered in or ordered durring a given period
     *
     * @param string $startDate mysql timestamp formatted date representing beginning of the period
     * @param string $enddate mysql timestamp formatted date representing end of the period
     * @param bool $hasTransferDate true if we are looking for parcels with an transfer_in_date (those that count as transfer), false if those without one (parcels that count as orders), or null for all parcels
     * @return int $sum
     */
	public function getTransferOutAmounts( $piId, $isotopeId, $startDate, $endDate ){
		$sql = "SELECT SUM( (parcel_use.quantity * (parcel_authorization.percentage / 100)) ) as iso_total
				FROM `parcel_use`
                JOIN `parcel` ON `parcel`.`key_id` = `parcel_use`.`parcel_id`
                JOIN `parcel_authorization` ON `parcel_authorization`.`parcel_id` = `parcel`.`key_id`

                WHERE `parcel_authorization`.`authorization_id` IN (
                    SELECT auth.key_id
                    FROM authorization auth JOIN pi_authorization pia ON auth.pi_authorization_id = pia.key_id
                    WHERE pia.principal_investigator_id = ? AND auth.isotope_id = ?
                )

				AND `date_transferred` BETWEEN ? AND ?";

		$stmt = DBConnection::prepareStatement($sql);
		$stmt->bindValue(1, $piId);
        $stmt->bindValue(2, $isotopeId);
		$stmt->bindValue(3, $startDate);
		$stmt->bindValue(4, $endDate);

		$stmt->execute();

		$total = $stmt->fetch(PDO::FETCH_NUM);
		$sum = $total[0]; // 0 is the first array. here array is only one.
		if($sum == NULL)$sum = 0;

		// 'close' the statment
		$stmt = null;

		return $sum;
	}
}
?>