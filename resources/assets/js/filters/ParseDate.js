angular.module( 'madisonApp.filters' )
    .filter( 'parseDate', function () {
        return function ( date ) {
            return Date.parse(date.replace(' ', 'T'));
        };
    });
