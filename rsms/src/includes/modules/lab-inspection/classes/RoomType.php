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
                RoomType::RESEARCH_LAB => new RoomType( RoomType::RESEARCH_LAB, 'Research Lab', true, 'icon-lab'),
                RoomType::ANIMAL_FACILITY => new RoomType( RoomType::ANIMAL_FACILITY, 'Animal Facility', true, $animal_img, false),
                RoomType::TEACHING_LAB => new RoomType( RoomType::TEACHING_LAB, 'Teaching Lab', false, 'icon-users')
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
    private $is_inspectable;
    private $icon_class;
    private $img_path;

	private function __construct( $name, $label, $is_inspectable, $glyphPath, $glyphIsIcon=TRUE ){
		$this->name = $name;
		$this->label = $label;
        $this->is_inspectable = $is_inspectable;

        if( $glyphIsIcon ){
            $this->icon_class = $glyphPath;
        }
        else {
            $this->img_path = $glyphPath;
        }
	}

    public function __toString(){
        return '['
            . get_class($this)
            . " $this->name"
        . ']';
    }

	public function getName(){ return $this->name; }
	public function getLabel(){ return $this->label; }
    public function isInspectable(){ return $this->is_inspectable; }
    public function getIcon_class(){ return $this->icon_class; }
    public function getImg_path(){ return $this->img_path; }

    public function getRestrictedToDepartments(){
        $inspectionTypeDeptsRel = DataRelationship::fromArray(RoomType::DEPT_ROOM_TYPE_RELATIONSHIP);

        return QueryUtil::selectFrom( new Department() )
            ->joinTo($inspectionTypeDeptsRel)
            ->where(Field::create('inspect_room_type', $inspectionTypeDeptsRel->getTableName()), '=', $this->name)
            ->getAll();
    }
}
?>
