<?php
require_once '../top_view.php';
?>
<script src="../../js/departmentHub.js"></script>

<div class="navbar">
    <ul class="nav pageMenu purpleBg" style="min-height: 50px; color:white !important; padding: 4px 0 0 0; width:100%">
        <li class="">
            <i class="pull-left fa fa-university fa-4x" style="height:50px;margin: 4px 10px 0;"></i>
            <h2  style="padding: 11px 0 5px 85px;">Department Hub
                <a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>
            </h2>
        </li>
    </ul>
</div>
<div class="whiteBg" ng-app="departmentHub" ng-controller="departmentHubController" style="padding-top:60px">
    <h2 class="alert alert-danger" ng-if="error">{{error}}</h2>
    <span ng-if="!departments && !error" class="loading">
       <img style="width:100px"src="<?php echo WEB_ROOT?>img/loading.gif"/>
      Loading Departments
    </span>
    <div class="" style="margin-bottom:14px;">
        <a ng-click="openModal()" class="btn btn-success btn-large left" ng-if="!creatingDepartment && departments"><i class="icon-plus-5"></i>Add New Department</a>
        <span ng-if="creatingDepartment" style="width:100%; display: block;">
            <input style="width:50%" ng-model="newDepartment.Name">
            <span style="width:50%">
                <a class="btn-success btn left" ng-click="saveNewDepartment(newDepartment)"><i class="icon-checkmark"></i>Save</a>
                <a class="btn-danger btn left" ng-click="cancelEdit(newDepartment)"><i class="icon-cancel"></i>Cancel</a>
                <img ng-show="newDepartment.isDirty" class="smallLoading" src="../../img/loading.gif"/>
            </span>
        </span>
    </div>

    <table class="userList table table-striped table-hover piTable table-bordered span12" style="margin-left:0; float:none;" ng-if="departments">
        <THEAD>
            <tr>
                <th>Edit</th>
                <th>Departments</th>
                <th style="text-align:center;"># Principal Investigators</th>
                <th style="text-align:center;"># Laboratory Rooms</th>
            </tr>
        </THEAD>
        <tbody>
            <tr ng-repeat="(key, department) in departments | specialtyLab_trueFalse:false | orderBy: 'Department_name'" class="center-block" ng-class="{inactive:!department.Is_active}">
                <td style="width:11%;">
                    <a class="btn btn-primary left" ng-click="openModal(department)" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a>
                    <a ng-click="handleActive(department)" class="btn" ng-class="{'btn-danger':department.Is_active,'btn-success':!department.Is_active}">
                        <span ng-if="department.Is_active"alt="Deactivate" title="Deactivate"><i class="icon-remove"></i></span>
                        <span ng-if="!department.Is_active"><i class="icon-checkmark-2"></i></span>
                    </a>
                    <i class="icon-spinnery-dealie spinner small" style="margin-left:5px; margin-top:5px; position:absolute" ng-show="department.isDirty"/>
                </td>
                <td style="width:35%;">
                    {{department.Department_name}}
                </td>
                <td style="width:27%; text-align:center;">{{department.Pi_count}}</td>
                <td style="width:22%; text-align:center;">{{department.Room_count}}</td>
            </tr>
        </tbody>
    </table>
    
    <table class="userList table table-striped table-hover piTable table-bordered span12" style="margin-left:0; float:none;" ng-if="departments">
        <THEAD>
            <tr>
                <th class="greenBg" colspan="4">
                    <h3 class="card-header padding greenBg">Specialty Labs</h3>
                    <a ng-click="openModal(null, true)" class="btn btn-success btn-small left" ng-if="!creatingDepartment && departments"><i class="icon-plus-5"></i>Add New Specialty Lab</a>
                </th>
            </tr>
            <tr>
                <th>Edit</th>
                <th>Departments</th>
                <th style="text-align:center;"># Principal Investigators</th>
                <th style="text-align:center;"># Laboratory Rooms</th>
            </tr>
        </THEAD>
        <tbody>
            <tr ng-repeat="(key, department) in departments | specialtyLab_trueFalse:true | orderBy: 'Department_name'" class="center-block" ng-class="{inactive:!department.Is_active}">
                <td style="width:11%;">
                    <a class="btn btn-primary left" ng-click="openModal(department)" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a>
                    <a ng-click="handleActive(department)" class="btn" ng-class="{'btn-danger':department.Is_active,'btn-success':!department.Is_active}">
                        <span ng-if="department.Is_active"alt="Deactivate" title="Deactivate"><i class="icon-remove"></i></span>
                        <span ng-if="!department.Is_active"><i class="icon-checkmark-2"></i></span>
                    </a>
                    <i class="icon-spinnery-dealie spinner small" style="margin-left:5px; margin-top:5px; position:absolute" ng-show="department.isDirty"/>
                </td>
                <td style="width:35%;">
                    {{department.Department_name}}
                </td>
                <td style="width:27%; text-align:center;">{{department.Pi_count}}</td>
                <td style="width:22%; text-align:center;">{{department.Room_count}}</td>
            </tr>
        </tbody>
    </table>
    <div>&nbsp;</div>
</div>
<div class="bottomMargin" style="clear:both;">&nbsp;</div>
<?php
require_once '../bottom_view.php';
?>
