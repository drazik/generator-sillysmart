'use strict';

var gulp = require('gulp');
var parseArgs = require('minimist');
var plumber = require('gulp-plumber');
var util = require('gulp-util');
var imagemin = require('gulp-imagemin');
var newer = require('gulp-newer');
var config = require('../config/images');

var args = parseArgs(process.argv);

gulp.task('images', function() {
    gulp.src(config.src)
        .pipe(args.production ? util.noop() : plumber())
        .pipe(newer(config.dest))
        .pipe(imagemin(config.imagemin))
        .pipe(gulp.dest(config.dest));
});
