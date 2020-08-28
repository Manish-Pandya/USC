<?php
require_once '../../Application.php';
require_once '../../RequireUserLoggedIn.php';
require_once '../top_view.php';
?>

<script>
    var GLOBAL_WEB_ROOT = '<?php echo WEB_ROOT?>';

    var RoleRequirements = <?php
        $rules = new UserCategoryRules();
        echo JsonManager::encode( $rules->getUserCategoryRules() );
    ?>;

    // Dynamically apply the hub theme to the body so that modal(s) are styled
    (function(){
        $('body').addClass('hub-theme-blue');
    })();
</script>

<script type="text/javascript" src="../../js/lib/angular-ui-router.min.js"></script>

<script type="text/javascript" src="myLab.js"></script>
<script type="text/javascript" src="widgets/my-lab-widget.js"></script>

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/mylab.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>user-hub/user-hub-styles.css" />
<script type="text/javascript" src="<?php echo WEB_ROOT?>user-hub/scripts/UserHubApp.js"></script>
<script type="text/javascript" src="../../login/scripts/directives/UserAccessRequestTable.js"></script>

<div ng-app="myLab" ng-controller="MyLabAppCtrl" class="hub-theme-blue" ng-cloak>
    <div cg-busy="{promise:inspectionPromise,message:'Loading', backdrop:true,templateUrl:'../../rad/views/busy-templates/full-page-busy.html'}"></div>

    <hub-banner-nav
        hub-title="Laboratory Dashboard"
        hub-views="mylabViews">
    </hub-banner-nav>

    <div ui-view class="overlay-container" style="margin-top: 10px;"></div>
</div>
