'use strict';

var gulp = require('gulp');
var parseArgs = require('minimist');
var plumber = require('gulp-plumber');
var util = require('gulp-util');
var browserify = require('gulp-browserify');
var uglify = require('gulp-uglifyjs');
var config = require('../config/javascripts');

var args = parseArgs(process.argv);

gulp.task('javascripts', function() {
    gulp.src(config.src)
        .pipe(args.production ? util.noop : plumber())
        .pipe(browserify())
        .pipe(uglify(config.outputName))
        .pipe(gulp.dest(config.dest));
});