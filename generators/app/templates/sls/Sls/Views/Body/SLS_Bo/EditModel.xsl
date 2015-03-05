<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="EditModel">	
	
		<h1>Edit the model `<xsl:value-of select="//View/model/class" />`<a href="#" onclick="confirmDelete('{concat(//View/delete,'/name/',//View/model/db,'_',//View/model/table)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" /></a></h1>
		<xsl:if test="count(//View/model/columns/column[fk != '']) = 2">
			<xsl:choose>
				<xsl:when test="//View/model/is_data_bearer = 'false'">
					<a href="{//View/model/url_data_bearer}" title="Specify a data bearer">This model is a data bearer ?</a><br />
				</xsl:when>
				<xsl:otherwise>
					<a href="#" onclick="confirmDelete('{concat(//View/delete_bearer,'/name/',//View/model/db,'_',//View/model/table)}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Cancel data bearer" alt="Cancel data bearer" style="border:0" align="absmiddle" /></a>&#160;This model is a data bearer manageable on model `<xsl:value-of select="//View/model/is_data_bearer" />`
				</xsl:otherwise>
			</xsl:choose>						
		</xsl:if>
		<table cellspacing="0" cellpadding="5" border="1">
			<tr>
				<td style="background-color:#E9E9E9;color:#000;">Class</td>
				<td><xsl:value-of select="//View/model/class" /></td>
			</tr>
			<tr>
				<td style="background-color:#E9E9E9;color:#000;">Description</td>
				<td>
					<form action="{//View/descriptions}" method="post">
						<input type="hidden" name="__table" value="{concat(//View/model/db,'_',//View/model/table)}" />
						<input type="text" name="description" value="{//View/model/description}" maxlength="60" />
						<input type="submit" value="Update" />
					</form>
				</td>
			</tr>
			<tr>
				<td style="background-color:#E9E9E9;color:#000;">Database</td>
				<td><xsl:value-of select="//View/model/db" /></td>
			</tr>
			<tr>
				<td style="background-color:#E9E9E9;color:#000;">Table</td>
				<td><xsl:value-of select="//View/model/table" /></td>
			</tr>
			<tr>
				<td style="background-color:#E9E9E9;color:#000;">PK</td>
				<td><xsl:value-of select="//View/model/pk" /><xsl:if test="//View/model/multilanguage = 'true'">, pk_lang</xsl:if></td>
			</tr>
			<tr>
				<td style="background-color:#E9E9E9;color:#000;">Multilanguage</td>
				<td><xsl:value-of select="//View/model/multilanguage" /></td>
			</tr>
			<tr>
				<td style="background-color:#E9E9E9;color:#000;">Columns / <br />Descriptions / <br />Custom</td>
				<td style="padding:0">
					<form action="{//View/descriptions}" method="post">
						<input type="hidden" name="__table" value="{concat(//View/model/db,'_',//View/model/table)}" />
						<table cellpadding="3" cellspacing="0" border="1" style="border-collapse:collapse;">
							<tr style="background-color:#E9E9E9;color:#000;">
								<th>Column</th>
								<th>Description</th>
								<th>PK</th>
								<th>FK</th>
								<th>Type</th>
								<th>Filter</th>
								<!--
								<xsl:if test="//View/model/multilanguage = 'true'">
									<th>Multilanguage</th>
								</xsl:if>
								-->
							</tr>							
							<xsl:for-each select="//View/model/columns/column">
								<tr>
									<td>
										<label for="{concat('col_',name)}"><xsl:value-of select="name" /></label>
									</td>
									<td>
										<input type="text" name="{concat('col_',name)}" id="{concat('col_',name)}" value="{comment}" maxlength="60" />
									</td>
									<td>
										<input type="checkbox" disabled="disabled">
											<xsl:if test="name = //View/model/pk or (name = 'pk_lang' and //View/model/multilanguage = 'true')"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
										</input>
									</td>
									<td>
										<xsl:choose>
											<xsl:when test="fk != ''">
												<xsl:attribute name="style">text-align:right</xsl:attribute>
												<i> 
													<xsl:value-of select="fk" />
													<a href="{concat(//View/edit_fk,'/name/',//View/model/db,'_',//View/model/table,'/column/',name)}" style="padding-left:4px;"><img src="{concat($sls_url_img_core_icons,'edit16.png')}" title="Edit this FK" alt="Edit this FK" style="border:0" align="absmiddle" /></a>
													<a href="#" onclick="confirmDelete('{concat(//View/delete_fk,'/tableFk/',//View/model/db,'_',//View/model/table,'/columnFk/',name,'/tablePk/',//View/model/db,'_',fk)}');return false;" style="padding-left:4px;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete this FK" alt="Delete this FK" style="border:0" align="absmiddle" /></a>
												</i>
											</xsl:when>
											<xsl:otherwise>
												<xsl:attribute name="style">text-align:center</xsl:attribute>
												<input type="checkbox" disabled="disabled" />
											</xsl:otherwise>
										</xsl:choose>
									</td>
									<td>
										<xsl:choose>
											<xsl:when test="type != ''">
												<xsl:attribute name="style">text-align:right</xsl:attribute>
												<i>
													<xsl:value-of select="type" />
													<a href="{concat(//View/edit_type,'/name/',//View/model/db,'_',//View/model/table,'/column/',name)}" style="padding-left:4px;"><img src="{concat($sls_url_img_core_icons,'edit16.png')}" title="Edit this Type" alt="Edit this Type" style="border:0" align="absmiddle" /></a>
													<xsl:choose>
														<xsl:when test="allow_to_delete_type = 'false'">
															<img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete this Type" alt="Delete this Type" style="border:0;opacity:0.3;" align="absmiddle" />
														</xsl:when>
														<xsl:otherwise>
															<a href="#" onclick="confirmDelete('{concat(//View/delete_type,'/table/',//View/model/db,'_',//View/model/table,'/column/',name)}');return false;" style="padding-left:4px;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete this Type" alt="Delete this Type" style="border:0" align="absmiddle" /></a>
														</xsl:otherwise>
													</xsl:choose>
												</i>
											</xsl:when>
											<xsl:otherwise>
												<xsl:attribute name="style">text-align:center</xsl:attribute>
												<input type="checkbox" disabled="disabled" />
											</xsl:otherwise>
										</xsl:choose>
									</td>
									<td>
										<xsl:choose>
											<xsl:when test="count(filters/filter) &gt; 0">
												<xsl:attribute name="style">text-align:right</xsl:attribute>
												<i> 
													<xsl:for-each select="filters/filter">
														<xsl:value-of select="name" />
														<a href="#" onclick="confirmDelete('{url_delete}');return false;" style="padding-left:4px;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete this Filter" alt="Delete this Filter" style="border:0" align="absmiddle" /></a>
														<xsl:if test="position() &lt; last()"><br /></xsl:if>
													</xsl:for-each>
												</i>
											</xsl:when>
											<xsl:otherwise>
												<xsl:attribute name="style">text-align:center</xsl:attribute>
												<input type="checkbox" disabled="disabled" />
											</xsl:otherwise>
										</xsl:choose>
									</td>
									<!--
									<xsl:if test="//View/model/multilanguage = 'true'">
										<td>
											<xsl:attribute name="style">text-align:center</xsl:attribute>
											<input type="checkbox" name="{concat('multi_',name)}" onchange="">														
												<xsl:if test="multilanguage = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
												<xsl:if test="name = //View/model/pk or name = 'pk_lang'"><xsl:attribute name="disabled">disabled</xsl:attribute></xsl:if>
												<xsl:if test="name = 'pk_lang'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</td>
									</xsl:if>
									-->
								</tr>
							</xsl:for-each>
							<tr>
								<td></td>
								<td align="center"><input type="submit" value="Update descriptions" /></td>
								<td></td>
								<td align="center"><a href="{//View/model/url_add_fk}" title="Add a foreign key on a column"><img src="{concat($sls_url_img_core_icons,'add16.png')}" title="Add a Foreign Key" alt="Add a Foreign Key" style="border:0" align="absmiddle" /></a></td>
								<td align="center"><a href="{//View/model/url_add_type}" title="Add a specific type on a column"><img src="{concat($sls_url_img_core_icons,'add16.png')}" title="Add a Type" alt="Add a Type" style="border:0" align="absmiddle" /></a></td>
								<td align="center"><a href="{//View/model/url_add_filter}" title="Add a specific filter on a column"><img src="{concat($sls_url_img_core_icons,'add16.png')}" title="Add a Filter" alt="Add a Filter" style="border:0" align="absmiddle" /></a></td>
								<!--
								<xsl:if test="//View/model/multilanguage = 'true'">
									<td align="center"><input type="submit" value="Update multi" /></td>
								</xsl:if>
								-->
							</tr>
						</table>
					</form>
				</td>
			</tr>
		</table>
		
		<table style="margin-top:10px;">											
			<tr>
				<td valign="bottom"><b><u>Source of the current model :</u></b></td>
				<td valign="bottom"><b><u>Source of the current table :</u></b></td>
			</tr>
			<xsl:if test="//View/model/current_table != -1 and //View/model/current_source != //View/model/current_table">
				<tr style="">
					<td colspan="2" align="center">
						<br />Your model isn't up to date, you should update it. <a href="{concat(//View/update,'/name/',//View/model/db,'_',//View/model/table)}" title="Update this model">Update this model</a>
					</td>
				</tr>
			</xsl:if>
			<xsl:if test="//View/model/current_table != -1 and //View/model/current_source = //View/model/current_table">
				<tr>
					<td colspan="2" align="center">
						<br />Your model is up to date.
					</td>
				</tr>
			</xsl:if>
			<tr>
				<td valign="top">								
					<pre name="code" class="brush: php">
						<xsl:value-of select="//View/model/current_source" />
					</pre>								
				</td>
				<td valign="top">
					<xsl:if test="//View/model/current_table = -1">
						Sorry, this model is deprecated. Her mapped table doesn't exists anymore.<br />
						You should re-create this table or delete this model.
					</xsl:if>
					<xsl:if test="//View/model/current_table != -1">
						<pre name="code2" class="brush: php">
							<xsl:value-of select="//View/model/current_table" />
						</pre>
					</xsl:if>
				</td>
			</tr>						
			<xsl:if test="//View/model/current_table != -1 and //View/model/current_source != //View/model/current_table">
				<tr>
					<td colspan="2" align="center">
						Your model isn't up to date, you should update it. <a href="{concat(//View/update,'/name/',//View/model/db,'_',//View/model/table)}" title="Update this model">Update this model</a>
					</td>
				</tr>
			</xsl:if>
			<xsl:if test="//View/model/current_table != -1 and //View/model/current_source = //View/model/current_table">
				<tr>
					<td colspan="2" align="center">
						Your model is up to date.
					</td>
				</tr>
			</xsl:if>
		</table>
				
	</xsl:template>
</xsl:stylesheet>