<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderEditForeignKey">
		<script type="text/javascript">
			var arrayTables = new Array();
			<xsl:for-each select="//View/model/tables/table">
				arrayTables.push('<xsl:value-of select="concat(db,'_',name)" />');
			</xsl:for-each>
			<xsl:text disable-output-escaping="yes"><![CDATA[							
			function changeSelectBox()
			{
				hideSelect();
				document.getElementById(document.getElementById('allTables').value + '_fkLabel').style.display = 'inline';
			}
			function checkSpecifiedCheckbox()
			{
				var display = document.getElementById('fkLabel_specified_checkbox').checked;
				if (display)
				{
					hideSelect();
					document.getElementById('fkLabel_specified').style.display = 'inline';
				}
				else
				{
					hideSelect();
					document.getElementById(document.getElementById('allTables').value + '_fkLabel').style.display = 'inline';
					document.getElementById('fkLabel_specified').style.display = 'none';
					document.getElementById('fkLabel_specified').value = '';
				}
			}	
			function hideSelect()
			{
				for(var i=0; i<arrayTables.length; i++)								
					document.getElementById(arrayTables[i]+'_fkLabel').style.display = 'none';
			}				
			]]></xsl:text>
		</script>
	</xsl:template>
</xsl:stylesheet>