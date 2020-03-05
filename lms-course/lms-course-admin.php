<div class="wrap mt-2">
	<div class="container-fluid">
		<div class="row">
			<h2>Pull and save all courses from LMS</h2>
		</div>
		<?php 
		$access_token = get_option( 'access_token' );
		$site_id = get_option( 'site_id' );
		if($access_token != '' && $site_id != ''){
		?>
		<div class="row padding-top-15 font-size-15">
			<table id="lms-pull-course-info" width="100%">
				<tr><td align="left" colspan="2">** Click on the button to get all LMS course related data and save as products.</td></tr>
				<tr><td align="left" colspan="2">** Check on the 'Products' menu section for all available courses.</td></tr>
				<tr><td align="left" colspan="2"></td></tr>
			</table>
		</div>
		<div class="row padding-top-15">
			<table id="lms-pull-course" width="100%">
				<tr><td align="left" colspan="2"><button class="wp-core-ui button-primary" id="pull" data-site-url="<?php echo get_site_url(); ?>">Pull All Courses</button></td></tr>
				<tr><td align="left" colspan="2"></td></tr>
				<tr><td align="left" colspan="2"></td></tr>
			</table>
		</div>
		<div class="row padding-top-15 font-size-15">
			<table id="lms-pull-course-result" width="100%">
				<tr><th align="left" colspan="2">Summary</th></tr>
				<tr><td align="left" colspan="2">New courses added : <span id="inserted"></span></td></tr>
				<tr><td align="left" colspan="2">Existing courses updated : <span id="updated"></span></td></tr>
			</table>
		</div>
		<?php } else { ?>
		<div class="row padding-top-15">
			<table id="lms-pull-course" width="100%">
				<tr><td align="left" colspan="2"><em class="warning">Please complete the API configuration from <a href="<?php echo get_site_url().'/wp-admin/admin.php?page=lms-course/lms-course-settings.php'; ?>">settings page</a> to start pulling courses</em></td></tr>
			</table>
		</div>
		<?php } ?>
	</div>
</div>
<div id="loader"><img src="<?php echo get_site_url(); ?>/wp-content/plugins/lms-course/loader.gif" alt="Loading.." /></div>