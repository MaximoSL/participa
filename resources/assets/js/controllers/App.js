angular.module('madisonApp.controllers')
  .controller('AppController', ['$rootScope', '$scope', 'UserService', function ($rootScope, $scope, UserService) {
    // Update page title
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
      $rootScope.pageTitle = current.$$route.title;
    });

    // Watch for user data change
    $scope.$on('userUpdated', function () {
      $scope.user = UserService.user;
    });

    // Load user data
    UserService.getUser();
  }]);
