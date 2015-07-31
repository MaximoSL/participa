angular.module( 'madisonApp.directives' )
    .directive( 'docComments', function () {
        return {
            restrict    : 'AECM',
            templateUrl : _baseUrl + '-public/templates/doc-comments.html'
        };
    });
