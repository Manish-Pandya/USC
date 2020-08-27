<?php
require_once '../../Application.php';
require_once '../../RequireUserLoggedIn.php';
require_once '../top_view.php';
?>

<script type="text/javascript" src="../../js/lib/angular-ui-router.min.js"></script>

<script src="../../js/lab/myLab.js"></script>
<script src="widgets/my-lab-widget.js"></script>

<script src="../../login/scripts/directives/UserAccessRequestTable.js"></script>

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/mylab.css"/>

<div ng-app="myLab" ng-controller="MyLabAppCtrl" class="hub-theme-green" ng-cloak>
    <div cg-busy="{promise:inspectionPromise,message:'Loading', backdrop:true,templateUrl:'../../rad/views/busy-templates/full-page-busy.html'}"></div>

    <hub-banner-nav
        hub-title="Laboratory Dashboard"
        hub-views="mylabViews">
    </hub-banner-nav>

    <div ui-view class="overlay-container" style="margin-top: 10px;"></div>
</div>
