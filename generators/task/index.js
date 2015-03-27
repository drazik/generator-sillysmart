'use strict';

var yeoman = require('yeoman-generator');
var fs = require('fs');

module.exports = yeoman.generators.NamedBase.extend({
    constructor: function() {
        yeoman.generators.NamedBase.apply(this, arguments);

        this.fileName = this.name + '.js';
    },
    copyFiles: function() {
        var srcConfigPath = this.templatePath('configs/' + this.fileName);
        var srcTaskPath = this.templatePath('tasks/' + this.fileName);
        var destConfigPath = this.destinationPath('gulp/configs/' + this.fileName);
        var destTaskPath = this.destinationPath('gulp/tasks/' + this.fileName);

        try {
            fs.lstatSync(srcConfigPath);
            fs.lstatSync(srcTaskPath);

            // if the previous lines didn't trigger an exception, both files exist, we can copy them
            this.copy(srcConfigPath, destConfigPath);
            this.copy(srcTaskPath, destTaskPath);
        } catch (e) {
            this.log('This task doesn\'t exist. Exiting');
        }
    }
});
