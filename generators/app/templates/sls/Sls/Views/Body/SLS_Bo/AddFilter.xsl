<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddFilter">	
	
		<h1>Add a specific filter on a column of `<xsl:value-of select="//View/model/table" />` table</h1>
		<xsl:if test="count(//View/error) &gt; 0">
			<div style="font-weight:bold;color:red;">
				<xsl:value-of select="//View/error" />
			</div>
		</xsl:if>
		<xsl:if test="count(//View/error) = 0">
			Please affect the specific filter to your column :<br />
			<form method="post" action="">
				<input type="hidden" name="reload" value="true" />
				<select name="column">
					<xsl:for-each select="//View/model/columns/column">
						<option value="{.}"><xsl:value-of select="." /></option>
					</xsl:for-each>
				</select>
				<select name="filter" id="filter" onchange="checkFilter()">
					<option value="alpha">Alpha</option>
					<option value="alnum">Alpha Numeric</option>
					<option value="numeric">Numeric</option>
					<option value="lower">Lowercase</option>
					<option value="lcfirst">Lowercase First</option>
					<option value="upper">Uppercase</option>
					<option value="ucfirst">Uppercase First</option>
					<option value="ucwords">Uppercase Words</option>
					<option value="trim">Trim</option>
					<option value="ltrim">Trim Left</option>
					<option value="rtrim">Trim Right</option>
					<option value="nospace">No Space</option>
					<option value="sanitize">Sanitize</option>
					<option value="striptags">Strip Tags</option>
					<option value="hash">Hash</option>
				</select>
				<select name="hash" id="hash" style="display:none">
					<option value="sha1">Sha1</option>
					<option value="md5">Md5</option>
					<option value="crc32">Crc32</option>
					<option value="crypt">Crypt</option>
				</select>														
				<input type="submit" value="Add" />
			</form>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>