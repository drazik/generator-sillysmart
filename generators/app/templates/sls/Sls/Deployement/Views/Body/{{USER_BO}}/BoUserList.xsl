<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml" xmlns:dyn="http://exslt.org/dynamic" extension-element-prefixes="dyn">
	<xsl:template name="BoUserList">

		<div id="sls-bo-fixed-header" class="no-sorting">
			<div class="sls-bo-fixed-header-content"></div>
		</div>
		<div class="sls-bo-listing-title fixed-in-header">
			<h1><span class="sls-bo-color-text">|||sls:lang:SLS_BO_USER_LIST_TITLE|||</span>&#160;|||sls:lang:SLS_BO_USER_LIST_SUBTITLE|||</h1>
		</div>

		<div class="sls-bo-listing main-core-content sls-bo-page-user-list" sls-listing-selection="false">
			<xsl:if test="(//Statics/Site/BoMenu/various/user_delete != '' and //Statics/Site/BoMenu/various/user_delete/@authorized = 'true') or (//Statics/Site/BoMenu/various/user_modify != '' and //Statics/Site/BoMenu/various/user_modify/@authorized = 'true')"><xsl:attribute name="sls-listing-selection">true</xsl:attribute></xsl:if>
			<div class="action-row sls-bo-color fixed-in-header">
				<ul class="actions">
					<xsl:if test="//Statics/Site/BoMenu/various/user_add != '' and //Statics/Site/BoMenu/various/user_add/@authorized = 'true'">
						<li class="add-user add-action">
							<a href="{//Statics/Site/BoMenu/various/user_add}" title="">
								<span class="picto"></span>
								<span class="label">|||sls:lang:SLS_BO_USER_LIST_ADD|||</span>
							</a>
						</li>
					</xsl:if>
					<xsl:if test="//Statics/Site/BoMenu/various/user_modify != '' and //Statics/Site/BoMenu/various/user_modify/@authorized = 'true'">
						<li class="edit-user edit-action">
							<a href="{//Statics/Site/BoMenu/various/user_modify}" title="">
								<span class="picto"></span>
								<span class="label">|||sls:lang:SLS_BO_USER_LIST_EDIT|||</span>
							</a>
						</li>
					</xsl:if>
					<xsl:if test="//Statics/Site/BoMenu/various/user_delete != '' and //Statics/Site/BoMenu/various/user_delete/@authorized = 'true'">
						<li class="delete-user delete-action">
							<a href="{//Statics/Site/BoMenu/various/user_delete}" title="">
								<span class="picto"></span>
								<span class="label">|||sls:lang:SLS_BO_USER_LIST_DELETE|||</span>
							</a>
						</li>
					</xsl:if>
				</ul>

				<a href="" title="" class="screen-layout-expand" sls-setting-name="list_view" sls-setting-value="expand" sls-setting-selected="false" sls-setting-selected-class="selected">
					<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='list_view'] = 'expand'">
						<xsl:attribute name="class">screen-layout-expand selected</xsl:attribute>
						<xsl:attribute name="sls-setting-selected">true</xsl:attribute>
					</xsl:if>
					<span class="picto"></span>
					<span class="label">|||sls:lang:SLS_BO_GENERIC_EXPAND|||</span>
				</a>
				<a href="" title="" class="screen-layout-condense" sls-setting-name="list_view" sls-setting-value="collapse" sls-setting-selected="false" sls-setting-selected-class="selected">
					<xsl:if test="//Statics/Site/BoMenu/admin/settings/setting[@key='list_view'] = 'collapse'">
						<xsl:attribute name="class">screen-layout-condense selected</xsl:attribute>
						<xsl:attribute name="sls-setting-selected">true</xsl:attribute>
					</xsl:if>
					<span class="picto"></span>
					<span class="label">|||sls:lang:SLS_BO_GENERIC_COLLAPSE|||</span>
				</a>

			</div>

			<div class="sls-bo-listing-container-positioning">
				<div class="sls-bo-listing-container">
					<div class="sls-bo-listing-head">
						<div class="sls-bo-listing-cell sls-listing-selection-btn">
							<div class="checkbox">
								<input type="checkbox" class="check-all" name="{name(//View/entities/entity[1]/*[1])}[]" value="all" />
							</div>
						</div>
						<xsl:for-each select="//View/columns/column">
							<div class="sls-bo-listing-cell">
								<div class="relative">
									<span class="column-label">
										<xsl:for-each select="labels_html/label_html">
											<span><xsl:value-of select="." /><xsl:if test="position() &gt; 1">&#160;</xsl:if></span>
										</xsl:for-each>
									</span>
								</div>
							</div>
						</xsl:for-each>
						<xsl:if test="//Statics/Site/BoMenu/various/user_status/@authorized = 'true'">
							<div class="sls-bo-listing-cell drag-and-drop">
								<div class="picto drag-and-drop-picto"></div>
							</div>
						</xsl:if>
					</div>

					<xsl:for-each select="//View/entities/entity">
						<xsl:variable name="recordset" select="position()" />
						<xsl:if test="$recordset != 1">
							<div class="sls-bo-listing-row-separator"></div>
						</xsl:if>
						<div class="sls-bo-listing-row">
							<div class="sls-bo-listing-recordset sls-bo-color-parent">
								<div class="sls-bo-listing-cell sls-listing-selection-btn">
									<div class="checkbox">
										<input type="checkbox" name="user[login]" value="{login}" />
									</div>
								</div>
								<div class="sls-bo-listing-cell user-profile-picture-cell">
									<div class="sls-bo-listing-cell-relative">
										<div class="sls-bo-listing-cell-content user-profile-picture-container sls-bo-color-border-hover">
											<img sls-image-src="{photo}?{php:functionString('time')}" sls-image-fit="cover" class="sls-image" alt="{concat(name,' ',firstname)}" title="{concat(name,' ',firstname)}" />
										</div>
									</div>
								</div>
								<div class="sls-bo-listing-cell">
									<div class="sls-bo-listing-cell-content">
										<xsl:value-of select="firstname" />
									</div>
								</div>
								<div class="sls-bo-listing-cell">
									<div class="sls-bo-listing-cell-content">
										<xsl:value-of select="name" />
									</div>
								</div>
								<div class="sls-bo-listing-cell">
									<div class="sls-bo-listing-cell-content">
										<xsl:value-of select="login" />
									</div>
								</div>
								<div class="sls-bo-listing-cell">
									<div class="sls-bo-listing-cell-content">
										<xsl:value-of select="last_connection" />
									</div>
								</div>
								<xsl:if test="//Statics/Site/BoMenu/various/user_status/@authorized = 'true'">
									<div class="sls-bo-listing-cell drag-and-drop">
										<div class="sls-bo-listing-cell-content">
											<div class="toggler-btn toggler-btn-radio vertical enabled" sls-toggler-url="{concat(//Statics/Site/BoMenu/various/user_status, login, '/enabled/')}" sls-toggler-activated="true" sls-toggler-notification="SLS_BO_GENERIC_SUBMIT_SUCCESS_EDIT">
												<xsl:if test="login = //Statics/Site/BoMenu/admin/login"><xsl:attribute name="sls-toggler-activated">false</xsl:attribute></xsl:if>
												<div class="toggler-btn-knob"></div>
												<input type="radio" name="user-enabled-{position()}" sls-toggler-state="on" value="true">
													<xsl:if test="enabled = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
												</input>
												<input type="radio" name="user-enabled-{position()}" sls-toggler-state="off" value="false">
													<xsl:if test="enabled = 'false'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
												</input>
											</div>
										</div>
									</div>
								</xsl:if>
							</div>
						</div>
					</xsl:for-each>
				</div>
			</div>
		</div>

	</xsl:template>
</xsl:stylesheet>