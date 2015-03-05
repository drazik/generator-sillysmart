'use strict';

var gulp = require('gulp');
var plumber = require('gulp-plumber');
var util = require('gulp-util');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var rename = require('gulp-rename');
var sourcemaps = require('gulp-sourcemaps');

gulp.task('styles', function() {
    // TODO gérer réellement les arguments
    var args = {production: false};
    var sassOutputStyle = args.production ? 'compressed' : 'nested';

    gulp.src('Public/Style/Scss/Global.scss')
        .pipe(args.production ? util.noop() : plumber())
        .pipe(args.production ? util.noop() : sourcemaps.init())
        .pipe(sass({
            outputStyle: sassOutputStyle
        }))
        .pipe(args.production ? util.noop() : sourcemaps.write())
        .pipe(autoprefixer({
            browsers: [
                'last 2 versions',
                'Firefox ESR',
                'IE >= 7',
                'BlackBerry >= 7',
                'Android >= 2'
            ],
            cascade: false
        }))
        .pipe(args.production ? rename('Global.min.css') : util.noop())
        .pipe(gulp.dest('./Public/Style/Css'));
});