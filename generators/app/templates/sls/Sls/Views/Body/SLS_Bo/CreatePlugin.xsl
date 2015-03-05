<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="CreatePlugin">
	
		<h1>Plugins Management</h1>
		<h2>Create your own SillySmart Plugin</h2>
		<!-- LIST OWN PLUGINS -->
		<xsl:if test="//View/step = 'list'">
			<xsl:if test="count(//View/own_plugin/plugin) &gt; 0">
				<table>
					<tr>
						<th>Plugin Name</th>
						<th>Edit Plugin</th>
						<th>Customize</th>
						<th>Delete</th>
						<th>submit to the community</th>
					</tr>
					<xsl:for-each select="//View/own_plugin/plugin">
						<tr>
							<td>
								<strong><xsl:value-of select="name" /></strong><br />
								<xsl:value-of select="description"/>
							</td>
							<td>
								<a href="{editAppli}" title=""><img src="{concat($sls_url_img_core_icons, 'application_edit.png')}" title="" alt="" /></a>
							</td>
							<td>
								<xsl:if test="custom = 1">
									<a href="{edit}" title="edit"><img src="{concat($sls_url_img_core_icons, 'plugin_edit.png')}" alt="edit" title="edit" /></a>
								</xsl:if>
								<xsl:if test="custom = 0">
									<img src="{concat($sls_url_img_core_icons, 'plugin_disabled.png')}" alt="Not editable" title="Not editable" />
								</xsl:if>
							</td>
							<td>
								<a href="{delete}" title="" class="delete_plugin"><img src="{concat($sls_url_img_core_icons, 'bin.png')}" title="" alt="" /></a>
							</td>
							<td>
								<a href="" title=""><img src="{concat($sls_url_img_core_icons, 'world_add.png')}" title="" alt="" /></a>
							</td>
						</tr>
					</xsl:for-each>							
				</table>
			</xsl:if>
			<xsl:if test="count(//View/own_plugin/plugin) = 0">
				<h3>You have created any plugins</h3>
			</xsl:if>
			<div>
				<a href="{//Statics/Sls/Configs/action/links/link[name='CREATE']/href}" title="Start a new Plugin Creation">Start a new Plugin Creation</a>
			</div>
		</xsl:if>
		<!-- /LIST OWN PLUGINS -->
		<!-- CREATE A PLUGIN -->
		<xsl:if test="//View/step = 'create'">
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="color:red">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<fieldset>
				<legend>Plugin Creation</legend>
				<form action="" enctype="multipart/form-data" method="post">
					<div>
						<label for="name">Common name :</label>
						<input type="text" name="name" id="name">
							<xsl:if test="//View/form/name != ''">
								<xsl:attribute name="value"><xsl:value-of select="//View/form/name" /></xsl:attribute>
							</xsl:if>
						</input>
					</div>
					<div>
						<label for="code">Code name :</label>
						<input type="text" name="code" id="code">
							<xsl:if test="//View/form/code != ''">
								<xsl:attribute name="value"><xsl:value-of select="//View/form/code" /></xsl:attribute>
							</xsl:if>
						</input>
					</div>
					<div>
						Is your plugin an output type (like RSS or ATOM)  ? :
						<input type="radio" name="output" id="output_yes" value="yes">
							<xsl:if test="//View/form/output != '' and //View/form/output = 'yes'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="output_yes">yes</label>
						<input type="radio" name="output" id="output_no" value="no" checked='checked'>
							<xsl:if test="//View/form/output != '' and //View/form/output = 'no'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="custom_no">no</label>
					</div>
					<div>
						Is it customizable ? :
						<input type="radio" name="custom" id="custom_yes" value="yes">
							<xsl:if test="//View/form/custom != '' and //View/form/custom = 'yes'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="custom_yes">yes</label>
						<input type="radio" name="custom" id="custom_no" value="no">
							<xsl:if test="//View/form/custom != '' and //View/form/custom = 'no'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="custom_no">no</label>
					</div>
					<div>
						Do you want a file or a directory ? :
						<input type="radio" name="path" id="path_file" value="file">
							<xsl:if test="//View/form/path != '' and //View/form/path = 'file'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="path_file">file</label>
						<input type="radio" name="path" id="path_dir" value="dir">
							<xsl:if test="//View/form/path != '' and //View/form/path = 'no'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="path_dir">directory</label>
					</div>
					<div>
						<label for="full_description">Full description of the plugin :</label>
						<textarea name="full_description" id="full_description">
							<xsl:if test="//View/form/fill_description != ''">
								<xsl:value-of select="//View/form/fill_description" />
							</xsl:if>
						</textarea>
					</div>
					<div>
						<input type="submit" value="create" />
					</div>
					<input type="hidden" name="reload" value="true" />
				</form>
			</fieldset>
		</xsl:if>	
		<!-- /CREATE A PLUGIN -->
		<!-- EDIT A PLUGIN -->
		<xsl:if test="//View/step = 'edit'">
			<xsl:if test="count(//View/errors/error) &gt; 0">
				<div style="color:red">
					<xsl:for-each select="//View/errors/error">
						<xsl:value-of select="." /><br />
					</xsl:for-each>
				</div>
			</xsl:if>
			<fieldset>
				<legend>Plugin Edition</legend>
				<form action="" enctype="multipart/form-data" method="post">
					<div>
						<label for="name">Common name :</label>
						<input type="text" name="name" id="name" value="{//View/plugin/name}">
							<xsl:if test="//View/form/name != ''">
								<xsl:attribute name="value"><xsl:value-of select="//View/form/name" /></xsl:attribute>
							</xsl:if>
						</input>
					</div>
					<div>
						<label for="code">Code name :</label>
						<input type="text" name="code" id="code" value="{//View/plugin/code}">
							<xsl:if test="//View/form/code != ''">
								<xsl:attribute name="value"><xsl:value-of select="//View/form/code" /></xsl:attribute>
							</xsl:if>
						</input>
					</div>
					<div>
						Is your plugin an output type (like RSS or ATOM)  ? :
						<input type="radio" name="output" id="output_yes" value="yes">
							<xsl:if test="//View/plugin/output = 'yes'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
							<xsl:if test="//View/form/output != '' and //View/form/output = 'yes'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="output_yes">yes</label>
						<input type="radio" name="output" id="output_no" value="no">
							<xsl:if test="//View/plugin/output = 'no'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
							<xsl:if test="//View/form/output != '' and //View/form/output = 'no'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="output_no">no</label>
					</div>
					<div>
						Is it customizable ? :
						<input type="radio" name="custom" id="custom_yes" value="yes">
							<xsl:if test="//View/plugin/custom = 'yes'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
							<xsl:if test="//View/form/custom != '' and //View/form/custom = 'yes'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="custom_yes">yes</label>
						<input type="radio" name="custom" id="custom_no" value="no">
							<xsl:if test="//View/plugin/custom = 'no'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
							<xsl:if test="//View/form/custom != '' and //View/form/custom = 'no'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="custom_no">no</label>
					</div>
					<div>
						Do you want a file or a directory ? :
						<input type="radio" name="path" id="path_file" value="file">
							<xsl:if test="//View/plugin/path = 'file'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
							<xsl:if test="//View/form/path != '' and //View/form/path = 'file'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="path_file">file</label>
						<input type="radio" name="path" id="path_dir" value="dir">
							<xsl:if test="//View/plugin/path = 'dir'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
							<xsl:if test="//View/form/path != '' and //View/form/path = 'no'">
								<xsl:attribute name="checked" select="'checked'" />
							</xsl:if>
						</input>
						<label for="path_dir">directory</label>
					</div>
					<div>
						<label for="full_description">Full description of the plugin :</label>
						<textarea name="full_description" id="full_description">
							<xsl:if test="count(//View/form/fill_description) = 0 or //View/form/fill_description = ''">
								<xsl:value-of select="//View/plugin/fill_description" />
							</xsl:if>
							<xsl:if test="//View/form/fill_description != ''">
								<xsl:value-of select="//View/form/fill_description" />
							</xsl:if>
						</textarea>
					</div>
					<div>
						<input type="submit" value="Save changes" />
					</div>
					<input type="hidden" name="reload" value="true" />
				</form>
			</fieldset>
		</xsl:if>
		<!-- /EDIT A PLUGIN -->
				
	</xsl:template>
</xsl:stylesheet>