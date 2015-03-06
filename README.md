# generator-sillysmart

This is a [Yeoman](http://www.yeoman.io/) generator for the PHP framework [SillySmart](http://www.sillysmart.org/) developped by the french web production agency [Wandi](http://www.wandi.fr/).

## Installation

### Prerequisite

If you don't have NodeJS and NPM installed, the first step is to install it. Go to the [official node website](http://nodejs.org/) and choose the right version for your system.

### generator-sillysmart installation

First, you need to [install Yeoman](http://yeoman.io/learning/index.html) :

    npm install -g yo

Then, you need to install generator-sillysmart :

    npm install -g generator-sillysmart

## How to use

When everything is installed, you can type the following command in a fresh clean project directory :

    yo sillysmart

The generator will ask you some questions. Just answer these questions and your SillySmart project is ready !

## Questions

### Project name

This will be used as your `package.json` name parameter. You can type whatever your want, it will be transformed into a well-formatted NPM package name.

### Gulp

At Wandi, we use [Gulp](http://gulpjs.com/) as our main build system. So this generator was firstly made with this in mind. If you want to use Gulp, then answer Yes. If you want to use another build system like [Grunt](http://gruntjs.com/), [Brunch](http://brunch.io/), [Broccoli](http://broccolijs.com/) or whatever, just answer No and install your prefered system by yourself.

In next releases, we want to add some common build systems.

### Gulp tasks

You will then be asked which tasks you would like to use. Select the tasks you need by navigating with arrow keys, pressing `space` key, then hit `enter`.

#### Gulp task : styles

This task uses [SASS](http://sass-lang.com/) to preprocess your stylesheets, [autoprefixer](https://github.com/postcss/autoprefixer) to automatically add vendor prefixes. Sourcemaps are added to the resulting CSS file in development mode (see Configuration section of this documentation to learn how to switch between production and development modes).

By default, this task takes `./Public/Style/Scss/Global.scss` as source and compile it in `./Public/Style/Css/Global.css`.

#### Gulp task : javascripts

This task uses [Browserify](http://browserify.org/). It allows you to write your app with clean CommonJS (node style) modules that can `require` other modules, etc...

The result is uglified with [UglifyJS](https://github.com/mishoo/UglifyJS).

By default, this task takes `./Public/Scripts/Javascript/Dyn/Uncompressed/app.js` as source and compile it in `Public/Scripts/Javascript/Dyn/app.js`.

#### Gulp task : images

This task uses [Imagemin](https://github.com/imagemin/imagemin) to compress your images withouth losing some of their quality.

By default, this task takes all images in `./Public/Style/Img.uncompressed` as source and put compressed ones in `./Public/Style/Img`.

### Gulp "meta" tasks

If you have selected at least one gulp task, the generator automatically add two "meta" tasks. What we call "meta" tasks is tasks that call other tasks.

#### compile

The `compile` meta task just call all the tasks you have selected. If you have selected `styles` and `images`, if you type `gulp compile`, then it will execute `styles` and `images` tasks.

#### watch

This task launch watchers on some files, depending on the tasks you have selected. When a change is detected, the right task is executed.

This task watches the following files :

* `./Public/Style/Scss/**/*.scss` for `styles` task
* `./Public/Scripts/Javascript/Dyn/Uncompressed/**/*` for `javascripts` task
* `Public/Style/Img.uncompressed/**/*` for `images` task

## Contributing

TODO