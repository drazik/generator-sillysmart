<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="ProjectSettings">
	
		<h1>
			<xsl:choose>
				<xsl:when test="//View/is_prod = 'true'">
					SillySmart Deployment <xsl:if test="//View/is_batch = 'true'">- <em style="font-size:0.8em;font-weight:normal;">Step <strong style="color:#D97878;font-weight:normal;">4</strong>/4</em></xsl:if>
				</xsl:when>
				<xsl:otherwise>
					Edit Your Settings
				</xsl:otherwise>
			</xsl:choose>						
		</h1>
		<h2>Project Settings</h2>
		<fieldset>
			<legend>Project Settings</legend>
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<form action="" name="" enctype="multipart/form-data" method="post">
				
											
				<label for="project">You can edit your XML to add your own parameters. To access to your configurations, you can call them by $this->_generic->getProjectSetting('settingName') or by $this->_generic->getProjectXML()</label><br /><br />
				<textarea name="project" rows="30" cols="50" id="project">
					<xsl:value-of select="php:functionString('str_replace', '&amp;#139;', '&lt;', php:functionString('str_replace', '&amp;#155;', '&gt;', //View/current_values/project))" />
				</textarea>
				<br /><br />
				
				<input type="hidden" name="reload" value="true" />
				<input type="submit" value="Confirm Changes" />
			</form>
		</fieldset>
				
	</xsl:template>
</xsl:stylesheet>