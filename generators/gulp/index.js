'use strict';

var yeoman = require('yeoman-generator');

module.exports = yeoman.generators.Base.extend({
    initialize: function () {
        this.dependencies = ['require-dir', 'gulp', 'gulp-plumber', 'gulp-util', 'minimist'];
    },
    copyFiles: function() {
        this.copy('gulpfile.js', 'gulpfile.js');
        this.template('package.json', 'package.json', { packageName: 'fake-package-name' });
    },
    installDependencies: function() {
        this.npmInstall(this.dependencies, { saveDev: true });
    }
});
