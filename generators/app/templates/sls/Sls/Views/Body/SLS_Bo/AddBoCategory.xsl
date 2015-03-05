<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddBoCategory">
		<div id="SLS_EditBo">
			<form method="post" action="">
				<h1>Add a new bo category</h1>

				<fieldset>
					<xsl:if test="//View/error != ''">
						<div style="padding-bottom:10px;color:red;">
							<xsl:value-of select="//View/error" />
						</div>
					</xsl:if>
					<legend>Category</legend>
					<label for="category">Name</label>
					<input type="text" name="category" id="category" />
				</fieldset>
				
				<input type="hidden" name="reload" value="true" />
				<input type="submit" value="Create" />
			</form>
		</div>

	</xsl:template>
</xsl:stylesheet>