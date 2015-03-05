<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="EditBo">
		<div id="SLS_EditBo">
			<form method="post" action="">
				<h1>Edit the back-office `<xsl:value-of select="//View/bo/class" />`<a href="#" onclick="confirmDelete('{//View/url_delete}');return false;"><img src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" /></a></h1>

				
				<fieldset>
					<legend>Bo menu</legend>
					<select name="category">
						<option value="">--None</option>
						<xsl:for-each select="//View/categories/category">
							<option value="{.}">
								<xsl:if test=". = //View/bo/category"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
								<xsl:value-of select="." />
							</option>
						</xsl:for-each>
					</select>&#160;
					<a href="{//View/url_add_category}">Add a category</a>
				</fieldset>
				

				<fieldset>
					<legend>Columns</legend>
					<ul class="columns_head">
						<li>
							<div class="col">Column</div>
							<div class="col available">Filter</div>
							<div class="col available">List</div>
							<div class="col available">Quick Edit</div>
							<div class="col available">Embed HTML</div>
							<xsl:if test="//View/bo/multilanguage = 'true'">
								<div class="col available">Multilanguage</div>
							</xsl:if>
							<div class="clear"></div>
						</li>
					</ul>
					<ul class="columns_body">
						<xsl:for-each select="//View/bo/columns/line">
							<li>
								<div class="col">
									<xsl:value-of select="column_label" />
									<input type="hidden" name="bo[columns][{position()}][table]" value="{table}"/>
									<input type="hidden" name="bo[columns][{position()}][column_value]" value="{column_value}"/>
									<input type="hidden" name="bo[columns][{position()}][column_label]" value="{column_label}"/>
									<input type="hidden" class="position" name="bo[columns][{position()}][position]" value="{position()}"/>
								</div>
								<div class="col available">
									<input type="checkbox" name="bo[columns][{position()}][display_filter]">
										<xsl:if test="display_filter = 'on'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
									</input>
								</div>
								<div class="col available">
									<input type="checkbox" name="bo[columns][{position()}][display_list]">
										<xsl:if test="display_list = 'on'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
									</input>
								</div>
								<div class="col available">
									<xsl:choose>
										<xsl:when test="table = //View/bo/table and type_file = 'false' and type_pk = 'false' and type_fk = 'false'">
											<input type="checkbox" name="bo[columns][{position()}][allow_edit]">
												<xsl:if test="allow_edit = 'on'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</xsl:when>
										<xsl:otherwise><input type="checkbox" disabled="disabled" name="bo[columns][{position()}][allow_edit]" value="off" /></xsl:otherwise>
									</xsl:choose>
								</div>
								<div class="col available">
									<xsl:choose>
										<xsl:when test="table = //View/bo/table and type_string = 'true' and type_pk = 'false'">
											<input type="checkbox" name="bo[columns][{position()}][allow_html]">
												<xsl:if test="allow_html = 'on'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
											</input>
										</xsl:when>
										<xsl:otherwise><input type="checkbox" disabled="disabled" name="bo[columns][{position()}][allow_html]" value="off" /></xsl:otherwise>
									</xsl:choose>
								</div>

								<xsl:if test="//View/bo/multilanguage = 'true'">
									<div class="col available">
										<xsl:choose>
											<xsl:when test="table = //View/bo/table and type_pk='false'">
												<input type="checkbox" name="bo[columns][{position()}][multilanguage]">
													<xsl:if test="multilanguage = 'on'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
												</input>
											</xsl:when>
											<xsl:otherwise><input type="checkbox" disabled="disabled" name="bo[columns][{position()}][multilanguage]" value="off" /></xsl:otherwise>
										</xsl:choose>
									</div>
								</xsl:if>

								<div class="clear"></div>
							</li>
						</xsl:for-each>
					</ul>
				</fieldset>

				<fieldset>
					<legend>Joins</legend>
					<table>
						<tbody>
							<xsl:for-each select="//View/bo/joins/line">
								<xsl:variable name="table" select="." />
								<tr>
									<td>
										<select name="bo[joins][]">
											<xsl:for-each select="//View/joins/join">
												<option value="{.}">
													<xsl:if test="$table = ."><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
													<xsl:value-of select="." />
												</option>
											</xsl:for-each>
										</select>
										<img class="delete" src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" />
									</td>
								</tr>
							</xsl:for-each>
						</tbody>

						<tfoot>
							<tr class="example">
								<td>
									<select name="{{NAME}}[joins][]">
										<xsl:for-each select="//View/joins/join">
											<option value="{.}"><xsl:value-of select="." /></option>
										</xsl:for-each>
									</select>
									<img class="delete" src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" />
								</td>
							</tr>
							<tr>
								<td>
									<input type="button" value="Add" name="joins" class="add" />
								</td>
							</tr>
						</tfoot>
					</table>
				</fieldset>

				<fieldset>
					<legend>Wheres</legend>
						<table>
							<tbody>
							<xsl:for-each select="//View/bo/wheres/line">
								<xsl:variable name="column" select="column" />
								<xsl:variable name="mode" select="mode" />
								<xsl:variable name="value" select="value" />
								<tr>
									<td>
										<select name="bo[wheres][{position()}][column]">
											<xsl:for-each select="//View/bo/columns/line">
												<option value="{column_value}">
													<xsl:if test="$column = column_value">
														<xsl:attribute name="selected">selected</xsl:attribute>
													</xsl:if>
													<xsl:value-of select="column_label" />
												</option>
											</xsl:for-each>
										</select>
										<select class="operator" name="bo[wheres][{position()}][mode]">
											<xsl:for-each select="//View/operators/operator">
												<option value="{operator_value}">
													<xsl:if test="operator_need_value = 'true'">
														<xsl:attribute name="class">operator_need_value</xsl:attribute>
													</xsl:if>
													<xsl:if test="$mode = operator_value">
														<xsl:attribute name="selected">selected</xsl:attribute>
													</xsl:if>
													<xsl:value-of select="operator_label" />
												</option>
											</xsl:for-each>
										</select>
										<input type="text" name="bo[wheres][{position()}][value]" value="{$value}">
											<xsl:if test="//View/operators/operator[operator_value = $mode]/operator_need_value = 'false'">
												<xsl:attribute name="class">hide</xsl:attribute>
											</xsl:if>
										</input>
										<img class="delete" src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" />
									</td>
								</tr>
							</xsl:for-each>
						</tbody>
							<tfoot>
								<tr class="example">
									<td>
										<select name="{{NAME}}[wheres][{{POSITION}}][column]">
											<xsl:for-each select="//View/bo/columns/line">
												<option value="{column_value}">
													<xsl:value-of select="column_label" />
												</option>
											</xsl:for-each>
										</select>
										<select class="operator"  name="{{NAME}}[wheres][{{POSITION}}][mode]">
											<xsl:for-each select="//View/operators/operator">
												<option value="{operator_value}">
													<xsl:if test="operator_need_value = 'true'">
														<xsl:attribute name="class">operator_need_value</xsl:attribute>
													</xsl:if>
													<xsl:value-of select="operator_label" />
												</option>
											</xsl:for-each>
										</select>
										<input type="text" name="{{NAME}}[wheres][{{POSITION}}][value]" value="" />
										<img class="delete" src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" />
									</td>
								</tr>
								<tr>
									<td>
										<input type="button" value="Add" name="wheres" class="add"/>
									</td>
								</tr>
							</tfoot>
						</table>
				</fieldset>

				<fieldset>
					<legend>Groups</legend>
					<table>
						<tr>
							<td>
								<select name="bo[groups][]" multiple="multiple" size="{count(//View/bo/columns/line)}">
									<xsl:for-each select="//View/bo/columns/line">
										<option value="{column_value}">
											<xsl:if test="column_group = 'true'">
												<xsl:attribute name="selected">selected</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="column_label" />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>
				</fieldset>

				<fieldset>
					<legend>Orders</legend>
					<table>
						<tbody>
							<xsl:for-each select="//View/bo/orders/line">
								<xsl:variable name="order" select="order" />
								<xsl:variable name="table" select="table" />
								<xsl:variable name="column" select="column" />

								<tr>
									<td>
										<select name="bo[orders][{position()}][column]">
											<xsl:for-each select="//View/bo/columns/line">
												<option value="{column_value}">
													<xsl:if test="$column = column_value">
														<xsl:attribute name="selected">selected</xsl:attribute>
													</xsl:if>
													<xsl:value-of select="column_label" />
												</option>
											</xsl:for-each>
										</select>
										<select name="bo[orders][{position()}][order]">
											<xsl:for-each select="//View/orders/order">
												<option value="{order_value}">
													<xsl:if test="$order = order_value">
														<xsl:attribute name="selected">selected</xsl:attribute>
													</xsl:if>
													<xsl:value-of select="order_label" />
												</option>
											</xsl:for-each>
										</select>
										<img class="delete" src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" />
									</td>
								</tr>
							</xsl:for-each>
						</tbody>

						<tfoot>
							<tr class="example">
								<td>
									<select name="{{NAME}}[orders][{{POSITION}}][column]">
										<xsl:for-each select="//View/bo/columns/line">
											<option value="{column_value}">
												<xsl:value-of select="column_label" />
											</option>
										</xsl:for-each>
									</select>
									<select name="{{NAME}}[orders][{{POSITION}}][order]">
										<xsl:for-each select="//View/orders/order">
											<option value="{order_value}">
												<xsl:value-of select="order_label" />
											</option>
										</xsl:for-each>
									</select>
									<img class="delete" src="{concat($sls_url_img_core_icons,'delete16.png')}" title="Delete" alt="Delete" style="border:0" align="absmiddle" />
								</td>
							</tr>
							<tr>
								<td>
									<input type="button" value="Add" name="orders" class="add" />
								</td>
							</tr>
						</tfoot>
					</table>
				</fieldset>

				<fieldset>
					<legend>Limit</legend>
					<table>
						<tr>
							<td>
								<select name="bo[limits][0][length]">
									<xsl:for-each select="//View/limits/limit">
										<option value="{.}">
											<xsl:if test=". = //View/bo/limits/line[1]/length">
												<xsl:attribute name="selected">selected</xsl:attribute>
											</xsl:if>
											<xsl:value-of select="." />
										</option>
									</xsl:for-each>
								</select>
							</td>
						</tr>
					</table>

				</fieldset>

				<xsl:if test="count(//View/children/child) &gt; 0">
					<fieldset>
						<legend>Chidlren</legend>
	
						<table>
							<tr>
								<td>
									<select name="bo[children][]" multiple="multiple" size="{count(//View/children/child)}">
										<xsl:for-each select="//View/children/child">
											<option value="{child_value}">
												<xsl:if test="child_selected = 'true'">
													<xsl:attribute name="selected">selected</xsl:attribute>
												</xsl:if>
												<xsl:value-of select="child_value" />
											</option>
										</xsl:for-each>
									</select>
								</td>
							</tr>
						</table>
					</fieldset>
				</xsl:if>

				<input type="hidden" name="reload" value="true" />
				<input type="submit" value="Edit" />
			</form>
		</div>

	</xsl:template>
</xsl:stylesheet>