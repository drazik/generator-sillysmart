var gulp = require('gulp');

gulp.task('watch', ['compile'], function() {
<% for (var i = 0, length = tasks.length; i < length; ++i) { %>
    gulp.watch('<%= tasks[i].srcPath %>', ['<%= tasks[i].name %>']);
<% } %>
});