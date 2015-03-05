<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:template name="Authentication">
	<script type="text/javascript">
		var authForm = null;
		window.addEvent('domready', function(){
			authForm = new Form('authForm');
			$('submit').addEvent('click', function(e) {
				authForm.submit();
				e.stop();
			});
		});
	</script>
	<div id="header">
			<div id="logo"></div>
			<div id="baseline"></div>
		</div>
		<div id="main">
			<div id="rightSide">
				<div id="container">
					<div id="breadcrumbs">Directories Rigths <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						<span class="focus">Authentication</span> <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Global Settings <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						International <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						DataBase <img src="{concat($sls_url_img_core_buttons, 'breadcrumbs.png')}" /> 
						Mails</div>
					<h1>Installation</h1>
					<h2>Authentication</h2>
					<fieldset class="sls_init_form">
						<legend>Authentication Informations</legend>
						<xsl:if test="count(//View/errors/error) &gt; 0">
							<ul style="display:block;width:70%;font-size:0.8em;margin:0 auto;margin-bottom:20px;text-align:center;color:red;font-weight:900">
								<xsl:for-each select="//View/errors/error">
									<li><xsl:value-of select="." /></li>
								</xsl:for-each>
							</ul>
						</xsl:if>
						<form method="post" id="authForm" enctype="multipart/form-data" action="{//Statics/Sls/Configs/action/links/link[name='AUTHENTICATION']/href}">
							<label for="login">Administrator Username:</label><input type="text" name="auth_login" id="login" />
							<label for="pass1">Password:</label><input type="password" name="auth_pass1" id="pass1" />
							<label for="pass2">Confirm it:</label><input type="password" name="auth_pass2" id="pass2" />
							<div id="buttons_panel">
								<input type="hidden" name="authentication_reload" value="true" />
								<a id="submit" href="{//Statics/Sls/Configs/action/links/link[name='AUTHENTICATION']/href}" title="Next" class="next">Next</a>
							</div>
						</form>
					</fieldset>
				</div>	
				
			</div>
			
		</div>
		
	</xsl:template>
</xsl:stylesheet>