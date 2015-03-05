'use strict';

var generators = require('yeoman-generator');

module.exports = generators.Base.extend({
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
    copySlsFiles: function() {
        this.directory('sls', '.');

        // Because previous line don't copy empty directories !
        this.mkdir('./Langs');
        this.mkdir('./Langs/Actions');
        this.mkdir('./Langs/Generics');

        this.mkdir('./Mvc/Controllers');
        this.mkdir('./Mvc/Controllers/Actions');
        this.mkdir('./Mvc/Controllers/Components');
        this.mkdir('./Mvc/Controllers/Statics');

        this.mkdir('./Mvc/Models');
        this.mkdir('./Mvc/Models/Objects');
        this.mkdir('./Mvc/Models/Sql');

        this.mkdir('./Mvc/Views/Body');
        this.mkdir('./Mvc/Views/Generics');
        this.mkdir('./Mvc/Views/Headers');

        this.mkdir('./Plugins');

        this.mkdir('./Public/Cache');
        this.mkdir('./Public/Files');

        this.mkdir('./Public/Style');
        this.mkdir('./Public/Style/Css');
        this.mkdir('./Public/Style/Fonts');
        this.mkdir('./Public/Style/Img');

        this.mkdir('./Public/Scripts/Javascript');
        this.mkdir('./Public/Scripts/Javascript/Dyn');
        this.mkdir('./Public/Scripts/Javascript/Statics');

        this.mkdir('./Sls/Controllers/Components');

        this.mkdir('./Sls/Downloads');
        this.mkdir('./Sls/Downloads/Plugins');
        this.mkdir('./Sls/Downloads/Releases');

        this.mkdir('./Sls/Logs');

        // Directories for each selected task
        if (this.answers.tasks.indexOf('styles') !== -1) {
            this.mkdir('./Public/Style/Scss');
        }

        if (this.answers.tasks.indexOf('javascripts') !== -1) {
            this.mkdir('./Public/Scripts/Javascript/Dyn/Uncompressed');
        }

        if (this.answers.tasks.indexOf('images') !== -1) {
            this.mkdir('./Public/Style/Img.uncompressed');
        }
    },
    generatePackageJson: function() {
        this.template('gulp/package.json', './package.json', { packageName: this.answers.projectName });
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