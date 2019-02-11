<?php
interface ISelectWithJoins {

    /**
     * @return array of DataRelationship
     */
    public function selectJoinReleationships();
}
?>
