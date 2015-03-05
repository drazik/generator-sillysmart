'use strict';

var gulp = require('gulp');
var plumber = require('gulp-plumber');
var util = require('gulp-util');
var browserify = require('gulp-browserify');
var uglify = require('gulp-uglifyjs');

gulp.task('javascripts', function() {
    var args = {production:false};

    gulp.src('Public/Scripts/Javascript/Dyn/Uncompressed/app.js')
        .pipe(args.production ? util.noop : plumber())
        .pipe(browserify())
        .pipe(uglify('app.js'))
        .pipe(gulp.dest('Public/Scripts/Javascript/Dyn'));
});