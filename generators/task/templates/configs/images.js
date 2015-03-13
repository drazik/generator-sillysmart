'use strict';

module.exports = function(config) {
    config.images = {
        src: 'Public/Style/Img.uncompressed/**/*.{png,jpg,jpeg,gif,svg}',
        dest: 'Public/Style/Img',
        imagemin: {
            progressive: true
        }
    };

    return config;
};