<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="GoogleSettings">

		<h1>
			Google Analytics Settings			
		</h1>
		
		<xsl:if test="count(//View/errors/error) &gt; 0">
			<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
				<xsl:for-each select="//View/errors/error">
					<xsl:value-of select="." /><br />
				</xsl:for-each>
			</div>
		</xsl:if>
		<xsl:if test="//View/success != ''">
			<div style="color:green;">
				<xsl:value-of select="//View/success" />
			</div>
		</xsl:if>
		
		<form action="" enctype="multipart/form-data" method="post">
			<input type="hidden" name="reload" value="true" />
			<fieldset>
				<legend>App Tracking</legend>
				<table border="0">
					<tr>
						<td><label for="ua">UA Tracking Code</label></td>
						<td><input type="text" name="ga[ua]" id="ua" value="{//View/google/ua}" /></td>
						<td>see: the _gaq.push(['_setAccount', '<strong>UA-XXXXXXXX-X</strong>']); in your JS code or in <a href="http://www.google.com/analytics" target="_blank">Google Analytics</a>&#160;<a href="http://www.sillysmart.org/Public/Files/bo_help/ga_tracking.jpg" rel="shadowbox"><img src="{$sls_url_img_core}Icons/information.png" alt="Screenshot" title="Screenshot" style="border:0;" /></a></td>
					</tr>
				</table>
			</fieldset>	
			<fieldset>
				<legend>Back-Office Integration</legend>
				<table border="0">
					<tr>
						<td><label for="apiKey">API - Key</label></td>
						<td><input type="text" name="ga[apiKey]" id="apiKey" value="{//View/google/apiKey}" /></td>
						<td>see: <a href="https://console.developers.google.com" target="_blank">Google Developer Console</a> (<strong>Public API access</strong> field)&#160;<a href="http://www.sillysmart.org/Public/Files/bo_help/ga_api.jpg" rel="shadowbox"><img src="{$sls_url_img_core}Icons/information.png" alt="Screenshot" title="Screenshot" style="border:0;" /></a></td>
					</tr>
					<tr>
						<td><label for="clientId">API - Client Id</label></td>
						<td><input type="text" name="ga[clientId]" id="clientId" value="{//View/google/clientId}" /></td>
						<td>see: <a href="https://console.developers.google.com" target="_blank">Google Developer Console</a> (<strong>OAuth Client Id</strong> field)&#160;<a href="http://www.sillysmart.org/Public/Files/bo_help/ga_api.jpg" rel="shadowbox"><img src="{$sls_url_img_core}Icons/information.png" alt="Screenshot" title="Screenshot" style="border:0;" /></a></td>
					</tr>
					<tr>
						<td><label for="accountId">API - Account Id</label></td>
						<td><input type="text" name="ga[accountId]" id="accountId" value="{//View/google/accountId}" /></td>
						<td>see: the id after the <strong>p</strong> at the end of your google analytics URL&#160;<a href="http://www.sillysmart.org/Public/Files/bo_help/ga_account.jpg" rel="shadowbox"><img src="{$sls_url_img_core}Icons/information.png" alt="Screenshot" title="Screenshot" style="border:0;" /></a></td>
					</tr>
				</table>
			</fieldset>	
			<input type="submit" value="Confirm Changes" />
		</form>
				
	</xsl:template>
</xsl:stylesheet>