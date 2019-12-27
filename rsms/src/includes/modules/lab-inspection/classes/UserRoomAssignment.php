<?php

class UserRoomAssignment extends GenericCrud {
    public const TABLE_NAME = 'user_room_assignment';
    
	protected const COLUMN_NAMES_AND_TYPES = [
        "key_id" => "integer",
        "room_id" => "integer",
        "user_id" => "integer",
        "role_name" => "text"
    ];

    private $room_id;
    private $user_id;
    private $role_name;

    // Transients
    private $room;
    private $user;
    private $role;

	public function getTableName(){
		return UserRoomAssignment::TABLE_NAME;
	}

	public function getColumnData(){
		return UserRoomAssignment::COLUMN_NAMES_AND_TYPES;
    }

    public function getRoom_id(){ return $this->room_id; }
    public function setRoom_id($val){ $this->room_id = $val; }

    public function getUser_id(){ return $this->user_id; }
    public function setUser_id($val){ $this->user_id = $val; }

    public function getRole_name(){ return $this->role_name; }
    public function setRole_name($val){ $this->role_name = $val; }

    public function getRoom(){
        if( $this->room == null && $this->hasPrimaryKeyValue() ){
            $this->room = QueryUtil::selectFrom( new Room() )
                ->where(Field::create('key_id', 'room'), '=', $this->room_id);
        }

        return $this->room;
    }

    public function getUser(){
        if( $this->user == null && $this->hasPrimaryKeyValue() ){
            $this->user = QueryUtil::selectFrom( new User() )
                ->where(Field::create('key_id', 'erasmus_user'), '=', $this->user_id);
        }

        return $this->user;
    }

    public function getRole(){
        if( $this->role == null && $this->hasPrimaryKeyValue() ){
            $this->role = QueryUtil::selectFrom( new Role() )
                ->where(Field::create('name', 'role'), '=', $this->role_id);
        }

        return $this->role;
    }
}

?>
