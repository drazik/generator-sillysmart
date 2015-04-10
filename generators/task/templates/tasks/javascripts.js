'use strict';

var gulp = require('gulp');
var parseArgs = require('minimist');
var plumber = require('gulp-plumber');
var util = require('gulp-util');
var uglifyjs = require('gulp-uglifyjs');
var browserify = require('browserify');
var source = require('vinyl-source-stream');
var buffer = require('vinyl-buffer');
var config = require('../configs/javascripts');

var args = parseArgs(process.argv);

gulp.task('javascripts', function() {
    var b = browserify({
        entries: config.src,
        debug: !args.production
    });

    return b.bundle()
        .pipe(args.production ? util.noop() : plumber())
        .pipe(source(config.fileName))
        .pipe(buffer())
        .pipe(args.production ? uglifyjs() : util.noop())
        .pipe(gulp.dest(config.dest));
});