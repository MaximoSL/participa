/*global window*/
window.jQuery = window.$;
$(function() {
  // Ajax Setup
  $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
      var token;
      if (! options.crossDomain) {
          token = $('meta[name="token"]').attr('content');
          if (token) {
              jqXHR.setRequestHeader('X-CSRF-Token', token);
          }
      }

      return jqXHR;
  });
});

var imports = [
    'madisonApp.filters',
    'madisonApp.services',
    'madisonApp.resources',
    'madisonApp.directives',
    'madisonApp.controllers',
    'madisonApp.dashboardControllers',
    'ui',
    'ui.bootstrap',
    'ui.bootstrap.datetimepicker',
    'ui.bootstrap.pagination',
    'ui.select',
    'ngAnimate',
    'ngCookies',
    'ngSanitize',
    'angular-growl',
    'ngResource',
    'ngRoute',
    'ipCookie',
    'pascalprecht.translate'
  ];

moment.locale('es');

var app = angular.module('madisonApp', imports);

// Add a prefix to all http calls
// app.config(function ($httpProvider) {
//   $httpProvider.interceptors.push(function ($q) {
//     return {
//       request: function (request) {
//         var doNotPrefix = [
//           'subcomment_renderer.html',
//           'template/',
//           'tour/'
//         ];
//         var shouldWeAvoidPrefix = function(element, index) {
//           return request.url.indexOf(element) > -1;
//         };
//
//         if ($.grep(doNotPrefix, shouldWeAvoidPrefix).length > 0) {
//           // templates included in angular-bootstrap
//           // e.g. angular.module("template/tabs/tabset.html",[])
//           // or defined as ng-templates
//         } else if (request.url.indexOf("templates/") < 0) {
//           request.url = "/participa/" + request.url;
//           request.url = request.url.replace(/\/\//g, "/");
//         } else {
//           request.url = "/" + request.url;
//           request.url = request.url.replace(/\/\//g, "/");
//         }
//         return request || $q.when(request);
//       }
//     };
//   });
// });

// app.config(['growlProvider', '$httpProvider', function (growlProvider, $httpProvider) {
//     //Set up growl notifications
//   growlProvider.messagesKey("messages");
//   growlProvider.messageTextKey("text");
//   growlProvider.messageSeverityKey("severity");
//   $httpProvider.responseInterceptors.push(growlProvider.serverMessagesInterceptor);
//   growlProvider.onlyUniqueMessages(true);
//   growlProvider.globalTimeToLive(5000);
//
//   // $routeProvider
//   //   .when(_baseUrl + '/user/edit/:user/notifications', {
//   //     templateUrl: _baseUrl + "/templates/pages/user-notification-settings.html",
//   //     controller: "UserNotificationsController",
//   //     title: "Notification Settings"
//   //   });
// }]);

// app.config(function ($locationProvider) {
//   $locationProvider.html5Mode(true);
// });

app.config(['$translateProvider', function ($translateProvider) {
  $translateProvider.translations('en', {
    'POSTED': 'Posted',
    'UPDATED': 'Updated'
  });

  $translateProvider.translations('es', {
    'POSTED': 'Publicación',
    'UPDATED': 'Última actualización'
  });

  $translateProvider.preferredLanguage('es');
}]);

window.console = window.console || {};
window.console.log = window.console.log || function () {};
