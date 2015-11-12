angular.module( 'madisonApp.directives' )
    .directive( 'commentItem', [ 'growl', function ( growl ) {
        return {
            restrict    : 'A',
            transclude  : true,
            templateUrl : '/consulta-public/templates/comment-item.html',
            compile     : function () {
                return {
                    post: function ( scope, element, attrs ) {
                        var commentLink = element.find( '.comment-link' ).first();
                        var linkPath    = _currentPath + '#' + attrs.activityItemLink;
                        $( commentLink ).attr( 'data-clipboard-text', linkPath );

                        var client      = new ZeroClipboard( commentLink );
                        client.on( 'aftercopy', function ( event ) {
                            scope.$apply( function () {
                                growl.success( "Link copied to clipboard 2." );
                            });
                        });

                        var $span       = $( element ).find( '.activity-icon > span.ng-binding' );
                        $span.on( "click", function() {
                            $( element ).parent().effect( "highlight",{
                                color   : "#2276d7"
                            }, 1000 );
                        });
                    }
                };
            }
        };
    }]);
