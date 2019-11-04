<?php
class TestActionManagerUtils implements I_Test {
    public function setup(){}

    private function create_inspection( $key ){
        $insp = new Inspection();
        $insp->setKey_id( $key );
        return $insp;
    }

    public function test__merge_entity_arrays(){

        $array1 = array(
            $this->create_inspection(1),
            $this->create_inspection(2),
            $this->create_inspection(3)
        );

        $array2 = array(
            $this->create_inspection(3),
            $this->create_inspection(4),
            $this->create_inspection(5)
        );

        Assert::eq( count($array1), 3, 'Array #1 includes 3 inspections');
        Assert::eq( count($array2), 3, 'Array #2 includes 3 inspections');

        $p_merged = array_merge($array1, $array2);
        Assert::eq( count($p_merged), 6, 'Merged array includes the duplicate inspections');

        $e_merged = ActionManager::merge_entity_arrays($array1, $array2);
        Assert::eq( count($e_merged), 5, 'Merged array includes only the distinct inspections');
    }

    public function test__merge_entity_arrays__emptyArrays(){
        $array1 = array( $this->create_inspection(3) );

        $merged = ActionManager::merge_entity_arrays(array(), $array1, array());
        Assert::eq( count($merged), 1, 'Merged array includes only the distinct inspections');
    }
}
?>
