<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="HeaderAddRight">
		<script type="text/javascript">
			<xsl:text disable-output-escaping="yes"><![CDATA[
			function checkAll(prefix,nb)
			{
				for(var i=1 ; i<=nb ; i++)				
					if (!document.getElementById(prefix+'_'+i).disabled)					
						document.getElementById(prefix+'_'+i).checked = (document.getElementById(prefix).checked) ? 'cheked' : false;				
			}
			function changeColor()
			{
				$('color').setStyle('backgroundColor', $$('#color option:selected')[0].getStyle('backgroundColor'));
			}
			]]></xsl:text>
		</script>
	</xsl:template>
</xsl:stylesheet>