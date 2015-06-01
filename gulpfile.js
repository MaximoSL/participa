var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
  mix
      .sass('app.scss')
      .scripts([
          'resources/assets/js/app.js',
          'resources/assets/js/**/*.js'
      ], 'public/dist/js/app.js', './')
      .version(['public/dist/css/app.css', 'public/dist/js/app.js']);
});
