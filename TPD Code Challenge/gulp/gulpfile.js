const { src, dest, task, parallel, series, watch } = require( 'gulp' );
var gulp = require('gulp'),
    plumber = require('gulp-plumber'),
    rename = require('gulp-rename');
var uglify = require('gulp-uglify');

task('watch', function(){
    watch("js/scripts.js", gulp.series('js'));
  });

task('js', function(){
  return gulp.src('js/scripts.js')
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))
    .pipe(rename({suffix: '.min'}))
    .pipe(uglify())
    .pipe(gulp.dest('dist/'))
});


