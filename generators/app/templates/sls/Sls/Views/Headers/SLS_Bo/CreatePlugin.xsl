<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderCreatePlugin">
		<script type="text/javascript">
			window.addEvent('domready', function(){
				$$('a.delete_plugin').each(function(element, i){
					element.addEvent('click', function(e){
						if (!confirm("If you delete this plugin, all files and configurations will be deleted and if your plugin has not been already submitted to SillySmart community, you shoudn't recover your datas"))
							e.stop();
					});
				});
			});
		</script>
	</xsl:template>
</xsl:stylesheet>