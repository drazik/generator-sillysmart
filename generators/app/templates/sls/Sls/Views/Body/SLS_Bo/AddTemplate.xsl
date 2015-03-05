<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddTemplate">
	
		<h1>Create a new template</h1>
		<xsl:if test="count(//View/errors/error) &gt; 0">
			<span style="font-weight:bold;color:red;">
				<xsl:value-of select="//View/errors/error" />
			</span>
		</xsl:if>
		<form action="" method="post">
			<input type="hidden" name="reload" value="true" />
			<table border="0">
				<tr>
					<td><label for="tpl_name">Name :</label></td>
					<td><input type="text" name="tpl_name" id="tpl_name" /></td>
				</tr>
				<tr>
					<td><label for="doctype">Doctype :</label></td>
					<td>
						<select name="doctype" id="doctype">
							<option value="xhtml_1.0_transitionnal">
								<xsl:if test="//View/doctype = 'xhtml_1.0_transitionnal'">
									<xsl:attribute name="selected" select="'selected'" />
								</xsl:if>
								xHTML 1.0 Transitionnal
							</option>
							<option value="xhtml_1.0_strict">
								<xsl:if test="//View/doctype = 'xhtml_1.0_strict'">
									<xsl:attribute name="selected" select="'selected'" />
								</xsl:if>
								xHTML 1.0 Strict
							</option>
							<option value="xhtml_1.1_strict">
								<xsl:if test="//View/doctype = 'xhtml_1.1_strict'">
									<xsl:attribute name="selected" select="'selected'" />
								</xsl:if>
								xHTML 1.1 Strict
							</option>
							<option value="html_5">
								<xsl:if test="//View/doctype = 'html_5'">
									<xsl:attribute name="selected" select="'selected'" />
								</xsl:if>
								HTML 5
							</option>
							<option value="html_4.01_transitionnal">
								<xsl:if test="//View/doctype = 'html_4.01_transitionnal'">
									<xsl:attribute name="selected" select="'selected'" />
								</xsl:if>
								HTML 4.01 Transitionnal
							</option>
							<option value="html_4.01_strict">
								<xsl:if test="//View/doctype = 'html_4.01_strict'">
									<xsl:attribute name="selected" select="'selected'" />
								</xsl:if>
								HTML 4.01 Strict
							</option>
						</select>
					</td>
				</tr>
			</table>
			<input type="submit" value="Add" />
		</form>		
				
	</xsl:template>
</xsl:stylesheet>