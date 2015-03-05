<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderBoUserAdd">
		<script type="text/javascript">
			window.addEvent('domready', function(){
				if ($$('.user-rights.customs .permission-square').length){
					$$('.user-rights.customs .permission-square').each(function(permissionSquare){
						new PermissionSquare(permissionSquare);
					});
				}
			});
		</script>
	</xsl:template>
</xsl:stylesheet>