<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddGeneric">
	
		<h1>Create a new generic</h1>
		<xsl:if test="count(//View/errors/error) &gt; 0">
			<span style="font-weight:bold;color:red;">
				<xsl:value-of select="//View/errors/error" />
			</span>
		</xsl:if>
		<form action="" method="post">
			<input type="hidden" name="reload" value="true" />
			<table border="0">
				<tr>
					<td><label for="generic_name">Name :</label></td>
					<td><input type="text" name="generic_name" id="generic_name" /></td>
				</tr>
			</table>			
			<input type="submit" value="Add" />
		</form>		
				
	</xsl:template>
</xsl:stylesheet>