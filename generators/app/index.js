'use strict';

var yeoman = require('yeoman-generator');
var yosay = require('yosay');

module.exports = yeoman.generators.Base.extend({
    constructor: function() {
        yeoman.generators.Base.apply(this, arguments);

        this.log(yosay('So you want to install SillySmart... Here we go !'));
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
    installSillySmart: function() {
        var done = this.async();

        this.invoke('sillysmart:sls')
            .on('end', function() {
                done();
            });
    },
    installGulp: function() {
        if (this.answers.gulp) {
            var done = this.async();

            this.invoke('sillysmart:gulp', {args: [this.answers.projectName]})
                .on('end', function() {
                    done();
                });
        }
    },
    installTasks: function() {
        if (this.answers.gulp && this.answers.tasks.length > 0) {
            for (var i in this.answers.tasks) {
                this._installTask(this.answers.tasks[i]);
            }
        }
    },
    _installTask: function(taskName) {
        var done = this.async();

        this.invoke('sillysmart:task', {args: [taskName]})
            .on('end', function() {
                done();
            });
    }
});