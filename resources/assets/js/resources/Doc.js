angular.module('madisonApp.resources')
    .factory('Doc', ['$resource', function($resource) {
        return $resource(_baseUrl + "/api/docs/:id", null, {
            query: {
                method  : 'GET',
                isArray : false
            }
        });
    }]);
