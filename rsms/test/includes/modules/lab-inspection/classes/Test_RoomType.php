<?php
class Test_RoomType implements I_Test {
    public function setup(){}

    public function test__getAll(){
        $types = RoomType::getAll();

        Assert::not_null($types, 'RoomTypes are defined');
        Assert::eq(count($types), 4, '4 RoomTypes are defined');
    }

    public function test__of_types_exist(){
        $typenames = [
            RoomType::RESEARCH_LAB,
            RoomType::ANIMAL_FACILITY,
            RoomType::TEACHING_LAB,
            RoomType::TRAINING_ROOM
        ];

        foreach( $typenames as $name ){
            $type = RoomType::of($name);
            Assert::not_null($type, "RoomType '$name' exists");
        }
    }

    public function test__research_lab(){
        $type = RoomType::of(RoomType::RESEARCH_LAB);
        Assert::true( $type->isInspectable(), 'Research Labs are inspectable');
        Assert::empty( $type->getRestrictedToDepartments(), 'Research Labs are not restricted to any Department');
    }

    public function test__teaching_lab(){
        $type = RoomType::of(RoomType::TEACHING_LAB);
        Assert::false( $type->isInspectable(), 'Teaching Labs are not inspectable');
        Assert::empty( $type->getRestrictedToDepartments(), 'Teaching Labs are not restricted to any Department');
    }

    public function test__animal_lab(){
        // Given that DLAR Department exists
        $dept = new Department();
        $dept->setName('DLAR');
        $deptDao = new GenericDAO($dept);
        $dept = $deptDao->save($dept);

        $allDepts = $deptDao->getAll();
        Assert::not_empty($allDepts, 'Departments exist');
        Assert::not_null($dept->getKey_id(), 'DLAR Department has key_id');

        $insert_dlar_mapping = DBConnection::prepareStatement('INSERT INTO ' . RoomType::DEPT_MAPPING_TABLE_NAME . ' (department_id, inspect_room_type)'
            . ' VALUES (' . $dept->getKey_id() . ', \'' . RoomType::ANIMAL_FACILITY . '\');');
        $insert_dlar_mapping->execute();

        $type = RoomType::of(RoomType::ANIMAL_FACILITY);
        Assert::true( $type->isInspectable(), 'Animal Facilities are inspectable');
        Assert::not_empty( $type->getRestrictedToDepartments(), 'Animal Facilities are restricted to Departments');
        Assert::eq( count($type->getRestrictedToDepartments()), 1, 'Animal Facilities are restricted to 1 Department');

        $dept = $type->getRestrictedToDepartments()[0];
        Assert::true( stristr($dept->getName(), 'DLAR'), 'Animal Facilities is restricted to DLAR department');
    }

    public function test__training_room(){
        $type = RoomType::of(RoomType::TRAINING_ROOM);
        Assert::false( $type->isInspectable(), 'Training Rooms are not inspectable');
        Assert::empty( $type->getRestrictedToDepartments(), 'Training Rooms are not restricted to any Department');
        Assert::null( $type->getAssignable_to(), 'Training Rooms are not assignable to any user');
    }
}
?>
