<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<!-- 
	 	- Function EditPlugin
	 	- Generic's Plugins Management
	 	- Don't change anything
	-->
	<xsl:template name="EditPlugins">
		<xsl:param name="xpath"/>
		
		<xsl:for-each select="$xpath">
			<xsl:variable select="position()" name="pos" />
			<xsl:variable select="concat(path, '_alias')" name="alias" />
			<xsl:variable select="path" name="path" />
			<xsl:variable select="value" name="value" />
			<xsl:if test="clonable='true' and $xpath[number($pos - 1)]/tag = tag">
				<a href="{linkDel}" title=""><img src="{concat($sls_url_img_core_icons, 'cross.png')}" title="" alt="" /></a>
			</xsl:if>
			<xsl:if test="type = 'part'">
				<fieldset id="{$path}">
					<legend>
						<xsl:if test="clonable='true'">
							<xsl:value-of select="concat(label, ' ', index)" />
						</xsl:if>
						<xsl:if test="clonable='false'">
							<xsl:value-of select="label" />
						</xsl:if>
					</legend>
					<xsl:if test="clonable='true'">
						<label>Alias</label> 
						<input type="text" name="{$alias}" value="{alias}">
							<xsl:if test="count(//View/memory) = 1 and //View/memory/values[name=$alias]/value != ''">
								<xsl:attribute name="value">
									<xsl:value-of select="//View/memory/values[name=$alias]/value" />
								</xsl:attribute>
							</xsl:if>
						</input>
					</xsl:if>
					<xsl:call-template name="EditPlugins">
						<xsl:with-param name="xpath" select="$xpath[$pos]/field" />
					</xsl:call-template>
				</fieldset>
				
			</xsl:if>
			<xsl:if test="type != 'part'">
				<div id="{$path}">
					<xsl:if test="clonable='true'">
						<xsl:value-of select="concat(label, ' ', index)" />
					</xsl:if>
					<xsl:if test="clonable='false'">
						<xsl:value-of select="label" />
					</xsl:if>
					<xsl:if test="type='password'">
						<input type="password" name="{$path}">
							<xsl:if test="$value != ''">
								<xsl:attribute name="value">****</xsl:attribute>
							</xsl:if>
							<xsl:if test="$value != ''">
								<input type="hidden" name="{concat($path, '_encrypted')}" value="{$value}" />
							</xsl:if>
							<xsl:if test="$value = ''">
								<input type="hidden" name="{concat($path, '_proceed')}" value="proceed" />
							</xsl:if>
						</input>						
					</xsl:if>
					<xsl:if test="type='string' or type='int' or type='float'">
						<input type="text" name="{$path}" value="{$value}">
							<xsl:if test="count(//View/memory) = 1 and //View/memory/values[name=$path]/value != ''">
								<xsl:attribute name="value">
									<xsl:value-of select="//View/memory/values[name=$path]/value" />
								</xsl:attribute>
							</xsl:if>
						</input>
					</xsl:if>
					<xsl:if test="type='select'">
						<select name="{$path}">
							<xsl:for-each select="values/value">
								<option value="{.}">
									<xsl:if test="$value != '' and $value=.">
										<xsl:attribute name="selected">selected</xsl:attribute>
									</xsl:if>
									<xsl:if test="count(//View/memory) = 1 and //View/memory/values[name = $path]/value != '' and //View/memory/values[name=$path]/value = .">
										<xsl:attribute name="selected">selected</xsl:attribute>
									</xsl:if>
									<xsl:value-of select="." />
								</option>
							</xsl:for-each>
						</select>
					</xsl:if>
					<xsl:if test="clonable='true'">
						<label>Alias</label> 
						<input type="text" value="{alias}" name="{$alias}">
							<xsl:if test="count(//View/memory) = 1 and //View/memory/values[name = $alias]/value != ''">
								<xsl:attribute name="value">
									<xsl:value-of select="//View/memory/values[name=$alias]/value" />
								</xsl:attribute>
							</xsl:if>
						</input>
					</xsl:if>
					
				</div>
			</xsl:if>
			
			<xsl:if test="clonable = 'true' and (count($xpath) = $pos or $xpath[$pos+1]/tag != tag)">
				<a href="{linkAdd}" title="" class="addField"><xsl:value-of select="concat('add ', label)" /></a>
			</xsl:if>
		</xsl:for-each>	
	</xsl:template>
</xsl:stylesheet>