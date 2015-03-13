'use strict';

var gulp = require('gulp');
var parseArgs = require('minimist');
var plumber = require('gulp-plumber');
var util = require('gulp-util');
var browserify = require('gulp-browserify');
var uglify = require('gulp-uglifyjs');

var args = parseArgs(process.argv);

gulp.task('javascripts', function() {
    gulp.src('Public/Scripts/Javascript/Dyn/Uncompressed/app.js')
        .pipe(args.production ? util.noop : plumber())
        .pipe(browserify())
        .pipe(uglify('app.js'))
        .pipe(gulp.dest('Public/Scripts/Javascript/Dyn'));
});