angular.module( 'madisonApp.directives' )
    .directive( 'docListItem', function() {
        return {
            restrict    : 'A',
            templateUrl : '/consulta-public/templates/doc-list-item.html'
        };
    });
