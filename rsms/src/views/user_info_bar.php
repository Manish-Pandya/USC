<?php if($_SESSION['USER'] != NULL){ ?>
    <div class="user-info no-print" ng-controller="roleBasedCtrl" style="text-align: center">
        <span style="float:left;">Signed in as <?php echo $_SESSION['USER']->getName(); ?></span>
        <span style="font-style: italic;"><?php echo RSMS_VERSION_DETAILS; ?></span>
        <a style="float:right;" href="<?php echo WEB_ROOT?>action.php?action=logoutAction">Sign Out</a>
    </div>
<?php }?>
