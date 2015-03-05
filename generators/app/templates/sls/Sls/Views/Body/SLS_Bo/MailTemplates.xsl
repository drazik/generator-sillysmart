<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="MailTemplates">
	
		<h1>						
			Email templates				
		</h1>
		<h2>Edit properties</h2>
		
		<form method="post" action="">
			<input type="hidden" name="reload" value="true" />
			<xsl:for-each select="//View/templates/template">
				<fieldset style="float:left;margin-right:5px;">
					<legend><xsl:value-of select="id" />&#160;<a href="#" onclick="confirmDelete('{url_delete}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete mail template" alt="Delete mail template" style="border:0" align="absmiddle" /></a></legend>
					<table border="0">
						<tr>
							<td><label for="template_{id}_header">Template Header</label></td>
							<td>
								<textarea name="template_{id}_header" id="template_{id}_header" cols="80" rows="20">
									<xsl:value-of select="header" disable-output-escaping="yes" />
								</textarea>
							</td>
						</tr>
						<tr>
							<td><label for="template_{id}_footer">Template Header</label></td>
							<td>
								<textarea name="template_{id}_footer" id="template_{id}_footer" cols="80" rows="20">
									<xsl:value-of select="footer" disable-output-escaping="yes" />
								</textarea>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<a href="{url_preview}" title="Email Template '{id}'" rel="shadowbox">
									Preview
								</a>
							</td>
						</tr>
					</table>
				</fieldset>
			</xsl:for-each>
			<div class="row" style="float:none;clear:both;width:100%;">
				<input type="submit" value="Update" />
				or <a href="{//View/url_template_add}" title="Add a new email template">Add a new email template</a>
			</div>
		</form>		
				
	</xsl:template>
</xsl:stylesheet>