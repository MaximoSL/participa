angular.module( 'madisonApp.directives' )
    .directive( 'docComments', function () {
        return {
            restrict    : 'AECM',
            templateUrl : '/consulta-public/templates/doc-comments.html'
        };
    });
