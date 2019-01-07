<?php if( isset($_SESSION['USER']) ) { ?>
    <?php $isImpersonating = isset($_SESSION['IMPERSONATOR']); ?>
    <div class="user-info no-print" ng-controller="roleBasedCtrl" style="text-align: center; <?php echo ApplicationConfiguration::get('server.env.style', '') ?>">
        <span style="float:left;">Signed in as <?php echo $_SESSION['USER']->getName(); ?></span>
        <span style="float:left; font-style:italic; font-weight:bold; padding-left:5px;"><?php
            if( $isImpersonating ){
                echo " (impersonated by " . $_SESSION['IMPERSONATOR']['USER']->getName() . ')';
            }
        ?></span>
        <span style="font-style: italic;"><?php echo RSMS_ENV_DETAILS; ?></span>
        <?php
            if( $isImpersonating ){
                ?><a style="float:right;" href="<?php echo WEB_ROOT?>action.php?action=stopImpersonating">Stop Impersonating</a><?php
            }
            else {
                ?><a style="float:right;" href="<?php echo WEB_ROOT?>action.php?action=logoutAction">Sign Out</a><?php
            }
        ?>
    </div>
<?php }?>
