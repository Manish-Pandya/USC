<?php if( isset($_SESSION['USER']) ) { ?>
    <div class="user-info no-print" ng-controller="roleBasedCtrl" style="text-align: center; <?php echo ApplicationConfiguration::get('server.env.style', '') ?>">
        <span style="float:left;">Signed in as <?php echo $_SESSION['USER']->getName(); ?></span>
        <span style="font-style: italic;"><?php echo RSMS_ENV_DETAILS; ?></span>
        <a style="float:right;" href="<?php echo WEB_ROOT?>action.php?action=logoutAction">Sign Out</a>
    </div>
<?php }?>
