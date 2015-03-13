'use strict';

module.exports = function(config) {
    config.styles = {
        src: 'Public/Style/Scss/Global.scss',
        dest: 'Public/Style/Css',
        outputName: 'Global.min.css',
        autoprefixer: {
            browsers: [
                'last 2 versions',
                'Firefox ESR',
                'IE >= 7',
                'BlackBerry >= 7',
                'Android >= 2'
            ],
            cascade: false
        }
    };

    return config;
};