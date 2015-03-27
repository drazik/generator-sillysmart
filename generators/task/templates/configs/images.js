'use strict';

module.exports = {
    src: 'Public/Style/Img.uncompressed/**/*.{png,jpg,jpeg,gif,svg}',
    dest: 'Public/Style/Img',
    imagemin: {
        progressive: true
    }
};