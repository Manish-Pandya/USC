<?php
require_once '../top_view.php';
?>
<script src="../../js/userHub.js"></script>
<style>
.modal-body .controls .ui-select-container{
    /* Override ui-select-container floating in user hub modals */
    float:unset;
}
</style>

<style>
  #userHub .hub-banner .banner-nav li {
    max-width: 200px;
    text-align: center;
  }
</style>

<span ng-app="userList"  ng-controller="MainUserListController" id="userHub" class="hub-theme-green">

    <hub-banner-nav
        hub-title="User Hub"
        hub-icon="icon-user-2"
        hub-views="userHubViews">
    </hub-banner-nav>

    <div class="hub-toolbar">
        <div class="even-content">
            <div class="loading" ng-if="!neededUsers">
                <i class="icon-spinnery-dealie spinner large"></i>
                <span>Loading Users...</span>
            </div>

            <div class="control-group" style="margin-left: auto;">
                <a class="btn btn-primary left" ng-click="openUserLookupModal()">
                    Find a User<i class="icon-magnifying-glass"></i>
                </a>
            </div>
        </div>

        <h2 class="alert alert-danger" ng-if="error">{{error}}</h2>
    </div>

   <ng-view></ng-view>
</span>

<?php
require_once '../bottom_view.php';
?>
