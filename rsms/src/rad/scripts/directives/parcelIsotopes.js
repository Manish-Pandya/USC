var myApp = angular.module('00RsmsAngularOrmApp');
myApp.directive("parcelIsotopes", function () {
    return {
        restrict: 'E',
        scope: {
            parcel: "="
        },
        template:
`<ul class="isotope-list">
    <li ng-repeat="pauth in parcel.ParcelAuthorizations">
        <span ng-if="parcel.ParcelAuthorizations.length != 1">{{pauth.Percentage}}%</span>
        <span>{{pauth.Isotope.Name}}</span>
        <span>({{pauth.Authorization.Form}})</span>
    </li>
</ul>`
     }
});
