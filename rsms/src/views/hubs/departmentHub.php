<?php
require_once '../top_view.php';
?>
    <script src="../../js/departmentHub.js"></script>

    <div class="navbar">
        <ul class="nav pageMenu purpleBg" style="min-height: 50px; color:white !important; padding: 4px 0 0 0; width:100%">
            <li class="">
                <i class="pull-left fa fa-university fa-4x" style="height:50px;margin: 4px 10px 0;"></i>
                <h2 style="padding: 11px 0 5px 85px;">Department Hub
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
        <select ng-options="campus.Name as campus.Name for campus in campuses | orderBy:'Name'" ng-model="selectedCampus" ng-show="departments">
            <option value="">All Campuses</option>
        </select>
        <table class="userList table table-rowspan-stripe piTable table-bordered span12" style="margin-left:0; float:none; width:1270px" ng-if="departments">
            <THEAD>
                <tr>
                    <th class="greenBg" colspan="5">
                        <h3 class="card-header padding greenBg">Departments
                        <a ng-click="openModal(null, false)" class="btn btn-small left" style="margin-left:10px" ng-if="!creatingDepartment && departments"><i class="icon-plus-5"></i>Add New Department</a>
                    </h3>
                    </th>
                </tr>
                <tr ng-hide="!filteredDepts.length">
                    <th>Edit</th>
                    <th>Department</th>
                    <th>Campus</th>
                    <th style="text-align:center;"># Principal Investigators</th>
                    <th style="text-align:center;"># Lab Rooms</th>
                </tr>
            </THEAD>
            <tbody ng-repeat="department in filteredDepts = (departments | matchDepartmentWithCampus:selectedCampus | specialtyLab_trueFalse:false | orderBy: ['Department_name'])">
                <tr ng-class="{inactive:!department.Is_active}">
                    <td rowspan="{{deptCampuses.length || 1}}" style="width:9%;">
                        <a class="btn btn-primary left" ng-click="openModal(department)" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a>
                        <a ng-click="handleActive(department)" class="btn" ng-class="{'btn-danger':department.Is_active,'btn-success':!department.Is_active}">
                            <span ng-if="department.Is_active" alt="Deactivate" title="Deactivate"><i class="icon-remove"></i></span>
                            <span ng-if="!department.Is_active"><i class="icon-checkmark-2"></i></span>
                        </a>
                        <i class="icon-spinnery-dealie spinner small" style="margin-left:5px; margin-top:5px; position:absolute" ng-show="department.isDirty" />
                    </td>
                    <td rowspan="{{deptCampuses.length || 1}}" style="width:27%;">
                        {{department.Department_name}}
                    </td>
                    <td style="width:20%;">
                        {{deptCampuses[0].Campus_name}}
                    </td>
                    <td style="width:22%; text-align:center;">{{deptCampuses[0].Pi_count}}</td>
                    <td style="width:10%; text-align:center;">{{deptCampuses[0].Room_count}}</td>
                </tr>
                <tr ng-if="!$first" ng-repeat="campus in deptCampuses = (department.Campuses | matchCampus:selectedCampus)" class="center-block" ng-class="{inactive:!department.Is_active}">
                    <td style="width:20%;">
                        {{campus.Campus_name}}
                    </td>
                    <td style="width:22%; text-align:center;">{{campus.Pi_count}}</td>
                    <td style="width:10%; text-align:center;">{{campus.Room_count}}</td>
                </tr>
            </tbody>
            <tbody ng-show="!filteredDepts.length">
                <tr><td colspan="5"><h3>No Departments in {{selectedCampus}}</h3></td></tr>
            </tbody>
        </table>

        <table class="userList table table-rowspan-stripe piTable table-bordered span12" style="margin-left:0; float:none;width:1270px" ng-if="departments" >
            <THEAD>
                <tr>
                    <th class="greenBg" colspan="5">
                        <h3 class="card-header padding greenBg">Specialty Labs
                        <a ng-click="openModal(null, true)" class="btn btn-small left" style="margin-left:10px" ng-if="!creatingDepartment && departments"><i class="icon-plus-5"></i>Add New Specialty Lab</a>
                    </h3>
                    </th>
                </tr>
                <tr ng-hide="!labs.length">
                    <th>Edit</th>
                    <th>Specialty Lab</th>
                    <th>Campus</th>
                    <th style="text-align:center;"># Principal Investigators</th>
                    <th style="text-align:center;"># Lab Rooms</th>
                </tr>
            </THEAD>
            <tbody ng-repeat="department in labs = (departments | matchDepartmentWithCampus:selectedCampus | specialtyLab_trueFalse:true | orderBy: 'Department_name')">
                <tr ng-class="{inactive:!department.Is_active}">
                    <td rowspan="{{labCampuses.length || 1}}" style="width:9%;">
                        <a class="btn btn-primary left" ng-click="openModal(department)" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a>
                        <a ng-click="handleActive(department)" class="btn" ng-class="{'btn-danger':department.Is_active,'btn-success':!department.Is_active}">
                            <span ng-if="department.Is_active" alt="Deactivate" title="Deactivate"><i class="icon-remove"></i></span>
                            <span ng-if="!department.Is_active"><i class="icon-checkmark-2"></i></span>
                        </a>
                        <i class="icon-spinnery-dealie spinner small" style="margin-left:5px; margin-top:5px; position:absolute" ng-show="department.isDirty" />
                    </td>
                    <td rowspan="{{labCampuses.length || 1}}" style="width:27%;">
                        {{department.Department_name}}
                    </td>
                    <td style="width:20%;">
                        {{labCampuses[0].Campus_name}}
                    </td>
                    <td style="width:22%; text-align:center;">{{labCampuses[0].Pi_count}}</td>
                    <td style="width:10%; text-align:center;">{{labCampuses[0].Room_count}}</td>
                </tr>
                <tr ng-if="!$first" ng-repeat="campus in labCampuses = (department.Campuses | matchCampus:selectedCampus)" class="center-block" ng-class="{inactive:!department.Is_active}">
                    <td style="width:20%;">
                        {{campus.Campus_name}}
                    </td>
                    <td style="width:22%; text-align:center;">{{campus.Pi_count}}</td>
                    <td style="width:10%; text-align:center;">{{campus.Room_count}}</td>
                </tr>
            </tbody>
            <tbody ng-show="!labs.length">
                <tr><td colspan="5"><h3>No Specialty Labs in {{selectedCampus}}</h3></td></tr>
            </tbody>
        </table>

        <div>&nbsp;</div>
    </div>
    <div class="bottomMargin" style="clear:both;">&nbsp;</div>
    <?php
require_once '../bottom_view.php';
?>
