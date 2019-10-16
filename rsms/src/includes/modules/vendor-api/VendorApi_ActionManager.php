<?php

class VendorApi_ActionManager {
    public function getAllCampuses(){
        $c = new GenericDto([
            'name' => 'Test Campus 1',
            'key_id' => 12345
        ]);

        return [$c];
    }
}
?>