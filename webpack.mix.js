let mix = require('laravel-mix');

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

mix
    .options({
        terser:   {
            terserOptions:   {
                mangle:   false,
                output:   {
                    comments: false,
                },
                compress: {
                    drop_console: false,
                },
            },
            extractComments: false,
        },
        cssNano:  {
            discardComments: {
                removeAll: true,
            },
        },
        cleanCss: {
            level: {
                1: {
                    specialComments: 'none',
                },
            },
        },
    })
    .version()

    .js('resources/assets/js/app.js', 'public/build')
    .sass('resources/assets/sass/app.scss', 'public/build')

;
