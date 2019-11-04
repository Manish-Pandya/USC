<?php
require_once '/var/www/html/rsms/Application.php';
?><html>
<head>
    <style>
        table {
            font-size: 12px;
        }

        th h2, th h4 {
            margin: 0;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr.heading {
            font-size:1.2em;
            background-color: #a7a6a6;
        }

        .secure-mapping { color: #009e00; }
        .insecure-mapping { color: #ff6c00; }

        .endpoint-name { color: #dedede; }
        .endpoint-name.override { color: #000000 }

        .non-declared { color: #ff0000; }
        .non-restricted { color: #ff0000; }

        pre.role {
            margin: 3px;
            padding: 1px;
            display: inline-block;
            background-color: lightgrey; 
        }

        pre.role:hover { background-color: #7fffd4; }
    </style>
</head>
<body>
    <h1>RSMS Endpoints</h1>
    <table>
        <?php
        $counter = 1;
foreach( ModuleManager::getAllModules() as $module ){
    // Get public methods declared directly by this manager
    $actionManager = $module->getActionManager();
    $reflection = new ReflectionClass( get_class($actionManager) );
    $methods = [];
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method){
        $methods[ $method->getName() ] = $method->getDeclaringClass()->getName();
    }

    $getDeclaringManager = function ( $fn_name ) use ($methods) {
        return $methods[$fn_name] ?? 'UNDEFINED';
    };

    ?>
    <tbody>
    <tr class="heading"><th colspan="11">
        <h2><?php echo $module->getModuleName(); ?> Module</h2>
        <h4><?php echo get_class($actionManager) ?></h4>
    </tr>

    <tr>
        <th>#</th>
        <th>Mapping Type</th>
        <th>Endpoint Name</th>
        <th>Function Name</th>
        <th>Restrict To Roles</th>
        <th>Roles (any)</th>
        <th>Security Precondition</th>
        <th>Success Page</th>
        <th>Success Code</th>
        <th>Error Page</th>
        <th>Error Code</th>
    </tr><?php
    foreach($module->getActionConfig() as $key => $action){
        $mappingtype_class = $action instanceof SecuredActionMapping ? 'secure-mapping' : 'insecure-mapping';
        $endpointOverride = $key != $action->actionFunctionName ? 'override' : '';
        $declaringManager = $getDeclaringManager($action->actionFunctionName);
        $declaringManagerCls = $declaringManager == get_class($actionManager) ? '' : 'non-declared';
        $restrictRolesCls = $action->checkRoles ? '' : 'non-restricted';

        $rowclass = '';
        if( $declaringManager == 'UNDEFINED' ){
            $rowclass .= 'error';
        }
        
        echo "<tr class='$rowclass'>";
        echo '<td>' . $counter++ . '</td>';

        echo "<td class='$mappingtype_class'>" . get_class($action) . "</td>";

        // Key could be different, but rarely is
        echo "<td class='endpoint-name $endpointOverride' style='text-align:right'>";
            echo $key;
        echo "</td>";

        echo "<td>";
            echo "<span class='$declaringManagerCls'>$declaringManager</span>";
            echo "::";
            echo "<span>" . $action->actionFunctionName . "</span>";
        echo "</td>";

        echo "<td class='$restrictRolesCls'>" . ($action->checkRoles ? 'YES' : 'NO') . "</td>";
        echo "<td>";
            foreach( $action->roles as $role ){
                echo "<pre class='role'>$role</pre>";
            }
        echo "</td>";

        echo "<td>";
            if( $action instanceof SecuredActionMapping){
                echo $action->preconditionFunction;
            }
        echo "</td>";

        echo "<td>" . $action->success_page . "</td>";
        echo "<td>" . $action->success_code . "</td>";
        echo "<td>" . $action->error_page . "</td>";
        echo "<td>" . $action->error_code . "</td>";

        echo '</tr>';
    }

    echo "</tbody>";
}
?></table>
</body>
</html>
