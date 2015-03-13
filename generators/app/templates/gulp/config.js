'use strict';

var config = {};

<% for (var i = 0, length = tasks.length; i < length; ++i) { %>
config = require('./configs/<%= tasks[i].name %>')(config);
<% } %>

module.exports = config;