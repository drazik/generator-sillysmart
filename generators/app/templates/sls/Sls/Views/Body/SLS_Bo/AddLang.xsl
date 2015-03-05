<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="AddLang">
	
		<h1>Manage your Langs > Add a Lang</h1>
		
		<xsl:choose>
			<!-- First time -->
			<xsl:when test="count(//View/step) = 0">
				<form action="" method="post">				
					<input type="hidden" name="step" value="1" />
					
					<xsl:if test="count(//View/errors/error) &gt; 0">
						<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
							<xsl:value-of select="//View/errors/error" />
						</div>
					</xsl:if>
					
					<table border="0">
						<tr>
							<td><label for="lang">Please choose the lang you want to add</label></td>
							<td>
								<select name="lang" id="lang">
									<xsl:for-each select="//View/langs/lang">
										<option value="{.}">
											<xsl:if test=". = //View/lang_selected"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
											<xsl:value-of select="." />
										</option>										
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>					
					
					<input type="submit" value="Add" />					
				</form>
			</xsl:when>
			<!-- /First time -->
			
			<!-- Set informations -->
			<xsl:when test="//View/step = '2'">
				<xsl:if test="count(//View/errors/error) &gt; 0">
					<div style="display:block;margin-bottom:10px;margin-top:10px;color:red;font-weight:bold;">
						<xsl:for-each select="//View/errors/error">
							<xsl:value-of select="." /><br />
						</xsl:for-each>
					</div>
				</xsl:if>
				<form action="" method="post">
					<input type="hidden" name="step" value="2" />
					<input type="hidden" name="lang_to_add" value="{//View/lang_to_add}" />
					<input type="hidden" name="reload" value="true" />
					<fieldset>
						<legend>Generic Langs</legend>									
						<table>
							<xsl:for-each select="//View/generic_langs/generic_lang">
								<tr>
									<td><label for="{concat('generic_',key)}"><xsl:value-of select="key" /></label></td>
									<td><input type="text" name="{concat('generic_',key)}" id="{concat('generic_',key)}" value="{value}" /></td>
								</tr>
							</xsl:for-each>
						</table>
					</fieldset>
					<fieldset>
						<legend>Controllers &amp; Actions</legend>									
						<xsl:for-each select="//View/controllers/controller">
							<xsl:variable name="c_id" select="id" />
							<fieldset style="margin:10px;">
								<legend style="text-transform:none;">Controller '<xsl:value-of select="key" />' (Id: <xsl:value-of select="id" />)</legend>											
								<div class="c_translatation">
									<span style="display:block;text-decoration:underline;margin-bottom:10px;">Controller translations:</span>
									<xsl:for-each select="values/value">
										<xsl:value-of select="key" />: 
										<input type="text" value="{value}">
											<xsl:choose>
												<xsl:when test="key = //View/lang_to_add">
													<xsl:attribute name="name"><xsl:value-of select="concat($c_id,'_',key)" /></xsl:attribute>
												</xsl:when>
												<xsl:otherwise>
													<xsl:attribute name="disabled">disabled</xsl:attribute>
												</xsl:otherwise>
											</xsl:choose>
										</input>&#160;
									</xsl:for-each>
								</div>
								<div class="a_translatation" style="margin-top:20px;">
									<span style="display:block;text-decoration:underline;">Actions:</span>
									<xsl:for-each select="actions/action">
										<xsl:variable name="a_id" select="id" />
										<fieldset style="margin:10px;">
											<legend style="text-transform:none;">Action '<xsl:value-of select="key" />' (Id: <xsl:value-of select="id" /></legend>
											<xsl:for-each select="values/value">
												<xsl:value-of select="key" />: 
												<input type="text" value="{value}">
													<xsl:choose>
														<xsl:when test="key = //View/lang_to_add">
															<xsl:attribute name="name"><xsl:value-of select="concat($a_id,'_',key)" /></xsl:attribute>
														</xsl:when>
														<xsl:otherwise>
															<xsl:attribute name="disabled">disabled</xsl:attribute>
														</xsl:otherwise>
													</xsl:choose>
												</input>&#160;
											</xsl:for-each>
											<span style="display:block;text-decoration:underline;margin-top:20px;">Metas:</span>
											<table>
												<xsl:for-each select="metas/meta">
													<tr>
														<xsl:variable name="m_key" select="key" />
														<td>* <xsl:value-of select="php:functionString('ucfirst',key)" /> :</td>
														<xsl:for-each select="values/value">
															<td>
																<xsl:value-of select="key" />: 
																<input type="text" value="{value}">
																	<xsl:choose>
																		<xsl:when test="key = //View/lang_to_add">
																			<xsl:attribute name="name"><xsl:value-of select="concat('meta_',$m_key,'-',$a_id,'_',key)" /></xsl:attribute>
																		</xsl:when>
																		<xsl:otherwise>
																			<xsl:attribute name="disabled">disabled</xsl:attribute>
																		</xsl:otherwise>
																	</xsl:choose>
																</input>
															</td>
														</xsl:for-each>
													</tr>
												</xsl:for-each>
											</table>
										</fieldset>
									</xsl:for-each>
								</div>
							</fieldset>
						</xsl:for-each>									
					</fieldset>
					<i>Meta fields are facultatives.</i><br />
					<input type="submit" value="Save" />
				</form>
			</xsl:when>
			<!-- /Set informations -->
		</xsl:choose>
					
				
	</xsl:template>
</xsl:stylesheet>