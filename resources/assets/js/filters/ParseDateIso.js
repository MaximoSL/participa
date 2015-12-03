angular.module( 'madisonApp.filters' )
    .filter( 'parseDateIso', function () {
        return function ( date ) {
            return Date.parse(date.replace(' ', 'T'));
        };
    });
