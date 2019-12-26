<?php
class RoomType {
    public const DEPT_MAPPING_TABLE_NAME = 'department_inspect_room_type';

	public const RESEARCH_LAB = 'RESEARCH_LAB';
	public const ANIMAL_FACILITY = 'ANIMAL_FACILITY';
	public const TEACHING_LAB = 'TEACHING_LAB';

	public const DEPT_ROOM_TYPE_RELATIONSHIP = array(
        "className"	=>	Department::class,
        "tableName"	=>	RoomType::DEPT_MAPPING_TABLE_NAME,
        "keyName"	=>	"key_id",
        "foreignKeyName"	=>	"department_id"
    );

	private static $TYPES;

    private static function init_types(){
        if( !isset(RoomType::$TYPES) ){
            $animal_img = WEB_ROOT . 'img/animal-facility.svg';

            RoomType::$TYPES = [
                RoomType::RESEARCH_LAB => (new RoomType())
                    ->setName(RoomType::RESEARCH_LAB)
                    ->setLabel('Research Lab')
                    ->setPluralLabel('Research Laboratories')
                    ->setInspectable(true)
                    ->setAssignable_to( PrincipalInvestigator::class )
                    ->setIcon_class('icon-lab')
                    ->setImg_path(null),

                RoomType::ANIMAL_FACILITY => (new RoomType())
                    ->setName(RoomType::ANIMAL_FACILITY)
                    ->setLabel('Animal Facility')
                    ->setPluralLabel('Animal Facilities')
                    ->setInspectable(true)
                    ->setAssignable_to( PrincipalInvestigator::class )
                    ->setIcon_class(null)
                    ->setImg_path($animal_img),

                RoomType::TEACHING_LAB => (new RoomType())
                    ->setName(RoomType::TEACHING_LAB)
                    ->setLabel('Teaching Lab')
                    ->setPluralLabel('Teaching Laboratories')
                    ->setInspectable(false)
                    ->setAssignable_to( User::class )
                    ->setIcon_class('icon-users')
                    ->setImg_path(null),
            ];
        }
    }

    public static function getAll(){
        RoomType::init_types();

        return array_values( RoomType::$TYPES );
    }

	public static function of( string $name ){
        RoomType::init_types();
		if( isset(RoomType::$TYPES[$name]) ){
			return RoomType::$TYPES[$name];
		}

		throw new Exception("Invalid Room Type '$name'");
	}

	private $name;
	private $label;
	private $plural_label;
    private $is_inspectable;
    private $icon_class;
    private $img_path;
    private $assignable_to;

    private function __construct(){}

    public function __toString(){
        return '['
            . get_class($this)
            . " $this->name"
        . ']';
    }

	public function getName(){ return $this->name; }
	public function getLabel(){ return $this->label; }
	public function getPluralLabel(){ return $this->plural_label; }
    public function isInspectable(){ return $this->is_inspectable; }
    public function getIcon_class(){ return $this->icon_class; }
    public function getImg_path(){ return $this->img_path; }
    public function getAssignable_to(){ return $this->assignable_to; }

    private function setName( $val ){
        $this->name = $val;
        return $this;
    }

	private function setLabel( $val ){
        $this->label = $val;
        return $this;
    }

	private function setPluralLabel( $val ){
        $this->plural_label = $val;
        return $this;
    }

    private function setInspectable( $val ){
        $this->is_inspectable = $val;
        return $this;
    }

    private function setIcon_class( $val ){
        $this->icon_class = $val;
        return $this;
    }

    private function setImg_path( $val ){
        $this->img_path = $val;
        return $this;
    }

    private function setAssignable_to( $val ){
        $this->assignable_to = $val;
        return $this;
    }

    public function getRestrictedToDepartments(){
        $inspectionTypeDeptsRel = DataRelationship::fromArray(RoomType::DEPT_ROOM_TYPE_RELATIONSHIP);

        return QueryUtil::selectFrom( new Department() )
            ->joinTo($inspectionTypeDeptsRel)
            ->where(Field::create('inspect_room_type', $inspectionTypeDeptsRel->getTableName()), '=', $this->name)
            ->getAll();
    }
}
?>
