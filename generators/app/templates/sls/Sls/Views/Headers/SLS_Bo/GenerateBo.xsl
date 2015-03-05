<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderGenerateBo">
		<script type="text/javascript">
			<xsl:text disable-output-escaping="yes"><![CDATA[
			function checkAll(db,nb)
			{
				for(var i=1 ; i<=nb ; i++)				
					if (!document.getElementById(db+'_'+i).disabled)					
						document.getElementById(db+'_'+i).checked = (document.getElementById(db+'_all').checked) ? 'cheked' : false;				
			}
			]]></xsl:text>
		</script>
	</xsl:template>
</xsl:stylesheet>