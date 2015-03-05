<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="ReportingBoAdd">
		<div id="reporting_add" class="reporting_form sls_select_relayer">
			<h1>Add a new graph</h1>

			<div class="form_gen form">
				<form method="post" action="">
					<div class="general">
						<div class="row">
							<div class="label">
								<label for="sls_graph_title">Title</label>
							</div>
							<div class="value">
								<div class="field">
									<xsl:if test="count(//View/errors/error[@column = 'sls_graph_title']) &gt; 0">
										<xsl:attribute name="class">field error</xsl:attribute>
									</xsl:if>

									<input type="text" name="sls_graph[sls_graph_title]" id="sls_graph_title" value="{//View/sls_graph/sls_graph_title}" />
								</div>
								<div class="clear"></div>
							</div>
							<div class="clear"></div>
						</div>

						<div class="row">
							<div class="label">
								<label for="sls_graph_type">Type</label>
							</div>
							<div class="value">
								<div class="field">
									<xsl:if test="count(//View/errors/error[@column = 'sls_graph_type']) &gt; 0">
										<xsl:attribute name="class">field error</xsl:attribute>
									</xsl:if>

									<select name="sls_graph[sls_graph_type]" id="sls_graph_type">
										<option value=""></option>
										<xsl:for-each select="//View/sls_graph_types/sls_graph_type">
											<option value="{sls_graph_type_value}">
												<xsl:if test="sls_graph_type_value = //View/sls_graph/sls_graph_type"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
												<xsl:value-of select="sls_graph_type_label" />
											</option>
										</xsl:for-each>
									</select>
								</div>
								<div class="clear"></div>
							</div>
							<div class="clear"></div>
						</div>

						<div class="row">
							<div class="label">
								<label for="sls_graph_query_table">Table</label>
							</div>
							<div class="value">
								<div class="field">
									<xsl:if test="count(//View/errors/error[@column = 'sls_graph_query_table']) &gt; 0">
										<xsl:attribute name="class">field error</xsl:attribute>
									</xsl:if>

									<select name="sls_graph_query[sls_graph_query_table]" id="sls_graph_query_table">
										<option value=""></option>
										<xsl:for-each select="//View/tables/table">
											<option value="{table_name}">
												<xsl:if test="table_name = //View/sls_graph/sls_graph_query/sls_graph_query_table"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
												<xsl:value-of select="table_label" />
											</option>
										</xsl:for-each>
									</select>
								</div>
								<div class="clear"></div>
							</div>
							<div class="clear"></div>
						</div>
					</div>

					<div class="sections">
						<div class="section" id="section_pie">
							<xsl:if test="//View/sls_graph/sls_graph_type = 'pie'"><xsl:attribute name="class">section selected</xsl:attribute></xsl:if>
							<div class="row">
								<div class="label">
									<label for="sls_graph_pie_group_by">Group by</label>
								</div>
								<div class="value">
									<div class="field field_columns">
										<xsl:if test="count(//View/errors/error[@column = 'sls_graph_pie_group_by']) &gt; 0">
											<xsl:attribute name="class">field field_columns error</xsl:attribute>
										</xsl:if>

										<select name="sls_graph[sls_graph_pie_group_by]" id="sls_graph_pie_group_by" class="columns">
											<option value="{//View/sls_graph/sls_graph_pie_group_by}"></option>
										</select>
									</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>

							<div class="clear"></div>
						</div>
						<div class="section" id="section_bar">
							<xsl:if test="//View/sls_graph/sls_graph_type = 'bar'"><xsl:attribute name="class">section selected</xsl:attribute></xsl:if>
							<div class="row">
								<div class="label">
									<label for="sls_graph_bar_group_by">Group by</label>
								</div>
								<div class="value">
									<div class="field field_columns">
										<xsl:if test="count(//View/errors/error[@column = 'sls_graph_bar_group_by']) &gt; 0">
											<xsl:attribute name="class">field field_columns error</xsl:attribute>
										</xsl:if>
										<select name="sls_graph[sls_graph_bar_group_by]" id="sls_graph_bar_group_by" class="columns">
											<option value="{//View/sls_graph/sls_graph_bar_group_by}"></option>
										</select>
									</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>

							<div class="row">
								<div class="label">
									<label for="sls_graph_bar_stacked_field">Stacked field</label>
								</div>
								<div class="value">
									<div class="field field_columns">
										<xsl:if test="count(//View/errors/error[@column = 'sls_graph_bar_stacked_field']) &gt; 0">
											<xsl:attribute name="class">field field_columns error</xsl:attribute>
										</xsl:if>
										<select name="sls_graph[sls_graph_bar_stacked_field]" id="sls_graph_bar_stacked_field" class="columns">
											<option value="{//View/sls_graph/sls_graph_bar_stacked_field}"></option>
										</select>
									</div>

									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>

							<div class="row">
								<div class="label">
									<label for="sls_graph_bar_aggregation">Aggregation</label>
								</div>
								<div class="value">
									<div class="field">
										<xsl:if test="count(//View/errors/error[@column = 'sls_graph_bar_aggregation']) &gt; 0">
											<xsl:attribute name="class">field error</xsl:attribute>
										</xsl:if>
										<select name="sls_graph[sls_graph_bar_aggregation]" id="sls_graph_bar_aggregation" class="aggregation">
											<option value=""></option>
											<xsl:for-each select="//View/sls_graph_aggregation_types/sls_graph_aggregation_type">
												<option value="{sls_graph_aggregation_type_value}">
													<xsl:if test="sls_graph_aggregation_type_value = //View/sls_graph/sls_graph_bar_aggregation"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
													<xsl:value-of select="sls_graph_aggregation_type_label" />
												</option>
											</xsl:for-each>
										</select>
									</div>

									<div class="field field_aggregation field_columns">
										<xsl:if test="count(//View/errors/error[@column = 'sls_graph_bar_aggregation_field']) &gt; 0">
											<xsl:attribute name="class">field field_columns error</xsl:attribute>
										</xsl:if>
										<select name="sls_graph[sls_graph_bar_aggregation_field]" id="sls_graph_bar_aggregation_field" class="columns">
											<option value="{//View/sls_graph/sls_graph_bar_aggregation_field}"></option>
										</select>
									</div>

									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>
						</div>

						<div class="section" id="section_pivot">
							<xsl:if test="//View/sls_graph/sls_graph_type = 'pivot'"><xsl:attribute name="class">section selected</xsl:attribute></xsl:if>

							<div class="row">
								<div class="label">
									<label for="sls_graph_pivot_line">Line</label>
								</div>
								<div class="value">
									<div class="field field_columns">
										<xsl:if test="count(//View/errors/error[@column = 'sls_graph_pivot_line']) &gt; 0">
											<xsl:attribute name="class">field field_columns error</xsl:attribute>
										</xsl:if>
										<select name="sls_graph[sls_graph_pivot_line]" id="sls_graph_pivot_line" class="columns">
											<option value="{//View/sls_graph/sls_graph_pivot_line}"></option>
										</select>
									</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>

							<div class="row">
								<div class="label">
									<label for="sls_graph_pivot_column">Column</label>
								</div>
								<div class="value">
									<div class="field field_columns">
										<xsl:if test="count(//View/errors/error[@column = 'sls_graph_pivot_column']) &gt; 0">
											<xsl:attribute name="class">field field_columns error</xsl:attribute>
										</xsl:if>
										<select name="sls_graph[sls_graph_pivot_column]" id="sls_graph_pivot_column" class="columns">
											<option value="{//View/sls_graph/sls_graph_pivot_column}"></option>
										</select>
									</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>

							<div class="row">
								<div class="label">
									<label for="sls_graph_pivot_aggregation">Aggregation</label>
								</div>
								<div class="value">
									<div class="field">
										<xsl:if test="count(//View/errors/error[@column = 'sls_graph_pivot_aggregation']) &gt; 0">
											<xsl:attribute name="class">field field_columns error</xsl:attribute>
										</xsl:if>
										<select name="sls_graph[sls_graph_pivot_aggregation]" id="sls_graph_pivot_aggregation" class="aggregation">
											<option value=""></option>
											<xsl:for-each select="//View/sls_graph_aggregation_types/sls_graph_aggregation_type">
												<option value="{sls_graph_aggregation_type_value}">
													<xsl:if test="sls_graph_aggregation_type_value = //View/sls_graph/sls_graph_pivot_aggregation"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
													<xsl:value-of select="sls_graph_aggregation_type_label" />
												</option>
											</xsl:for-each>
										</select>
									</div>

									<div class="field field_aggregation field_columns">
										<xsl:if test="count(//View/errors/error[@column = 'sls_graph_pivot_aggregation_field']) &gt; 0">
											<xsl:attribute name="class">field field_aggregation field_columns error</xsl:attribute>
										</xsl:if>
										<select name="sls_graph[sls_graph_pivot_aggregation_field]" id="sls_graph_pivot_aggregation_field" class="columns">
											<option value="{//View/sls_graph/sls_graph_pivot_aggregation_field}"></option>
										</select>
									</div>

									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>
						</div>

						<div class="section" id="section_list">
							<xsl:if test="//View/sls_graph/sls_graph_type = 'list'"><xsl:attribute name="class">section selected</xsl:attribute></xsl:if>

							<div class="row">
								<div class="label">
									<label>Columns</label>
								</div>
								<div class="value">
									<div class="column_fields left">
										<h3>Available columns</h3>
										<ul class="original_fields">
                                            <span class="first_loader"></span>
										</ul>
									</div>

									<div class="actions">
										<a href="" title="" class="shift">&lt;</a>
										<a href="" title="" class="push">&gt;</a>
									</div>

									<div class="column_fields right">
										<h3>Selected columns</h3>
										<ul class="selected_fields">
											<xsl:for-each select="//View/sls_graph/sls_graph_query/sls_graph_query_columns/sls_graph_query_column">
												<li>
													<xsl:value-of select="sls_graph_query_column_label" />
													<input type="hidden" name="sls_graph_query[sls_graph_query_column][{position()-1}][sls_graph_query_column_value]" value="{sls_graph_query_column_value}" class="column value" />
													<input type="hidden" name="sls_graph_query[sls_graph_query_column][{position()-1}][sls_graph_query_column_label]" value="{sls_graph_query_column_label}" class="column label" />
												</li>
											</xsl:for-each>
										</ul>
									</div>

									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>

							<div class="clear"></div>
						</div>
					</div>

					<div class="general_end">
						<div class="row">
							<div class="label">
								<label>Filters</label>
							</div>
							<div class="value">
								<div class="graph_where">
									<!-- query_where example -->
									<xsl:call-template name="SlsGraphQueryWhere">
										<xsl:with-param name="sls_graph_query_where_path"></xsl:with-param>
										<xsl:with-param name="sls_graph_query_where_tree"></xsl:with-param>
										<xsl:with-param name="sls_graph_query_where_dom_id">sls_graph_query_where_example</xsl:with-param>
									</xsl:call-template>
									<!-- /query_where example -->

									<!-- query_where 1 = group NULL -->
									<xsl:call-template name="SlsGraphQueryWhere">
										<xsl:with-param name="sls_graph_query_where_path">//View/sls_graph/sls_graph_query/sls_graph_query_where</xsl:with-param>
										<xsl:with-param name="sls_graph_query_where_tree">sls_graph_query[sls_graph_query_where]</xsl:with-param>
										<xsl:with-param name="sls_graph_query_where_dom_id">sls_graph_query_where_root</xsl:with-param>
									</xsl:call-template>
								</div>
							</div>
							<div class="clear"></div>
						</div>

						<div class="row submit">
							<div class="label">
							</div>
							<div class="value">
								<span class="btn_gen">
									<span class="left"></span>
									<span class="middle">
										<input type="hidden" name="reload" value="true" />
										<input type="submit" name="submit_add" value="Add"  class="btn_content" />
									</span>
									<span class="right"></span>
								</span>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>