<?php if($_SESSION['USER'] != NULL){ ?>
    <div class="user-info no-print" ng-controller="roleBasedCtrl">
        <div>
            Signed in as <?php echo $_SESSION['USER']->getName(); ?>
            <a style="float:right;" href="<?php echo WEB_ROOT?>action.php?action=logoutAction">Sign Out</a>
        </div>
    </div>
<?php }?>
