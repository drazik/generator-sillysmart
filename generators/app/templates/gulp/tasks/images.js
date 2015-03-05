'use strict';

var gulp = require('gulp');
var plumber = require('gulp-plumber');
var util = require('gulp-util');
var imagemin = require('gulp-imagemin');
var newer = require('gulp-newer');

gulp.task('images', function() {
    var args = {production:false};

    gulp.src('Public/Style/Img.uncompressed/**/*.{png,jpg,jpeg,gif,svg}')
        .pipe(args.production ? util.noop() : plumber())
        .pipe(newer('Public/Style/Img'))
        .pipe(imagemin({
            progressive: true
        }))
        .pipe(gulp.dest('Public/Style/Img'));
});
