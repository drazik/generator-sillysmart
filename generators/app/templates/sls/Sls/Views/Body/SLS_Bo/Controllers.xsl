<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Controllers">
	
		<h1>Manage your Controllers &amp; Actions</h1>
		
		<h2>Existing controllers</h2>
		
		<h3>Statics Controllers</h3>
		<a href="{//Statics/Sls/Configs/action/links/link[name='ADDSTATICCONTROLLER']/href}" title="Add a new Static Controller"><img src="{concat($sls_url_img_core_icons, 'script_add.png')}" alt="Add a new Static Action" title="Add a new Static Action" style="margin-right:10px;" />Add a new Static Controller</a>
		<xsl:if test="count(//View/statics/static) &gt; 0">
			<table>
				<tr height="10"></tr>
				<xsl:for-each select="//View/statics/static">
					<tr>
						<td>
							<xsl:choose>
								<xsl:when test="cache != 'false'">
									<img src="{concat($sls_url_img_core_icons, 'cache.png')}" style="position:relative;top:-1px;" alt="Cache {php:functionString('str_replace','|',', ',cache)} seconds" title="Cache {php:functionString('str_replace','|',', ',cache)} seconds" />								
								</xsl:when>
								<xsl:otherwise>
									<span style="display:block;width:16px;height:16px;"></span>
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td>
							<xsl:value-of select="name" />								
						</td>							
						<td>
							<a href="{concat(//Statics/Sls/Configs/action/links/link[name='EDITSTATICCONTROLLER']/href, '/Controller/', name, '.sls')}" title="View Details - Modify">
								<img src="{concat($sls_url_img_core_icons, 'magnifier.png')}" alt="View Details - Modify" title="View Details - Modify" />
							</a>
						</td>
						<td>
							<a href="#" onclick="confirmDelete('{concat(//Statics/Sls/Configs/action/links/link[name='DELSTATICCONTROLLER']/href, '/Controller/', name, '.sls')}');return false;" title="Delete Controller">
								<img src="{concat($sls_url_img_core_icons, 'cross.png')}" alt="Delete Static Controller" title="Delete Static Controller" />
							</a>
						</td>
						<td>
							<xsl:choose>
								<xsl:when test="cache != 'false'">
									<a href="#" onclick="confirmFlush('{concat(//Statics/Sls/Configs/action/links/link[name='FLUSHCACHE']/href, '/From/Static/Item/', id, '.sls')}');return false;" title="Flush cache">
										<img src="{concat($sls_url_img_core_buttons, 'cache_flush.png')}" alt="Flush cache" title="Flush cache" />
									</a>							
								</xsl:when>
								<xsl:otherwise>
									<span style="display:block;width:16px;height:16px;"></span>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
		<hr style="margin:20px 0 0 0;width:240px" />
		<h3>Components Controllers</h3>
		<a href="{//Statics/Sls/Configs/action/links/link[name='ADDCOMPONENTCONTROLLER']/href}" title="Add a new Component Controller"><img src="{concat($sls_url_img_core_icons, 'script_add.png')}" alt="Add a new Component Action" title="Add a new Component Action" style="margin-right:10px;" />Add a new Component Controller</a>
		<xsl:if test="count(//View/components/component) &gt; 0">
			<table>
				<tr height="10"></tr>
				<xsl:for-each select="//View/components/component">
					<tr>
						<td>
							<xsl:choose>
								<xsl:when test="cache != 'false'">
									<img src="{concat($sls_url_img_core_icons, 'cache.png')}" style="position:relative;top:-1px;" alt="Cache {php:functionString('str_replace','|',', ',cache)} seconds" title="Cache {php:functionString('str_replace','|',', ',cache)} seconds" />								
								</xsl:when>
								<xsl:otherwise>
									<span style="display:block;width:16px;height:16px;"></span>
								</xsl:otherwise>
							</xsl:choose>
						</td>
						<td>
							<xsl:value-of select="name" />								
						</td>							
						<td>
							<a href="{concat(//Statics/Sls/Configs/action/links/link[name='EDITCOMPONENTCONTROLLER']/href, '/Controller/', name, '.sls')}" title="View Details - Modify">
								<img src="{concat($sls_url_img_core_icons, 'magnifier.png')}" alt="View Details - Modify" title="View Details - Modify" />
							</a>
						</td>
						<td>
							<a href="#" onclick="confirmDelete('{concat(//Statics/Sls/Configs/action/links/link[name='DELCOMPONENTCONTROLLER']/href, '/Controller/', name, '.sls')}');return false;" title="Delete Controller">
								<img src="{concat($sls_url_img_core_icons, 'cross.png')}" alt="Delete Component Controller" title="Delete Component Controller" />
							</a>
						</td>
						<td>
							<xsl:choose>
								<xsl:when test="cache != 'false'">
									<a href="#" onclick="confirmFlush('{concat(//Statics/Sls/Configs/action/links/link[name='FLUSHCACHE']/href, '/From/Component/Item/', id, '.sls')}');return false;" title="Flush cache">
										<img src="{concat($sls_url_img_core_buttons, 'cache_flush.png')}" alt="Flush cache" title="Flush cache" />
									</a>							
								</xsl:when>
								<xsl:otherwise>
									<span style="display:block;width:16px;height:16px;"></span>
								</xsl:otherwise>
							</xsl:choose>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
		<hr style="margin:20px 0 0 0;width:240px" />
		<h3>Project Controllers &amp; Actions</h3>
		<a href="{//Statics/Sls/Configs/action/links/link[name='ADDCONTROLLER']/href}" title="Add a new Controller"><img src="{concat($sls_url_img_core_icons, 'script_add.png')}" alt="Add a new Controller" title="Add a new Controller" style="margin-right:10px;" />Add a new Controller</a>			
		<xsl:if test="count(//View/controllers/controller) &gt; 0">
			<table>
				<tr height="10"></tr>
				<tr>
					<td colspan="5"></td>
				</tr>
				<xsl:for-each select="//View/controllers/controller">
					<xsl:sort select="name" order="ascending" />
					<tr>							
						<td colspan="2">
							<span style="font-weight:bold;"><xsl:value-of select="name" /></span>
							&#160;<span style="font-size:0.7em;color:#000;"><i>&lt;<xsl:value-of select="tpl" />&gt;</i></span>
						</td>
						<td>
							<a href="{concat(//Statics/Sls/Configs/action/links/link[name='EDITCONTROLLER']/href, '/Controller/', name, '.sls')}" title="View Details - Modify">
								<img src="{concat($sls_url_img_core_icons, 'magnifier.png')}" alt="View Details - Modify" title="View Details - Modify" />
							</a>
						</td>
						<td>
							<xsl:if test="canBeDeleted = 'true'">
								<a href="#" onclick="confirmDelete('{concat(//Statics/Sls/Configs/action/links/link[name='DELCONTROLLER']/href, '/Controller/', name, '.sls')}');return false;" title="Delete Controller">
									<img src="{concat($sls_url_img_core_icons, 'cross.png')}" alt="Delete Controller (will delete all actions refered)" title="Delete Controller (will delete all actions refered)" />
								</a>
							</xsl:if>
						</td>
						<td>							
							<a href="#" onclick="confirmFlush('{concat(//Statics/Sls/Configs/action/links/link[name='FLUSHCACHE']/href, '/From/Controller/Item/', id, '.sls')}');return false;" title="Flush cache">
								<img src="{concat($sls_url_img_core_buttons, 'cache_flush.png')}" alt="Flush cache" title="Flush cache" />
							</a>								
						</td>
					</tr>
					<xsl:if test="count(scontrollers/scontroller) &gt; 0">
						<xsl:variable name="controllerName" select="name" />
						<xsl:for-each select="scontrollers/scontroller">
							<xsl:sort select="name" order="ascending" />
							<tr>
								<td>
									<xsl:choose>
										<xsl:when test="cache != 'false'">
											<img src="{concat($sls_url_img_core_icons, 'cache.png')}" style="position:relative;top:-1px;" alt="Cache {php:functionString('str_replace','|',', ',cache)} seconds" title="Cache {php:functionString('str_replace','|',', ',cache)} seconds" />								
										</xsl:when>
										<xsl:otherwise>
											<span style="display:block;width:16px;height:16px;"></span>
										</xsl:otherwise>
									</xsl:choose>
								</td>
								<td>
									<xsl:value-of select="name" />
									&#160;<span style="font-size:0.7em;color:#000;"><i>&lt;<xsl:value-of select="tpl" />&gt;</i></span>										
								</td>									
								<td>
									<a href="{concat(//Statics/Sls/Configs/action/links/link[name='EDITACTION']/href, '/Controller/', $controllerName, '/Action/', name, '.sls')}" title="View Details - Modify">
										<img src="{concat($sls_url_img_core_icons, 'magnifier.png')}" alt="View Details - Modify" title="View Details - Modify" />
									</a>
								</td>
								<td>
									<xsl:if test="canBeDeleted = 'true'">
										<a href="#" onclick="confirmDelete('{concat(//Statics/Sls/Configs/action/links/link[name='DELACTION']/href, '/Controller/', $controllerName, '/Action/', name, '.sls')}');return false;" title="Delete Action">
											<img src="{concat($sls_url_img_core_icons, 'cross.png')}" alt="Delete Action" title="Delete Action" />
										</a>
									</xsl:if>
								</td>
								<td>
							<xsl:choose>
								<xsl:when test="cache != 'false'">
									<a href="#" onclick="confirmFlush('{concat(//Statics/Sls/Configs/action/links/link[name='FLUSHCACHE']/href, '/From/Action/Item/', id, '.sls')}');return false;" title="Flush cache">
										<img src="{concat($sls_url_img_core_buttons, 'cache_flush.png')}" alt="Flush cache" title="Flush cache" />
									</a>							
								</xsl:when>
								<xsl:otherwise>
									<span style="display:block;width:16px;height:16px;"></span>
								</xsl:otherwise>
							</xsl:choose>
						</td>
							</tr>
						</xsl:for-each>
					</xsl:if>
					<tr>
						<td colspan="5">
							<a href="{concat(//Statics/Sls/Configs/action/links/link[name='ADDACTION']/href, '/Controller/', name, '.sls')}" title=""><img src="{concat($sls_url_img_core_icons, 'script_add.png')}" style="margin-right:10px;" title="" alt="" />
								<xsl:value-of select="concat('Add a new Action in ', name)" />
							</a>
						</td>
					</tr>
					<tr height="20"></tr>
				</xsl:for-each>
			</table>
		</xsl:if>
		
	</xsl:template>
</xsl:stylesheet>