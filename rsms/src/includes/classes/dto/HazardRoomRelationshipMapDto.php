<?php

/**
 * A dto class to send a group of hazard room relationships from the client to the server
 *
 *
 * @author Matthew Breeden, GraySail LLC
 */
class HazardRoomRelationshipMapDto {
	private $hazard_id;
	private $room_ids;
	private $add;

	public function getHazard_id()
	{
	    return $this->hazard_id;
	}

	public function setHazard_id($hazard_id)
	{
	    $this->hazard_id = $hazard_id;
	}

	public function getRoom_ids()
	{
	    return $this->room_ids;
	}

	public function setRoom_ids($rooms_ids)
	{
	    $this->rooms_ids = $rooms_ids;
	}

	public function getAdd()
	{
	    return $this->add;
	}

	public function setAdd($add)
	{
	    $this->add = $add;
	}
}