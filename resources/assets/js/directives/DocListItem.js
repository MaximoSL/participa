angular.module( 'madisonApp.directives' )
    .directive( 'docListItem', function() {
        return {
            restrict    : 'A',
            templateUrl : _baseUrl + '/templates/doc-list-item.html'
        };
    });
