<?php
// Inject dynamic RoomType definitions into Contstants
echo '<script type="text/javascript">(function(){';

echo 'if( !window.Constants ){
        /* Define constants object if undefined */
        window.Constants = {};
    }

    // Extend existing Constants with RoomTypes values
    let Constants = window.Constants;

    Constants.ROOM_TYPE = {';

    foreach( RoomType::getAll() as $type ){
        $name = $type->getName();
        $label = $type->getLabel();
        $inspectable = $type->isInspectable() ? 'true' : 'false';

        // Build array of quoted department name strings
        $depts = implode(',', array_map(function($d){ return '"' . $d->getName() . '"'; }, $type->getRestrictedToDepartments()));
        
        echo "$name:{";
        echo  "name:'$name',";
        echo  "label:'$label',";
        echo  "inspectable:$inspectable,";
        echo  "departments:[$depts]";
        echo "},\n";
    }
echo '};';

echo '})();</script>';
?>
