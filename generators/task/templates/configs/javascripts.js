'use strict';

module.exports = function(config) {
    config.javascripts = {
        src: 'Public/Scripts/Javascript/Dyn/Uncompressed/app.js',
        dest: 'Public/Scripts/Javascript/Dyn',
        outputName: 'app.js'
    };

    return config;
};