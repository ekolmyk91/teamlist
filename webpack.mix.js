const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// mix.react('resources/js/app.js', 'public/js')
//    .sass('resources/sass/app.scss', 'public/css');


mix.react('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');


// mix.js(['resources/js/admin/admin.js'], 'public/js')
//     .sass('resources/sass/admin/admin.scss', 'public/css');

mix.js(['resources/js/front/frontapp.js'], 'public/js')
    .sass('resources/sass/front/application.scss', 'public/css');


if (mix.inProduction()) {
    mix.version();
}