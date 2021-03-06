'use strict';

var config = require('../../config');
var gulp = require('gulp');
var path = require('path');
var rev = require('gulp-rev');
var revNapkin = require('gulp-rev-napkin');

// 4) Rev and compress CSS and JS files (this is done after assets, so that if a
//    referenced asset hash changes, the parent hash will change as well
gulp.task('rev-js', function () {
  return gulp.src(path.join(config.buildPath, '**/*.js'))
    .pipe(rev())
    .pipe(gulp.dest(config.buildPath))
    .pipe(revNapkin({verbose: false}))
    .pipe(rev.manifest(path.join(config.buildPath, config.manifestFile), {merge: true}))
    .pipe(gulp.dest(''));
});
