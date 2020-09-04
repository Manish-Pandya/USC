<?php
class IsotopeDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new Isotope());

    }

    public function getIsotopeTotalsReport(){
		$this->LOG->info('Building Isotope Report');

        $queryString = "SELECT
			isotope_id,
			isotope_name,
			auth_limit,
			COALESCE(SUM( parcel_quantity_ordered ), 0) AS total_ordered,
			SUM( total_disposed ) AS disposed,
			SUM( total_waste ) AS waste,
			COALESCE(SUM( parcel_quantity_current ), 0) - SUM( total_disposed ) AS total_quantity,
			COALESCE(SUM( parcel_quantity_current ), 0) - SUM( total_disposed ) - SUM( total_waste ) AS total_unused
		FROM (
			SELECT
				isotope_id,
				isotope_name,
				auth_limit,
				parcel_id,
				parcel_quantity AS parcel_quantity_ordered,
				parcel_quantity - COALESCE(SUM( transfer_amount ), 0) AS parcel_quantity_current,
				COALESCE(SUM( use_quantity ), 0) AS amount,
				COALESCE(SUM( disposed_amount ), 0) AS total_disposed,
				COALESCE(SUM( waste_amount ), 0) AS total_waste
			FROM(
				-- per-isotope usages
				SELECT
					iso.key_id AS isotope_id,
					iso.name AS isotope_name,
					iso.auth_limit AS auth_limit,
					parcel.key_id AS parcel_id,
					parcel.quantity AS parcel_multinuclide_quantity,
 					(parcel.quantity * (parcel_authorization.percentage / 100)) as parcel_quantity,
					parcel_authorization.percentage AS isotope_percentage,
                    parcel.comments AS parcel_comments,

					-- per-isotope use data
                    use_log.key_id AS use_log_id,
 					(amt.curie_level * (parcel_authorization.percentage / 100)) as use_quantity,
                    amt.key_id AS use_amt_id,
                    amt.comments AS transfer_comments,

					-- per-isotope waste data
					IF(container.is_disposed = 1, (amt.curie_level * (parcel_authorization.percentage / 100)), 0) AS disposed_amount,
					IF(amt.waste_type_id = 6, (amt.curie_level * (parcel_authorization.percentage / 100)), 0) AS transfer_amount,
					IF(amt.waste_type_id != 6 && IFNULL(container.is_disposed, 0) = 0, (amt.curie_level * (parcel_authorization.percentage / 100)), 0) AS waste_amount
				FROM isotope iso

				-- parcel_authorization of authorized isotopes
				LEFT OUTER JOIN parcel_authorization parcel_authorization
				  ON parcel_authorization.authorization_id in (SELECT key_id FROM authorization WHERE isotope_id = iso.key_id)

				-- parcel in status which puts it on-premises
				LEFT OUTER JOIN parcel parcel
				  ON parcel_authorization.parcel_id = parcel.key_id
				 AND parcel.status IN('Arrived', 'Wipe Tested', 'Delivered')
	
				LEFT OUTER JOIN parcel_use use_log ON parcel.key_id = use_log.parcel_id

				-- Granulate to individual usage amounts
				LEFT OUTER JOIN parcel_use_amount amt ON amt.is_active AND use_log.key_id = amt.parcel_use_id

				-- Generalize container usages, determine if they're disposed or not
				LEFT OUTER JOIN (
					SELECT
						c.type AS type,
						c.key_id AS key_id,
						c.close_date AS close_date,
						c.pickup_id AS pickup_id,
						c.drum_id AS drum_id,
						c.carboy_pour_date AS carboy_pour_date,
						drum.pickup_date AS drum_ship_date,
						IF(	c.carboy_pour_date IS NOT NULL
							OR (c.drum_id IS NOT NULL AND drum.pickup_date IS NOT NULL)
							OR (c.type = 'other_waste_container' AND c.close_date IS NOT NULL), 1, 0
                        ) AS is_disposed
					FROM (
						SELECT 'scint_vial_collection' AS type, key_id, pickup_id, close_date, drum_id, NULL AS carboy_status, NULL AS carboy_pour_date FROM scint_vial_collection   UNION ALL
						SELECT 'waste_bag'             AS type, key_id, pickup_id, close_date, drum_id, NULL AS carboy_status, NULL AS carboy_pour_date FROM waste_bag               UNION ALL
						SELECT 'other_waste_container' AS type, key_id, pickup_id, close_date, drum_id, NULL AS carboy_status, NULL AS carboy_pour_date FROM other_waste_container   UNION ALL
						SELECT 'carboy_use_cycle'      AS type, key_id, pickup_id, close_date, drum_id, status AS carboy_status, pour_date AS carboy_pour_date FROM carboy_use_cycle 
					) c
					LEFT OUTER JOIN drum drum ON drum.key_id = c.drum_id
		
				) container ON (
					(container.type = 'scint_vial_collection' AND container.key_id = amt.scint_vial_collection_id)
					OR (container.type = 'waste_bag' AND container.key_id = amt.waste_bag_id)
					OR (container.type = 'other_waste_container' AND container.key_id = amt.other_waste_container_id)
					OR (container.type = 'carboy_use_cycle' AND container.key_id = amt.carboy_id)
				)

			) package
		
			GROUP BY package.isotope_id, package.parcel_id
		
		) inventory
		
		GROUP BY isotope_id
		ORDER BY isotope_name";

		$this->LOG->debug("Executing: $queryString");

        $stmt = DBConnection::prepareStatement($queryString);

        if( !$stmt->execute() ){
			$this->LOG->error("ERROR executing inventory report");
			$this->LOG->error($stmt->errorInfo());
		}
		$inventories = $stmt->fetchAll(PDO::FETCH_CLASS, "RadReportDTO");

		// 'close' the statement
		$stmt = null;

		$this->LOG->info(count($inventories) . ' Resulting Isotope Inventories');
		if($this->LOG->isTraceEnabled()){
			$this->LOG->trace($inventories);
		}

        return $inventories;
	}

	public function getCurrentInvetoriesByPiId( $piId, $authId ){

		$summaryQueryString = "SELECT
		pi_auth.principal_investigator_id as principal_investigator_id,
		authorization.isotope_id,
		authorization.key_id as authorization_id,
		SUM(parcel.quantity * (parcel_authorization.percentage / 100)) as ordered,
		isotope.name as isotope_name,
		authorization.max_quantity as auth_limit,

		-- Max Order = auth_limit - amount_on_hand
        authorization.max_quantity - (SUM(parcel.quantity * (parcel_authorization.percentage / 100)) - picked_up.amount_picked_up - amount_transferred.amount_used) as max_order,

		other_disposed.other_amount_disposed as _other_disposed,
		COALESCE(picked_up.amount_picked_up, 0) as _picked_up,
		amount_transferred.amount_used as _transferred,

		COALESCE(picked_up.amount_picked_up, 0) + COALESCE(other_disposed.other_amount_disposed, 0) as amount_picked_up,

		-- On Hand = total_ordered - amount_picked_up - amount_transferred
		SUM(parcel.quantity * (parcel_authorization.percentage / 100)) - picked_up.amount_picked_up - amount_transferred.amount_used as amount_on_hand,

		COALESCE(total_used.amount_used, 0) as amount_disposed,
		COALESCE(total_used.amount_used, 0) as total_used,

		-- Usable = total_ordered - amount_disposed - amount_transferred
		SUM(parcel.quantity * (parcel_authorization.percentage / 100)) - total_used.amount_used - amount_transferred.amount_used as usable_amount,
		amount_transferred.amount_used as amount_transferred

		from pi_authorization pi_auth

		LEFT OUTER JOIN authorization authorization
		ON authorization.pi_authorization_id = pi_auth.key_id

		LEFT OUTER JOIN isotope isotope
		ON isotope.key_id = authorization.isotope_id

		LEFT OUTER JOIN parcel_authorization parcel_authorization
		ON parcel_authorization.authorization_id = authorization.key_id

		LEFT OUTER JOIN parcel parcel
		ON parcel.key_id = parcel_authorization.parcel_id AND parcel.status IN ('Delivered')

		LEFT OUTER JOIN (
			select sum(amt.curie_level * (parcel_authorization.percentage / 100)) as amount_picked_up,
			iso.name as isotope,
			iso.key_id as isotope_id
			from parcel_use_amount amt
			join parcel_use parcel_use
				on amt.parcel_use_id = parcel_use.key_id
			JOIN parcel parcel
				ON parcel_use.parcel_id = parcel.key_id
			JOIN parcel_authorization parcel_authorization
				ON parcel.key_id = parcel_authorization.parcel_id
			JOIN authorization auth
				ON parcel_authorization.authorization_id = auth.key_id
			JOIN isotope iso
				ON auth.isotope_id = iso.key_id
			left join waste_bag waste_bag
				ON amt.waste_bag_id = waste_bag.key_id
			left join carboy_use_cycle cycle
				ON amt.carboy_id = cycle.key_id
			left join scint_vial_collection svc
				ON amt.scint_vial_collection_id = svc.key_id
			left join other_waste_container owc
				ON amt.other_waste_container_id = owc.key_id
			left join pickup pickup
				ON waste_bag.pickup_id = pickup.key_id
				OR cycle.pickup_id = pickup.key_id
				OR svc.pickup_id = pickup.key_id
			WHERE pickup.principal_investigator_id = ?
				AND (pickup.status != 'REQUESTED' OR owc.close_date IS NOT NULL)
				AND parcel_use.is_active = 1
				AND amt.is_active = 1
			group by iso.name, iso.key_id, auth.isotope_id
		) as picked_up
		ON picked_up.isotope_id = authorization.isotope_id

		LEFT OUTER JOIN (
			select sum(amt.curie_level * (parcel_authorization.percentage / 100)) as other_amount_disposed,
			iso.name as isotope,
			iso.key_id as isotope_id
			from parcel_use_amount amt
			join parcel_use parcel_use
				on amt.parcel_use_id = parcel_use.key_id AND parcel_use.is_active = 1
			JOIN parcel parcel
				ON parcel_use.parcel_id = parcel.key_id
			JOIN parcel_authorization parcel_authorization
				ON parcel.key_id = parcel_authorization.parcel_id
			JOIN authorization auth
				ON parcel_authorization.authorization_id = auth.key_id
			JOIN isotope iso
				ON auth.isotope_id = iso.key_id
			join other_waste_container owc
				ON amt.other_waste_container_id = owc.key_id AND owc.close_date IS NOT NULL
			WHERE parcel.principal_investigator_id = ?
				AND parcel_use.is_active = 1
				AND amt.is_active = 1
			group by iso.name, iso.key_id, auth.isotope_id
		) other_disposed
		ON other_disposed.isotope_id = authorization.isotope_id

		-- RSMS-780 Join to experiment uses (do not check parcel_use_amount, as this is too granular since parcel_use specifis enough)
		LEFT OUTER JOIN (
			select sum(parcel_use.quantity * (parcel_authorization.percentage / 100)) as amount_used,
			iso.name as isotope,
			iso.key_id as isotope_id
			from parcel_use parcel_use
			JOIN parcel parcel
				ON parcel_use.parcel_id = parcel.key_id
			JOIN parcel_authorization parcel_authorization
				ON parcel.key_id = parcel_authorization.parcel_id
			JOIN authorization auth
				ON parcel_authorization.authorization_id = auth.key_id
			JOIN isotope iso
				ON auth.isotope_id = iso.key_id
			WHERE parcel.principal_investigator_id = ?
				AND parcel_use.date_transferred IS NULL
				AND parcel_use.is_active = 1
			group by iso.name, iso.key_id, auth.isotope_id
		) as total_used
		ON total_used.isotope_id = authorization.isotope_id


		LEFT OUTER JOIN (
			select sum(amt.curie_level * (parcel_authorization.percentage / 100)) as amount_used,
			iso.name as isotope,
			iso.key_id as isotope_id
			from parcel_use_amount amt
			join parcel_use parcel_use
				on amt.parcel_use_id = parcel_use.key_id
			JOIN parcel parcel
				ON parcel_use.parcel_id = parcel.key_id
			JOIN parcel_authorization parcel_authorization
				ON parcel.key_id = parcel_authorization.parcel_id
			JOIN authorization auth
				ON parcel_authorization.authorization_id = auth.key_id
			JOIN isotope iso
				ON auth.isotope_id = iso.key_id
			WHERE parcel.principal_investigator_id = ?
				AND amt.waste_type_id = 6
			group by iso.name, iso.key_id, auth.isotope_id
		) as amount_transferred
		ON amount_transferred.isotope_id = authorization.isotope_id

		where pi_auth.key_id = ?

		group by authorization.isotope_id, isotope.name, isotope.key_id, pi_auth.principal_investigator_id";

		$queryString = "SELECT
			principal_investigator_id,
			isotope_id,
			authorization_id,
			ordered,
			isotope_name,
			auth_limit,
			summary.auth_limit - (summary.ordered - summary.amount_picked_up - summary.amount_transferred) as max_order,
			_other_disposed,
			_picked_up,
			_transferred,
			amount_picked_up,
			(summary.ordered - summary.amount_picked_up - summary.amount_transferred) as amount_on_hand,
			amount_disposed,
			(summary.ordered - summary.total_used - summary.amount_transferred) as usable_amount
			amount_transferred

			FROM ($summaryQueryString) summary";

        $stmt = DBConnection::prepareStatement($queryString);

        $stmt->bindValue(1, $piId);
        $stmt->bindValue(2, $piId);
        $stmt->bindValue(3, $piId);
        $stmt->bindValue(4, $piId);
		$stmt->bindValue(5, $authId);

		if( $this->LOG->isDebugEnabled()){
			$this->LOG->debug("Executing SQL with params piId=$piId: " . $queryString);
		}

		if( !$stmt->execute() ){
			$errorInfo = $stmt->errorInfo();
			$object = new ModifyError($errorInfo[2], $object);
			$this->LOG->fatal('Error: ' . $object->getMessage());
			throw new Exception($object->getMessage());
		}

        $inventories = $stmt->fetchAll(PDO::FETCH_CLASS, "CurrentIsotopeInventoryDto");

		// 'close' the statement
		$stmt = null;

        return $inventories;
	}
}
?>
