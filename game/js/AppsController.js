 	function AppListCtrl($scope, $http, $templateCache) {
 		$scope.listApps = function() {
 			$http({method: 'GET', url: 'http://playitcool.fablabsaigon.org/rest/teams', cache: $templateCache}).
  				success(function(data, status, headers, config) {
    		    	$scope.teams = data.teams;                  //set view model
    		    	$scope.view = './Partials/teamReport.html'; //set to list view
  				}).
  				error(function(data, status, headers, config) {
  					$scope.apps = data || "Request failed";
  					$scope.status = status;
  					$scope.view = './Partials/teamReport.html';
  				});
  		}
  			
/*  		$scope.showApp = function(id) {
  			$http({method: 'GET', url: './api.php?action=get_app&id=' + id, cache: $templateCache}).
  				success(function(data, status, headers, config) {
  					$scope.appDetail = data;               //set view model
  					$scope.view = './Partials/detail.html'; //set to detail view
  				}).
  				error(function(data, status, headers, config) {
  					$scope.appDetail = data || "Request failed";
  					$scope.status = status;
  					$scope.view = './Partials/detail.html';
  				});
  		}
  		*/
  		$scope.view = './Partials//teamReport.html'; //set default view
  		$scope.listApps();
 	}
 	AppListCtrl.$inject = ['$scope', '$http', '$templateCache'];
