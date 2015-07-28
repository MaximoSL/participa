angular.module('madisonApp.controllers')
  .controller('EmailSubscribeController', ['$scope', '$http', function ($scope, $http) {
    $scope.email = '';
    $scope.successMessage = false;
    $scope.subscribeEmail = function () {
      $http.post('http://www.gob.mx/subscribe', { email: $scope.email })
        .success(function (data) {
          $scope.successMessage = true;
        }).error(function (data) {
          console.error( "Unable to mark activity as seen: %o", data );
        });
    };
  }]);
