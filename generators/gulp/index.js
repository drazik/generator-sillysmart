'use strict';

var yeoman = require('yeoman-generator');

module.exports = yeoman.generators.Base.extend({
    constructor: function() {
        yeoman.generators.Base.apply(this, arguments);

        this.argument('packageName', { type: String, optional: true, defaults: 'your-package-name' });

        this.packageName = this.packageName.toLowerCase().split(' ').join('-');
        this.dependencies = ['require-dir', 'gulp', 'gulp-plumber', 'gulp-util', 'minimist'];
    },
    copyFiles: function() {
        this.copy('gulpfile.js', 'gulpfile.js');
        this.template('package.json', 'package.json', { packageName: this.packageName });
    },
    installDependencies: function() {
        this.npmInstall(this.dependencies, { saveDev: true });
    }
});
