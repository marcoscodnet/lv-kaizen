const gulp = require('gulp');
const del = require('del');

const { paths, baseDir, version } = require('./utils');

/*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
|  Clean
=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-*/
gulp.task('clean', () =>
    del([
        `${baseDir}/${paths.style.dest}/**/*.*`,
        `${baseDir}/${paths.script.dest}/**/*.*`,
        `!${baseDir}/${paths.script.dest}/combo-provincia-localidad.js`,
        `!${baseDir}/${paths.script.dest}/combo-provincia-localidad-modal.js`,
        `!${baseDir}/${paths.script.dest}/combo-sucursal-ubicacion.js`,
        `!${baseDir}/${paths.script.dest}/confirm-exit.js`,
        `!${baseDir}/${paths.script.dest}/flatpickr.js`,
        `${baseDir}/**/*.html`
    ])
);
gulp.task('clean:build', () => del(paths.dir.prod));
gulp.task('clean:live', () => del(`live/${version}`));
