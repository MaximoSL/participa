var elixir = require('laravel-elixir');

elixir.config.sourcemaps = false;

elixir(function(mix) {
  mix
      .sass('app.scss')
      .scripts([
        'bower_components/jquery/dist/jquery.js',
        'bower_components/bootstrap/dist/js/bootstrap.js',
        'bower_components/underscore/underscore.js',
        'bower_components/jquery.ui/ui/effect.js',
        'bower_components/jquery.ui/ui/effect-highlight.js',
        'bower_components/select2/select2.js',

        //'bower_components/google-diff-match-patch-js/diff_match_patch.js',
        'bower_components/angular/angular.js',
        'bower_components/angular-animate/angular-animate.js',
        'bower_components/angular-bootstrap/ui-bootstrap.js',
        'bower_components/angular-bootstrap/ui-bootstrap-tpls.js',
        'bower_components/angular-cookies/angular-cookies.js',
        'bower_components/angular-ui/build/angular-ui.js',
        'bower_components/angular-ui-select/dist/select.js',
        'bower_components/zeroclipboard/dist/ZeroClipboard.js',
        'bower_components/angular-growl-2/build/angular-growl.js',
        'bower_components/angular-sanitize/angular-sanitize.js',
        'bower_components/angular-resource/angular-resource.js',
        'bower_components/angular-route/angular-route.js',
        'bower_components/angular-i18n/angular-locale_es-mx.js',
        'bower_components/pagedown/Markdown.Converter.js',
        'bower_components/pagedown/Markdown.Sanitizer.js',
        'bower_components/pagedown/Markdown.Editor.js',
        'bower_components/crypto-js/index.js',
        'bower_components/google-translate/index.txt',
        //'bower_components/angular-tour/dist/angular-tour.min.js',
        //'bower_components/angular-tour/dist/angular-tour-tpls.min.js',
        'bower_components/angular-cookie/angular-cookie.js',
        'bower_components/angular-translate/angular-translate.js',

        // Datetimepicker and dependencies
        //'public/vendor/datetimepicker/datetimepicker.js',
        'bower_components/moment/min/moment-with-locales.js',
        'bower_components/angular-bootstrap-datetimepicker/src/js/datetimepicker.js',

        // Annotator JS
        'bower_components/annotator/annotator-full.min.js',
        'bower_components/showdown/dist/showdown.js',
        'resources/assets/js/annotator-madison.js',

        // Custom JS
        'resources/assets/js/controllers/module.js',
        'resources/assets/js/controllers/**/*.js',
        'resources/assets/js/dashboard/module.js',
        'resources/assets/js/dashboard/**/*.js',
        'resources/assets/js/resources/module.js',
        'resources/assets/js/resources/**/*.js',
        'resources/assets/js/services/module.js',
        'resources/assets/js/services/**/*.js',
        'resources/assets/js/directives/module.js',
        'resources/assets/js/directives/**/*.js',
        'resources/assets/js/filters/module.js',
        'resources/assets/js/filters/**/*.js',
        'resources/assets/js/annotationServiceGlobal.js',
        'resources/assets/js/app.js',
        'resources/assets/js/googletranslate.js',
      ], 'public/dist/js/app.js', './')
      .version(['public/dist/css/app.css', 'public/dist/js/app.js'])
      .copy('bower_components/bootstrap-sass/assets/fonts/bootstrap/', 'public/fonts/');;
});
