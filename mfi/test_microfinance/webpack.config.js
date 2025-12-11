const { assertSupportedNodeVersion } = require('../src/Engine');

module.exports = async () => {
    // @ts-ignore
    process.noDeprecation = true;

    assertSupportedNodeVersion();

    const mix = require('../src/Mix').primary;

    require(mix.paths.mix());

    await mix.installDependencies();
    await mix.init();

    return mix.build();
};



// webpack.mix.js
const mix = require('laravel-mix');

mix.webpackConfig({
    devtool: false, // Disable source map generation
});

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        // PostCSS plugins
    ]);

// Use Vite for building assets
if (mix.inProduction()) {
    mix.vite();
} else {
    mix.vite({
        // Set custom host for Vite development server
        host: 'testcyberpointpro.ubx.co.tz',
        // Set other configuration options as needed
    }).watch();
}
