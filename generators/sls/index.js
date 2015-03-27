'use strict';

var yeoman = require('yeoman-generator');
var fs = require('fs');
var yosay = require('yosay');
var http = require('http');
var tar = require('tar');
var rimraf = require('rimraf');

module.exports = yeoman.generators.Base.extend({
    constructor: function() {
        yeoman.generators.Base.apply(this, arguments);

        yosay('So you want to install SillySmart... Here we go !');
    },
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
    }
});
