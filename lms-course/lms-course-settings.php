<div class="wrap mt-2">
	<div class="container-fluid">
		<div class="row">
			<h2>Configuration Settings For LMS API</h2>
		</div>
		<div class="row padding-top-15 font-size-15">
			<table id="lms-settings-info" width="100%">
				<tr><td align="left" colspan="2">** Please fill up the fields <em>API URL</em>, <em>Authorization URL</em>, <em>Client ID</em>, <em>Callback URI</em> and click on the 'Generate Authorization Key' link to generate the authorization code. Use authorization code in the next step to generate the access token for API access.</td></tr>
			</table>
		</div>
		<div class="row padding-top-15">
			<h5>Step 1</h5>
		</div>
		<div class="row padding-top-15 font-size-12">
			<table id="lms-settings-1" width="100%">
				<tr><th width="20%" align="left"><label for="api_url">API URL <span>*</span></label></th><td><input name="api_url" id="api_url" class="input-text-width" value="<?php echo get_option( 'api_url' );?>" /></td></tr>
				<tr><th width="20%" align="left"><label for="authorization_url">Authorization URL <span>*</span></label></th><td><input name="authorization_url" id="authorization_url" class="input-text-width" value="<?php echo get_option( 'authorization_url' );?>" /></td></tr>
				<tr><th width="20%" align="left"><label for="client_id">Client ID <span>*</span></label></th><td><input name="client_id" id="client_id" class="input-text-width" value="<?php echo get_option( 'client_id' );?>" /></td></tr>
				<tr><th width="20%" align="left"><label for="redirect_uri">Callback URI <span>*</span></label></th><td><input name="redirect_url" id="redirect_url" class="input-text-width" value="<?php echo get_option( 'redirect_uri' );?>" /></td></tr>
				<tr><td></td><td align="left"><a href="#" id="get_auth_code">Generate Authorization Key</a></td></tr>
			</table>
		</div>
		<div class="row padding-top-15 font-size-15">
			<table id="lms-settings-info" width="100%">
				<tr><td align="left" colspan="2">** Please fill up the fields <em>Secret Key</em>, <em>Token URL</em> and place the authorization code in <em>Authorization Code</em> field to generate the access token for API access.</td></tr>
			</table>
		</div>
		<div class="row padding-top-15">
			<h5>Step 2</h5>
		</div>
		<div class="row padding-top-15 font-size-12">
			<table id="lms-settings-2" width="100%">	
				<tr><th width="20%" align="left"><label for="secret_id">Secret Key <span>*</span></label></th><td><input name="secret_id" id="secret_id" class="input-text-width" value="<?php echo get_option( 'client_secret' );?>" /></td></tr>
				<tr><th width="20%" align="left"><label for="token_url">Token URL <span>*</span></label></th><td><input name="token_url" id="token_url" class="input-text-width" value="<?php echo get_option( 'token_url' );?>" /></td></tr>
				<tr><th width="20%" align="left"><label for="auth_code">Authorization Code <span>*</span></label></th><td><input name="auth_code" id="auth_code" class="input-text-width" value="<?php echo get_option( 'auth_code' );?>" /></td></tr>
				<tr><td></td><td><a href="#" id="get_access_token">Generate Access Token</button></td></tr>
			</table>
		</div>
		<div class="row padding-top-15 font-size-15">
			<table id="lms-settings-info" width="100%">
				<tr><td align="left" colspan="2">** Save the generated <em>Access Token</em>, <em>Site ID</em> along with other configuration settings parameter.</td></tr>
			</table>
		</div>
		<div class="row padding-top-15 font-size-15">
			<h5>Step 3</h5>
		</div>
		<div class="row padding-top-15 font-size-12">
			<table id="lms-settings-3" width="100%">	
				<tr><th width="20%" align="left"><label for="access_token">Access Token</label></th><td><input name="access_token" id="access_token" class="input-text-width" value="<?php echo get_option( 'access_token' );?>" /></td></tr>
				<tr><th width="20%" align="left"><label for="site_id">Site ID</label></th><td><input name="site_id" id="site_id" class="input-text-width" value="<?php echo get_option( 'site_id' );?>" /></td></tr>
			</table>
		</div>
		<div class="row padding-top-15">
			<table id="lms-settings-4" width="100%">
				<tr><td width="20%"></td><td align="left"><button class="wp-core-ui button-primary" id="save_config_data" data-site-url="<?php echo get_site_url(); ?>">Save Configuration Settings</button></td></tr>
			</table>
		</div>
	</div>
</div>
<div id="loader"><img src="<?php echo get_site_url(); ?>/wp-content/plugins/lms-course/loader.gif" alt="Loading.." /></div>
