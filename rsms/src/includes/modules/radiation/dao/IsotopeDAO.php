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
			ROUND( COALESCE(SUM( parcel_quantity_ordered ), 0), 7) AS total_ordered,
			ROUND( SUM( total_disposed ), 7) AS disposed,
			ROUND( SUM( total_waste ), 7) AS waste,
			ROUND( COALESCE(SUM( parcel_quantity_current ), 0) - SUM( total_disposed ), 7) AS total_quantity,
			ROUND( COALESCE(SUM( parcel_quantity_current ), 0) - SUM( total_disposed ) - SUM( total_waste ), 7) AS total_unused
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
				SELECT
					iso.key_id AS isotope_id,
					iso.name AS isotope_name,
					iso.auth_limit AS auth_limit,
					parcel.key_id AS parcel_id,
					parcel.quantity AS parcel_quantity,
                    parcel.comments AS parcel_comments,
                    use_log.key_id AS use_log_id,
					amt.curie_level AS use_quantity,
                    amt.key_id AS use_amt_id,
                    amt.comments AS transfer_comments,
					IF(container.is_disposed = 1, amt.curie_level, 0) AS disposed_amount,
                    IF(amt.waste_type_id = 6, amt.curie_level, 0) AS transfer_amount,
					IF(amt.waste_type_id != 6 && IFNULL(container.is_disposed, 0) = 0, amt.curie_level, 0) AS waste_amount
				FROM isotope iso
		
				LEFT OUTER JOIN parcel parcel ON
				parcel.authorization_id in (SELECT key_id FROM authorization WHERE isotope_id = iso.key_id)
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
}
?>
