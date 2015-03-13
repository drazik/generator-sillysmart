'use strict';
var yeoman = require('yeoman-generator');
var fs = require('fs');

module.exports = yeoman.generators.NamedBase.extend({
    initializing: function () {
        this.fileName = this.name + '.js';
    },
    copyFiles: function() {
        this.copy('configs/' + this.fileName, 'gulp/configs/' + this.fileName);
        this.copy('tasks/' + this.fileName, 'gulp/tasks/' + this.fileName);
    }
});
