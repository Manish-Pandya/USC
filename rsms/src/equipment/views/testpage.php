<form>
    <input type="text" placeholder="Enter a loader to call" ng-model="loaderName">
    <input type="submit" ng-click="callLoader()">
</form>
<pre>{{testData | json}}</pre>
