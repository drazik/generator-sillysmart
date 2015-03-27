'use strict';

var gulp = require('gulp');
var parseArgs = require('minimist');
var plumber = require('gulp-plumber');
var util = require('gulp-util');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var rename = require('gulp-rename');
var sourcemaps = require('gulp-sourcemaps');
var config = require('../configs/styles');

var args = parseArgs(process.argv);

gulp.task('styles', function() {
    var sassOutputStyle = args.production ? 'compressed' : 'nested';

    gulp.src(config.src)
        .pipe(args.production ? util.noop() : plumber())
        .pipe(args.production ? util.noop() : sourcemaps.init())
        .pipe(sass({
            outputStyle: sassOutputStyle
        }))
        .pipe(args.production ? util.noop() : sourcemaps.write())
        .pipe(autoprefixer(config.autoprefixer))
        .pipe(args.production ? rename(config.outputName) : util.noop())
        .pipe(gulp.dest(config.dest));
});