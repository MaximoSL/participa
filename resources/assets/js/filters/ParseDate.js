angular.module( 'madisonApp.filters' )
    .filter( 'parseDate', function () {
        return function ( date ) {
            if(typeof date === 'string') {
                date = date.replace(/-/g, '/');
            }
            return Date.parse( date );
        };
    });
