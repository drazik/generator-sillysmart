<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderPlugins">
		<script type="text/javascript">
			window.addEvent('domready', function(){
				$$('a.update_plugins').each(function(element, i){
					element.addEvent('click', function(e){
						if (!confirm("Update a plugin will erase all your existing configuration for this plugin"))
							e.stop();
					});
				});
				$$('a.delete_plugins').each(function(element, i){
					element.addEvent('click', function(e){
						if (!confirm("Delete a plugin will erase all plugins's files and existing configuration"))
							e.stop();
					});
				});
			});
		</script>
	</xsl:template>
</xsl:stylesheet>