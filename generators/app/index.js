'use strict';

var http = require('http');
var fs = require('fs');
var generators = require('yeoman-generator');
var yosay = require('yosay');
var tar = require('tar');
var rimraf = require('rimraf');

module.exports = generators.Base.extend({
    // Utils methods
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

    // Generator flow
    initialize: function() {
        this.tasks = {
            styles: {
                dependencies: ['gulp-sass', 'gulp-autoprefixer', 'gulp-rename', 'gulp-sourcemaps'],
                srcPath: 'Public/Style/Scss',
                name: 'styles'
            },
            javascripts: {
                dependencies: ['gulp-browserify', 'gulp-uglifyjs'],
                srcPath: 'Public/Scripts/Javascript/Dyn/Uncompressed',
                name: 'javascripts'
            },
            images: {
                dependencies: ['gulp-imagemin', 'gulp-newer'],
                srcPath: 'Public/Style/Img.uncompressed',
                name: 'images'
            }
        };
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

        this.log(yosay('Hello and welcome to this amazing SillySmart generator, my friend !'));

        this.prompt(prompts, function(answers) {
            this.answers = answers;

            done();
        }.bind(this));
    },
    createSelectedTasksArray: function() {
        if (this.answers.tasks) {
            this.selectedTasks = [];

            for (var i = 0, length = this.answers.tasks.length; i < length; ++i) {
                this.selectedTasks.push(this.tasks[this.answers.tasks[i]]);
            }
        }
    },
    createDependenciesArray: function() {
        if (this.answers.tasks) {
            var sharedDependencies = ['require-dir', 'gulp', 'gulp-plumber', 'gulp-util', 'minimist'];

            this.dependencies = sharedDependencies;

            for (var i = 0, length = this.selectedTasks.length; i < length; ++i) {
                this.dependencies = this.dependencies.concat(this.selectedTasks[i].dependencies);
            }
        }
    },
    // downloadSillySmartArchive: function() {
    //     var done = this.async();

    //     this.log('Downloading last SillySmart release...');
    //     this._getArchive('http://www.sillysmart.org/Home/DownloadLastRelease', done);
    // },
    // extractSillySmartFiles: function() {
    //     var slsTar = fs.createReadStream('./sls.tar');

    //     this.log('Extracting SillySmart files...');
    //     slsTar.pipe(tar.Extract({ path: '.' }));
    //     this.log('Done.');
    // },
    // deleteSillySmartArchive: function() {
    //     var done = this.async();

    //     this.log('Deleting SillySmart archive...');
    //     rimraf('./sls.tar', function() {
    //         this.log('Done.');
    //         done();
    //     }.bind(this));
    // },
    // addTasksDirectories: function() {
    //     if (this.answers.tasks) {
    //         for (var i = 0, length = this.selectedTasks.length; i < length; ++i) {
    //             this.mkdir(this.selectedTasks[i].srcPath);
    //         }
    //     }
    // },
    // generatePackageJson: function() {
    //     if (this.answers.gulp) {
    //         this.template('gulp/package.json', './package.json', { packageName: this.answers.projectName });
    //     }
    // },
    // generateGulpFile: function() {
    //     if (this.answers.gulp) {
    //         this.template('gulp/gulpfile.js', './gulpfile.js');
    //     }
    // },
    // copyTasksFiles: function() {
    //     if (this.answers.tasks) {
    //         var fileName;

    //         this.mkdir('gulp');
    //         this.mkdir('gulp/tasks');

    //         for (var i = 0, length = this.answers.tasks.length; i < length; ++i) {
    //             fileName = this.answers.tasks[i] + '.js';
    //             this.copy('gulp/tasks/' + fileName,'gulp/tasks/' + fileName);
    //             this.copy('gulp/configs/' + fileName, 'gulp/configs/' + fileName);
    //         }

    //         var tasksArrayString = '[\'' + this.answers.tasks.join('\',\'') + '\']';
    //         this.template('gulp/tasks/compile.js', './gulp/tasks/compile.js', { tasksArray: tasksArrayString });

    //         this.template('gulp/tasks/watch.js', './gulp/tasks/watch.js', { tasks: this.selectedTasks });

    //         this.template('gulp/config.js', 'gulp/config.js', { tasks: this.selectedTasks });
    //     }
    // },
    // installNpmDependencies: function() {
    //     if (this.dependencies) {
    //         this.npmInstall(this.dependencies, { saveDev: true });
    //     }
    // }
});