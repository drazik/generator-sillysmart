'use strict';

var http = require('http');
var fs = require('fs');
var generators = require('yeoman-generator');
var tar = require('tar');
var rimraf = require('rimraf');

module.exports = generators.Base.extend({
    _getArchive: function(url, done) {
        http.get(url, function(response) {
            if (response.statusCode === 302) {
                var url = response.headers.location;

                this._getArchive(url, done);
            } else {
                var file = fs.createWriteStream('sls.tar');
                response.pipe(file);

                file.on('finish', function() {
                    file.close(function() {
                        this.log('Done.');
                        done();
                    }.bind(this));
                }.bind(this));
            }
        }.bind(this));
    },
    promptUser: function() {
        var done = this.async();

        var prompts = [{
            name: 'projectName',
            message: 'What is your project\'s name ?'
        },{
            name: 'gulp',
            type: 'confirm',
            message: 'Would you like to use gulp ?'
        }, {
            when: function(answers) {
                return answers.gulp;
            },
            name: 'tasks',
            type: 'checkbox',
            choices: [{
                name: 'Styles preprocessing (SASS)',
                value: 'styles'
            }, {
                name: 'Images compression',
                value: 'images'
            },{
                name:'JS (Browserify)',
                value: 'javascripts'
            }],
            message: 'Which tasks would you like to use ?'
        }];

        this.prompt(prompts, function(answers) {
            this.answers = answers;

            done();
        }.bind(this));
    },
    createDependenciesArray: function() {
        if (this.answers.tasks) {
            var sharedDependencies = ['require-dir', 'gulp', 'gulp-plumber', 'gulp-util'];

            var otherDependencies = {
                styles: ['gulp-sass', 'gulp-autoprefixer', 'gulp-rename', 'gulp-sourcemaps'],
                javascripts: ['gulp-browserify', 'gulp-uglifyjs'],
                images: ['gulp-imagemin', 'gulp-newer']
            };

            this.dependencies = sharedDependencies;

            for (var i = 0, length = this.answers.tasks.length; i < length; ++i) {
                this.dependencies = this.dependencies.concat(otherDependencies[this.answers.tasks[i]]);
            }
        }
    },
    downloadSillySmartArchive: function() {
        var done = this.async();

        this.log('Downloading last SillySmart release...');
        this._getArchive('http://www.sillysmart.org/Home/DownloadLastRelease', done);
    },
    extractSillySmartFiles: function() {
        var slsTar = fs.createReadStream('./sls.tar');

        this.log('Extracting SillySmart files...');
        slsTar.pipe(tar.Extract({ path: '.' }));
        this.log('Done.');
    },
    deleteSillySmartArchive: function() {
        var done = this.async();

        this.log('Deleting SillySmart archive...');
        rimraf('./sls.tar', function() {
            this.log('Done.');
            done();
        }.bind(this));
    },
    addTasksDirectories: function() {
        // Directories for each selected task
        if (this.answers.tasks) {
            if (this.answers.tasks.indexOf('styles') !== -1) {
                this.mkdir('./Public/Style/Scss');
            }

            if (this.answers.tasks.indexOf('javascripts') !== -1) {
                this.mkdir('./Public/Scripts/Javascript/Dyn/Uncompressed');
            }

            if (this.answers.tasks.indexOf('images') !== -1) {
                this.mkdir('./Public/Style/Img.uncompressed');
            }
        }
    },
    generatePackageJson: function() {
        if (this.answers.gulp) {
            this.template('gulp/package.json', './package.json', { packageName: this.answers.projectName });
        }
    },
    generateGulpFile: function() {
        if (this.answers.gulp) {
            this.template('gulp/gulpfile.js', './gulpfile.js');
        }
    },
    copyTasksFiles: function() {
        if (this.answers.tasks) {
            this.mkdir('gulp');
            this.mkdir('gulp/tasks');

            for (var i = 0, length = this.answers.tasks.length; i < length; ++i) {
                this.copy('gulp/tasks/' + this.answers.tasks[i] + '.js','gulp/tasks/' + this.answers.tasks[i] + '.js');
            }

            var tasksArrayString = '[\'' + this.answers.tasks.join('\',\'') + '\']';
            this.template('gulp/tasks/compile.js', './gulp/tasks/compile.js', { tasksArray: tasksArrayString });

            var watchTasks = {
                styles: {
                    path: 'Public/Style/Scss/**/*.scss',
                    name: 'styles'
                },
                javascripts: {
                    path: 'Public/Scripts/Javascript/Dyn/Uncompressed/**/*',
                    name: 'javascripts'
                },
                images: {
                    path: 'Public/Style/Img.uncompressed/**/*',
                    name: 'images'
                }
            };

            var realWatchTasks = [];
            for (i = 0, length = this.answers.tasks.length; i < length; ++i) {
                realWatchTasks.push(watchTasks[this.answers.tasks[i]]);
            }

            this.template('gulp/tasks/watch.js', './gulp/tasks/watch.js', { tasks: realWatchTasks });
        }
    },
    installNpmDependencies: function() {
        if (this.dependencies) {
            this.npmInstall(this.dependencies, { saveDev: true });
        }
    }
});