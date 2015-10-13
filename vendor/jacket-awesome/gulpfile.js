var gulp = require('gulp'),
    replace = require('gulp-replace'),
    concat = require('gulp-concat'),
    minifyCSS = require('gulp-minify-css');

gulp.task('css', function() {
    gulp.src(['./vendor/entypo/font/entypo.css', './additional.src.css'])
        .pipe(concat('jacket-awesome.css'))
        .pipe(replace('icon-', 'xn-'))
        .pipe(replace('.icon', '.xn'))
        .pipe(replace('.xn-right-open-big:before', '.xn-nav:before, .xn-right-open-big:before'))
        .pipe(replace('.xn-cancel:before', '.xn-close:before, .xn-cancel:before'))
        .pipe(replace('url(\'', 'url(\'../fonts/'))
        .pipe(gulp.dest('dist/css'));

    gulp.src(['./vendor/entypo/font/entypo.css', './additional.src.css'])
        .pipe(concat('jacket-awesome.min.css'))
        .pipe(replace('icon-', 'xn-'))
        .pipe(replace('.icon', '.xn'))
        .pipe(replace('.xn-right-open-big:before', '.xn-nav:before, .xn-right-open-big:before'))
        .pipe(replace('.xn-cancel:before', '.xn-close:before, .xn-cancel:before'))
        .pipe(replace('url(\'', 'url(\'../fonts/'))
        .pipe(minifyCSS())
        .pipe(gulp.dest('dist/css'));
});

gulp.task('fonts', function() {
    gulp.src(['./vendor/entypo/font/entypo.svg',
             './vendor/entypo/font/entypo.ttf',
             './vendor/entypo/font/entypo.eot',
             './vendor/entypo/font/entypo.woff'])
        .pipe(gulp.dest('dist/fonts'));
});

gulp.task('default', ['css', 'fonts']);
