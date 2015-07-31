angular.module( 'madisonApp.directives' )
    .directive( 'profileCompletionMessage', [ '$http', function ( $http ) {
        return {
            restrict    : 'A',
            templateUrl : _baseUrl + '-public/templates/profile-completion-message.html',
            link        : function ( scope ) {
                scope.updateEmail   = function ( newEmail, newPassword ) {
                    //Issue PUT request to update user
                    $http.put(_baseUrl + '/api/user/' + scope.user.id + '/edit/email', {
                        email       : newEmail,
                        password    : newPassword
                    })
                        .success( function () {
                            //Note: Growl message comes from server response
                            scope.user.email = newEmail;
                        }).error( function ( data ) {
                            console.error( "Error updating user email: %o", data );
                            $('.update-email-error').html(data.messages[0].text);
                        });
                };
            }
        };
    }]);
