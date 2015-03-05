<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="MailTemplatesAdd">
	
		<h1>						
			Email templates				
		</h1>
		<h2>Add a new one</h2>
		
		<form method="post" action="">
			<input type="hidden" name="reload" value="true" />
			
			<xsl:if test="//View/error != ''">
				<div class="error" style="font-weight:bold;color:red;">
					<xsl:value-of select="//View/error" />
				</div>
			</xsl:if>
			
			<fieldset>
				<table border="0">
					<tr>
						<td><label for="tpl_id">Template Name</label></td>
						<td><input type="text" name="tpl_id" id="tpl_id" value="{//View/template/id}" /></td>
					</tr>
					<tr>
						<td><label for="tpl_header">Template Header</label></td>
						<td>
							<textarea name="tpl_header" id="tpl_header" cols="80" rows="20">
								<xsl:value-of select="//View/template/header" disable-output-escaping="yes" />
							</textarea>
						</td>
					</tr>
					<tr>
						<td><label for="tpl_footer">Template Footer</label></td>
						<td>
							<textarea name="tpl_footer" id="tpl_footer" cols="80" rows="20">
								<xsl:value-of select="//View/template/footer" disable-output-escaping="yes" />
							</textarea>
						</td>
					</tr>
				</table>
			</fieldset>
			<input type="submit" value="Add template" />
		</form>
				
	</xsl:template>
</xsl:stylesheet>