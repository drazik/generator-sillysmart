'use strict';

var yeoman = require('yeoman-generator');
var fs = require('fs');
var yosay = require('yosay');
var taskDependencies = require('./taskdependencies');

module.exports = yeoman.generators.NamedBase.extend({
    constructor: function() {
        yeoman.generators.NamedBase.apply(this, arguments);

        this.fileName = this.name + '.js';
        this.srcConfigPath = this.templatePath('configs/' + this.fileName);
        this.srcTaskPath = this.templatePath('tasks/' + this.fileName);
        this.destConfigPath = this.destinationPath('gulp/configs/' + this.fileName);
        this.destTaskPath = this.destinationPath('gulp/tasks/' + this.fileName);
    },
    checkIfTaskExists: function() {
        try {
            fs.lstatSync(this.srcConfigPath);
            fs.lstatSync(this.srcTaskPath);
        } catch (e) {
            this.log('This task doesn\'t exist. Exiting.');
            process.exit(1);
        }
    },
    checkIfGulpIsInstalled: function() {
        try {
            fs.lstatSync(this.destinationPath('gulpfile.js'));
            this.isGulpInstalled = true;
        } catch (e) {
            this.isGulpInstalled = false;
        }
    },
    promptInstallGulp: function() {
        if (!this.isGulpInstalled) {
            var done = this.async();

            this.prompt({
                type: 'confirm',
                name: 'installGulp',
                message: 'It seems Gulp is not installed. Do you want to install it ?'
            }, function(answers) {
                this.installGulp = answers.installGulp;

                done();
            }.bind(this));
        } else {
            this.installGulp = false;
        }
    },
    installGulp: function() {
        if (this.installGulp) {
            var done = this.async();

            this.invoke('sillysmart:gulp')
                .on('end', function() {
                    done();
                });
        }
    },
    copyFiles: function() {
        this.copy(this.srcConfigPath, this.destConfigPath);
        this.copy(this.srcTaskPath, this.destTaskPath);
    },
    installDependencies: function() {
        if (taskDependencies[this.name]) {
            this.npmInstall(taskDependencies[this.name], { saveDev: true });
        }
    }
});
