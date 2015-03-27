# generator-sillysmart

This is a [Yeoman](http://www.yeoman.io/) generator for the PHP framework [SillySmart](http://www.sillysmart.org/) developped by the french web production agency [Wandi](http://www.wandi.fr/). SillySmart is an open source MVC framework. Feel free to give it a try !

## Installation

### Prerequisites

If you don't have NodeJS and NPM installed, the first step is to install it. Go to the [official node website](http://nodejs.org/) and choose the right version for your system.

### generator-sillysmart installation

First, you need to [install Yeoman](http://yeoman.io/learning/index.html) :

    npm install -g yo

Then, you need to install generator-sillysmart :

    npm install -g generator-sillysmart

## How to use

### Main generator

When everything is installed, you can type the following command in a fresh clean project directory :

    yo sillysmart

The generator will ask you some questions. Just answer these questions and your SillySmart project is ready !

### Gulp subgenerator

At Wandi, we use [Gulp](http://gulpjs.com/) as our main build system. So this generator was firstly made with this in mind. The main generator asks if you want to use Gulp. If it's the case, then answer Yes. The main generator will call this subgenerator, and the package name you have previously entered will be passed to it.

If you want to use another build system like [Grunt](http://gruntjs.com/), [Brunch](http://brunch.io/), [Broccoli](http://broccolijs.com/) or whatever, just answer No and install your favourite system by yourself.

If you have previously installed SillySmart and just want to install Gulp and some basic dependencies for further tasks, you can call this subgenerator by typing the following line in your terminal :

    yo sillysmart:gulp [package-name]

This subgenerator will create a `package.json`, a generic `gulpfile.js` and install `gulp`, `require-dir`, `gulp-plumber`, `gulp-util`, and `minimist`.

The package name is optional. If you don't provide it, it will be equals to "your-package-name". It is just used in `package.json`.

### Task subgenerator

Some task that are used in every project are shipped with the generator. You can install them by calling the task subgenerator :

    yo sillysmart:task task-name

`task-name` must be one of the three tasks that are shipped :

* `images` : lossless image compression using `imagemin`
* `javascripts` : javascript bundling and minifcation using `browserify` and `uglifyJS`
* `styles` : CSS preprocessing using `sass`

You can check `generator/task/templates/tasks` to see what is the content of each task. The configuration of each task is stored in `gulp/configs/{task-name}.js`. Default configurations fit the SillySmart's directories architecture.

Obviously, you can update the tasks installed using this subgenerator and their configuration, and create your own tasks (we recommend you to create a `compile` and a `watch` task, for example).

The main generator is calling this subgenerator for each task you have selected.

## Contributing

This generator is open source. You can improve it. Just fork the repository, create a branch for your new feature and make a pull request when everything is fine. You can also contribute by opening an issue if you find a bug or have an idea for new features.