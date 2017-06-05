const elixir = require('laravel-elixir');

elixir(function (mix) {
    mix
        // Build Sass assets.
        .sass('./src/scss/s8-zipper.scss', './assets/css/s8-zipper.css')
});