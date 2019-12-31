<?php
class Test_RoomType implements I_Test {
    public function setup(){}

    public function test__getAll(){
        $types = RoomType::getAll();

        Assert::not_null($types, 'RoomTypes are defined');
        Assert::eq(count($types), 3, '3 RoomTypes are defined');
    }

    public function test__of_types_exist(){
        $typenames = [
            RoomType::RESEARCH_LAB,
            RoomType::ANIMAL_FACILITY,
            RoomType::TEACHING_LAB
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
        $type = RoomType::of(RoomType::ANIMAL_FACILITY);
        Assert::true( $type->isInspectable(), 'Animal Facilities are inspectable');
        Assert::not_empty( $type->getRestrictedToDepartments(), 'Animal Facilities are restricted to Departments');
        Assert::eq( count($type->getRestrictedToDepartments()), 1, 'Animal Facilities are restricted to 1 Department');

        $dept = $type->getRestrictedToDepartments()[0];
        Assert::true( stristr($dept->getName(), 'DLAR'), 'Animal Facilities is restricted to DLAR department');
    }
}
?>
