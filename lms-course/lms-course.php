<?php
/**
 * Plugin Name:       LMS Courses Pull
 * Plugin URI:        https://www.codaemon.com/contact-us/
 * Description:       To pull and show course related data in WP Admin Panel and Frontend. Store the data in wp database table.
 * Version:           1.0.0
 * Author:            Codaemon Softwares LLP
 * Author URI:        https://www.codaemon.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       LMS Pull Course
 * Domain Path:       https://www.codaemon.com/
 */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/** activation and deactivation of the plugin start */
register_activation_hook( __FILE__, 'lms_course_on_activation' );
function lms_course_on_activation() {
	if(! current_user_can('activate_plugin')) return;

	$api_url = '';
	$authorization_url = '';
	$client_id = '';
	$redirect_uri = '';
	$client_secret = '';
	$token_url = '';
	$auth_code = '';
	$access_token = '';
	$site_id = '';

	if(!get_option('api_url')){
		add_option( 'api_url', $api_url, '', 'yes' );
	}
	if(!get_option('authorization_url')){
		add_option( 'authorization_url', $authorization_url, '', 'yes' );
	}
	if(!get_option('client_id')){
		add_option( 'client_id', $client_id, '', 'yes' );
	}
	if(!get_option('redirect_uri')){
		add_option( 'redirect_uri', $redirect_uri, '', 'yes' );
	}
	if(!get_option('client_secret')){
		add_option( 'client_secret', $client_secret, '', 'yes' );
	}
	if(!get_option('token_url')){
		add_option( 'token_url', $token_url, '', 'yes' );
	}
	if(!get_option('auth_code')){
		add_option( 'auth_code', $auth_code, '', 'yes' );
	}
	if(!get_option('access_token')){
		add_option( 'access_token', $access_token, '', 'yes' );
	}
	if(!get_option('site_id')){
		add_option( 'site_id', $site_id, '', 'yes' );
	}
}

register_deactivation_hook( __FILE__, 'lms_course_on_deactivation' );
function lms_course_on_deactivation() {
	if(! current_user_can('activate_plugin')) return;

	delete_option( 'api_url' );
	delete_option( 'authorization_url' );
	delete_option( 'client_id' );
	delete_option( 'redirect_uri' );
	delete_option( 'client_secret' );
	delete_option( 'token_url' );
	delete_option( 'auth_code' );
	delete_option( 'access_token' );
	delete_option( 'site_id' );
}
/** activation and deactivation of the plugin end */

/** adding javascripts of the plugin starts */
add_action( 'admin_enqueue_scripts', 'lms_course_scripts' );
function lms_course_scripts() {
	wp_enqueue_script( 'lms-course', plugins_url('lms-course.js', __FILE__), '','1.1', true );
	wp_enqueue_style( 'lms-course', plugins_url('lms-course.css', __FILE__) );
}
/** adding javascripts of the plugin ends */ 

/** adding admin menu of the plugin start */
add_action("admin_menu", "lms_courses_add_admin_menu");
function lms_courses_add_admin_menu() {
	add_menu_page( 'Pull LMS Courses',
        'Pull LMS Courses',
        'manage_options',
        'lms-course/lms-course-admin.php',
        '',
        'dashicons-book-alt',
        10
    );
	
	add_submenu_page( 'lms-course/lms-course-admin.php',
		'LMS Settings',
        'LMS Settings',
        'manage_options',
        'lms-course/lms-course-settings.php'
	);
}
/** adding admin menu of the plugin end */

add_action( 'init', 'register_api_courses' );
function register_api_courses() {
    if ( ! function_exists( 'register_api_courses' ) ) {
		return false;
	}

	add_action('rest_api_init', function () {
		register_rest_route( 'lms/v1', '/courses',array(
		  'methods'  => 'GET',
		  'callback' => 'get_courses'
		));
	});

	add_action('rest_api_init', function () {
		register_rest_route( 'lms/v1', '/accesstoken',array(
		  'methods'  => 'POST',
		  'callback' => 'get_access_token'
		));
	});

	add_action('rest_api_init', function () {
		register_rest_route( 'lms/v1', '/saveconfiguration',array(
		  'methods'  => 'POST',
		  'callback' => 'save_configuration'
		));
	});
}

/** Creating API for pulling all courses from DialgEdu start */
function get_courses() {
	global $wpdb;

	$access_token = get_option( 'access_token' );
	$api_url = get_option( 'api_url' );
	$site_id = get_option( 'site_id' );

	$header = array("Authorization: Bearer $access_token");

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => "$api_url/api/v1/sites/$site_id/courses",
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_RETURNTRANSFER => true
	));
	$response = curl_exec($curl);
	curl_close($curl);

	$response = json_decode($response, true);

	$count_insert = $count_update = 0; 
	$summery['count_insert'] = $count_insert;
	$summery['count_update'] = $count_update;
	$summery['error'] = '';

	if(!empty($response)){
		if(array_key_exists('courses', $response)){
			$courses = $response['courses'];
			
			if(!empty($courses)){
				foreach($courses as $each_course){
					$cid = $each_course['id'];
					$curlc = curl_init();
					curl_setopt_array($curlc, array(
						CURLOPT_URL => "$api_url/api/v1/sites/$site_id/courses/$cid",
						CURLOPT_HTTPHEADER => $header,
						CURLOPT_RETURNTRANSFER => true
					));
					$responsec = curl_exec($curlc);
					curl_close($curlc);
					$responsec = json_decode($responsec, true);

					/** Check and create category starts */
					$category_id = $responsec['category_id'];
					$category_name = '';
					$category_ids = array();

					if(!empty($category_id)){
						$curlcat = curl_init();
						curl_setopt_array($curlcat, array(
							CURLOPT_URL => "$api_url/api/v1/sites/$site_id/categories/$category_id",
							CURLOPT_HTTPHEADER => $header,
							CURLOPT_RETURNTRANSFER => true
						));
						$responsecat = curl_exec($curlcat);
						curl_close($curlcat);
						$responsecat = json_decode($responsecat, true);

						$category_name = wp_slash( wp_strip_all_tags( $responsecat['name'] ) );

						$cat_name = wp_slash( wp_strip_all_tags( $responsecat['name'] ) );
						$cat_slug = str_replace(' ', '-', strtolower($cat_name)) ;
						$term = term_exists( $cat_name, 'product_cat', null );
						if ( is_array($term) && !empty($term) ) {
							$category_ids[] = $term['term_id'];
						}else{
							$term = wp_insert_term(
								$cat_name,
								'product_cat',
								array(
									'description' => '',
									'slug'        => $cat_slug,
									'parent'      => null,
								)
							);
							$category_ids[] = $term['term_id'];
						}
					}else{
						$category_ids[] = get_option('default_category');

						$category_name = 'Uncategorized';
						$category_id = get_option('default_category');
					}
					/** Check and create category ends */

					$unit_arr = array();
					if(!empty($responsec['units'])){
						$i=0; 
						$units = $responsec['units'];
						foreach($units as $each_course_unit){
							$id = $each_course_unit['id'];
							$title = $each_course_unit['title']; 
							$description = $each_course_unit['description']; 

							$unit_arr[$i]['id'] = $each_course_unit['id'];
							$unit_arr[$i]['name'] = $each_course_unit['title'];
							$unit_arr[$i]['description'] = $each_course_unit['description'];
							$i++;
						}
					}
					
					$user_id = get_current_user_id();

					$postid = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'course_id' AND meta_value = '". $cid. "'" );
					if(!empty($postid)){
						$post_data = get_post( $postid, ARRAY_A );
						$post_meta_price = get_post_meta( $postid, '_price', true );
						$post_meta_course_code = get_post_meta( $postid, 'course_code', true );
						$post_meta_course_cover_image = get_post_meta( $postid, 'course_cover_image', true );
						$post_meta_course_term_title = get_post_meta( $postid, 'course_term_title', true );
						$post_meta_course_program_title = get_post_meta( $postid, 'course_program_title', true );
						$post_meta_course_category_title = get_post_meta( $postid, 'course_category_title', true );
						$post_meta_course_units = get_post_meta( $postid, 'repeatable_fields', true );

						$array_previous_data = array(
													'title' => $post_data['post_title'],
													'content' => $post_data['post_content'],
													'code' => $post_meta_course_code,
													'slug' => $post_data['post_name'],
													'price' => $post_meta_price,
													'cover_image' => $post_meta_course_cover_image,
													'term_title' => $post_meta_course_term_title,
													'program_title' => $post_meta_course_program_title,
													'category_title' => $post_meta_course_category_title,
													'units' => $post_meta_course_units
												);
						$array_current_data = array(
													'title' => wp_slash( wp_strip_all_tags( $each_course['title'] ) ),
													'content' => wp_slash( wp_strip_all_tags( $each_course['description'] ) ),
													'code' => wp_slash( wp_strip_all_tags( $each_course['code'] ) ),
													'slug' => wp_slash( wp_strip_all_tags( $each_course['slug'] ) ),
													'price' => wp_slash( wp_strip_all_tags( $responsec['price'] ) ),
													'cover_image' => wp_slash( wp_strip_all_tags( $each_course['cover_image'] ) ),
													'term_title' => wp_slash( wp_strip_all_tags( $each_course['term_title'] ) ),
													'program_title' => wp_slash( wp_strip_all_tags( $each_course['program_title'] ) ),
													'category_title' => wp_slash( wp_strip_all_tags( $category_name ) ),
													'units' => $unit_arr
												);

						$if_post_updated = array_diff($array_previous_data, $array_current_data);

						if(!empty($if_post_updated)){
							$my_post = array(
								'ID'           	=> $postid,
								'post_title'    => wp_slash( wp_strip_all_tags( $each_course['title'] ) ),
								'post_content'  => wp_slash( wp_strip_all_tags( $each_course['description'] ) ),
								'post_status'   => 'publish',
								'post_author'   => $user_id,
								'post_type'		=> 'product',
								'post_name'		=> wp_slash( wp_strip_all_tags( $each_course['slug'] ) ),
								'meta_input'   => array(
									'_regular_price' 		=> wp_slash( wp_strip_all_tags( $responsec['price'] ) ),
									'_price'				=> wp_slash( wp_strip_all_tags( $responsec['price'] ) ),
									'course_id'				=> $each_course['id'],
									'course_code'			=> wp_slash( wp_strip_all_tags( $each_course['code'] ) ),
									'course_slug'			=> wp_slash( wp_strip_all_tags( $each_course['slug'] ) ),
									'course_cover_image'	=> wp_slash( wp_strip_all_tags( $each_course['cover_image'] ) ),
									'course_term_title'		=> wp_slash( wp_strip_all_tags( $each_course['term_title'] ) ),
									'course_program_title'	=> wp_slash( wp_strip_all_tags( $each_course['program_title'] ) ),
									'course_category_title'	=> wp_slash( wp_strip_all_tags( $category_name ) ),
									'course_term_id'		=> wp_slash( wp_strip_all_tags( $each_course['term_id'] ) ),
									'course_program_id'		=> wp_slash( wp_strip_all_tags( $each_course['program_id'] ) ),
									'course_category_id'	=> wp_slash( wp_strip_all_tags( $category_id ) ),
									'repeatable_fields'		=> $unit_arr,
								),
							);
							
							// Update the post into the database
							wp_update_post( $my_post );
		
							wp_set_post_terms( $postid, $category_ids, 'product_cat' );
						
							$image_url = wp_slash( wp_strip_all_tags( $each_course['cover_image'] ) );
							save_featured_image($image_url, $postid);
		
							$count_update++;
						}
					}else{
						$my_post = array(
							'post_title'    => wp_slash( wp_strip_all_tags( $each_course['title'] ) ),
							'post_content'  => wp_slash( wp_strip_all_tags( $each_course['description'] ) ),
							'post_status'   => 'publish',
							'post_author'   => $user_id,
							'post_type'		=> 'product',
							'post_name'		=> wp_slash( wp_strip_all_tags( $each_course['slug'] ) ),
							'meta_input'   => array(
								'_regular_price' 		=> wp_slash( wp_strip_all_tags( $responsec['price'] ) ),
								'_price'				=> wp_slash( wp_strip_all_tags( $responsec['price'] ) ),
								'course_id'				=> $each_course['id'],
								'course_code'			=> wp_slash( wp_strip_all_tags( $each_course['code'] ) ),
								'course_slug'			=> wp_slash( wp_strip_all_tags( $each_course['slug'] ) ),
								'course_cover_image'	=> wp_slash( wp_strip_all_tags( $each_course['cover_image'] ) ),
								'course_term_title'		=> wp_slash( wp_strip_all_tags( $each_course['term_title'] ) ),
								'course_program_title'	=> wp_slash( wp_strip_all_tags( $each_course['program_title'] ) ),
								'course_category_title'	=> wp_slash( wp_strip_all_tags( $category_name ) ),
								'course_term_id'		=> wp_slash( wp_strip_all_tags( $each_course['term_id'] ) ),
								'course_program_id'		=> wp_slash( wp_strip_all_tags( $each_course['program_id'] ) ),
								'course_category_id'	=> wp_slash( wp_strip_all_tags( $category_id ) ),
								'repeatable_fields'		=> $unit_arr,
								),
						);
							
						// Insert the post into the database
						$postid = wp_insert_post( $my_post );

						wp_set_post_terms( $postid, $category_ids, 'product_cat' );
					
						$image_url = wp_slash( wp_strip_all_tags( $each_course['cover_image'] ) );
						save_featured_image($image_url, $postid);

						$count_insert++;
					}
				}
			}else{
				$summery['error'] = 'There are no records in LMS';
			}
			
			$summery['count_insert'] = $count_insert;
			$summery['count_update'] = $count_update;
		}else{
			$summery['error'] = 'There are no records in LMS';
		}
	}else{
		$summery['error'] = 'There are no records in LMS';
	}

	return $summery;
}

function save_featured_image($image_url = null, $post_id = null){
	// Add Featured Image to Post
	if(!empty($image_url)){
		$image_name 		= basename($image_url);
		$upload_dir       	= wp_upload_dir(); // Set upload folder
		$image_data       	= file_get_contents($image_url); // Get image data
		$unique_file_name 	= wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
		$filename         	= basename( $unique_file_name ); // Create image file name

		// Check folder permission and define file location
		if( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		// Create the image  file on the server
		file_put_contents( $file, $image_data );

		// Check image file type
		$wp_filetype = wp_check_filetype( $filename, null );

		// Set attachment data
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		// Create the attachment
		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );

		// Include image.php
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id, $attach_data );

		// And finally assign featured image to post
		set_post_thumbnail( $post_id, $attach_id );
	}
}
/** Creating API for pulling all courses from DialgEdu end */

/** Creating API for creating new access token for DialgEdu start */
function get_access_token(){
	$client_id = stripslashes( strip_tags( $_POST['client_id'] ) );
	$client_secret = stripslashes( strip_tags( $_POST['secret_id'] ) );
	$token_url = stripslashes( strip_tags( $_POST['token_url'] ) );
	$redirect_uri = stripslashes( strip_tags( $_POST['redirect_url'] ) );
	$code = stripslashes( strip_tags( $_POST['auth_code'] ) );

	$curl = curl_init( $token_url );
    curl_setopt( $curl, CURLOPT_POST, true );
    curl_setopt( $curl, CURLOPT_POSTFIELDS, array(
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'client_secret' => $client_secret,
        'code' => $code,
        'grant_type' => 'authorization_code'
    ) );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
    $auth = curl_exec( $curl );
    $secret = json_decode($auth);
	
	$return = array();
	if(!empty($secret) && isset($secret->error)){
		$return['error'] = $secret->error_description;
		$return['access_token'] = $return['site_id'] = '';
	}
	if(!empty($secret) && isset($secret->access_token)){
		$return['error'] = '';
		$return['access_token'] = $secret->access_token;

		$access_token = $secret->access_token;
		$api_url = stripslashes( strip_tags( $_POST['api_url'] ) );;

		$header = array("Authorization: Bearer $access_token");

		$curls = curl_init();
		curl_setopt_array($curls, array(
			CURLOPT_URL => "$api_url/api/v1/sites",
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($curls);
		curl_close($curls);
		$site = json_decode($response, true);
		$return['site_id'] = $site['sites'][0]['id'];
	}
	return $return;
}
/** Creating API for creating new access token for DialgEdu end */

/** Creating API for saving all configuration to access DialgEdu API start */
function save_configuration(){
	$api_url = wp_slash( wp_strip_all_tags( $_POST['api_url'] ) );
	$authorization_url = wp_slash( wp_strip_all_tags( $_POST['authorization_url'] ) );
	$client_id = wp_slash( wp_strip_all_tags( $_POST['client_id'] ) );
	$redirect_uri = wp_slash( wp_strip_all_tags( $_POST['redirect_url'] ) );
	$client_secret = wp_slash( wp_strip_all_tags( $_POST['secret_id'] ) );
	$token_url = wp_slash( wp_strip_all_tags( $_POST['token_url'] ) );
	$auth_code = wp_slash( wp_strip_all_tags( $_POST['auth_code'] ) );
	$access_token = wp_slash( wp_strip_all_tags( $_POST['access_token'] ) );
	$site_id = wp_slash( wp_strip_all_tags( $_POST['site_id'] ) );

	update_option( 'api_url', $api_url );
	update_option( 'authorization_url', $authorization_url );
	update_option( 'client_id', $client_id );
	update_option( 'redirect_uri', $redirect_uri );
	update_option( 'client_secret', $client_secret );
	update_option( 'token_url', $token_url );
	update_option( 'auth_code', $auth_code );
	update_option( 'access_token', $access_token );
	update_option( 'site_id', $site_id );

	return 'success';
}
/** Creating API for saving all configuration to access DialgEdu API end */

/** Adding meta boxes start */  
add_action('admin_init', 'lms_course_add_meta_boxes', 1);
function lms_course_add_meta_boxes() {
	add_meta_box( 'repeatable-fields', 'Course Units', 'lms_course_repeatable_meta_box_display', 'product', 'normal', 'default');
	add_meta_box( 'course-code', 'Code', 'lms_course_code_meta_box_display', 'product', 'side', 'default');
	add_meta_box( 'course-slug', 'Permalink', 'lms_course_slug_meta_box_display', 'product', 'side', 'default');
	add_meta_box( 'course-image', 'Cover Image', 'lms_course_image_meta_box_display', 'product', 'side', 'default');
}
/** Adding meta boxes end */ 

/** Add and save meta box for course image start */
function lms_course_image_meta_box_display() {
	global $post;

	$course_cover_image = get_post_meta($post->ID, 'course_cover_image', true);

	wp_nonce_field( 'lms_course_image_meta_box_nonce', 'lms_course_image_meta_box_nonce' );

	if ( $course_cover_image ) {
	?>
	<img height="150" src="<?php echo $course_cover_image; ?>" alt="Cover Image" />
	<input type="text" class="widefat" name="course_cover_image" value="<?php if($course_cover_image != '') echo esc_attr( $course_cover_image ); ?>" />
	<?php
	} else {
	// show a blank one
	?>
	<input type="text" class="widefat" name="course_cover_image" />
	<?php 
	}
}

add_action('save_post', 'lms_course_image_meta_box_save');
function lms_course_image_meta_box_save($post_id) {
	if ( ! isset( $_POST['lms_course_image_meta_box_nonce'] ) ||
	! wp_verify_nonce( $_POST['lms_course_image_meta_box_nonce'], 'lms_course_image_meta_box_nonce' ) )
		return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;
	
	$course_cover_image = $_POST['course_cover_image'];
	$old_course_cover_image = get_post_meta($post_id, 'course_cover_image', true);
	$new_course_cover_image = '';
	if ( $course_cover_image != '' ) {
		$new_course_cover_image = stripslashes( strip_tags( $course_cover_image ) );
	}

	if ( !empty( $new_course_cover_image ) && $new_course_cover_image != $old_course_cover_image )
		update_post_meta( $post_id, 'course_cover_image', $new_course_cover_image );
	elseif ( empty($new_course_cover_image) && $old_course_cover_image )
		delete_post_meta( $post_id, 'course_cover_image', $old_course_cover_image );
}
/** Add meta box for course image end */

/** Add and save meta box for course slug start */
function lms_course_slug_meta_box_display() {
	global $post;

	$course_slug = get_post_meta($post->ID, 'course_slug', true);

	wp_nonce_field( 'lms_course_slug_meta_box_nonce', 'lms_course_slug_meta_box_nonce' );

	if ( $course_slug ) {
	?>
	<input type="text" class="widefat" name="course_slug" value="<?php if($course_slug != '') echo esc_attr( $course_slug ); ?>" />
	<?php
	} else {
	// show a blank one
	?>
	<input type="text" class="widefat" name="course_slug" />
	<?php 
	}
}

add_action('save_post', 'lms_course_slug_meta_box_save');
function lms_course_slug_meta_box_save($post_id) {
	if ( ! isset( $_POST['lms_course_slug_meta_box_nonce'] ) ||
	! wp_verify_nonce( $_POST['lms_course_slug_meta_box_nonce'], 'lms_course_slug_meta_box_nonce' ) )
		return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;
	
	$course_slug = $_POST['course_slug'];
	$old_course_slug = get_post_meta($post_id, 'course_slug', true);
	$new_course_slug = '';
	if ( $course_slug != '' ) {
		$new_course_slug = stripslashes( strip_tags( $course_slug ) );
	}

	if ( !empty( $new_course_slug ) && $new_course_slug != $old_course_slug )
		update_post_meta( $post_id, 'course_slug', $new_course_slug );
	elseif ( empty($new_course_slug) && $old_course_slug )
		delete_post_meta( $post_id, 'course_slug', $old_course_slug );
}
/** Add and save meta box for course slug end */

/** Add and save meta box for course code start */
function lms_course_code_meta_box_display() {
	global $post;

	$course_code = get_post_meta($post->ID, 'course_code', true);

	wp_nonce_field( 'lms_course_code_meta_box_nonce', 'lms_course_code_meta_box_nonce' );

	if ( $course_code ) {
	?>
	<input type="text" class="widefat" name="course_code" value="<?php if($course_code != '') echo esc_attr( $course_code ); ?>" />
	<?php
	} else {
	// show a blank one
	?>
	<input type="text" class="widefat" name="course_code" />
	<?php 
	}
}

add_action('save_post', 'lms_course_code_meta_box_save');
function lms_course_code_meta_box_save($post_id) {
	if ( ! isset( $_POST['lms_course_code_meta_box_nonce'] ) ||
	! wp_verify_nonce( $_POST['lms_course_code_meta_box_nonce'], 'lms_course_code_meta_box_nonce' ) )
		return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;
	
	$course_code = $_POST['course_code'];
	$old_course_code = get_post_meta($post_id, 'course_code', true);
	$new_course_code = '';
	if ( $course_code != '' ) {
		$new_course_code = stripslashes( strip_tags( $course_code ) );
	}

	if ( !empty( $new_course_code ) && $new_course_code != $old )
		update_post_meta( $post_id, 'course_code', $new_course_code );
	elseif ( empty($new_course_code) && $old_course_code )
		delete_post_meta( $post_id, 'course_code', $old_course_code );
}
/** Add and save meta box for course code end */

/** Add and save meta box for course units start */
function lms_course_repeatable_meta_box_display() {
	global $post;

	$repeatable_fields = get_post_meta($post->ID, 'repeatable_fields', true);

	wp_nonce_field( 'lms_course_repeatable_meta_box_nonce', 'lms_course_repeatable_meta_box_nonce' );
	?>
	<script type="text/javascript">
	jQuery(document).ready(function( $ ){
		$( '#add-row' ).on('click', function() {
			var row = $( '.empty-row.screen-reader-text' ).clone(true);
			row.removeClass( 'empty-row screen-reader-text' );
			row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
			return false;
		});
  	
		$( '.remove-row' ).on('click', function() {
			$(this).parents('tr').remove();
			return false;
		});
	});
	</script>
  
	<table id="repeatable-fieldset-one" width="100%">
	<thead>
		<tr>
			<th width="40%">Name</th>
			<th width="50%">Description</th>
			<th width="8%"></th>
		</tr>
	</thead>
	<tbody>
	<?php
	
	if ( $repeatable_fields ) :
	
	foreach ( $repeatable_fields as $field ) {
	?>
	<tr>
		<td><input type="text" class="widefat" name="name[]" value="<?php if($field['name'] != '') echo esc_attr( $field['name'] ); ?>" /></td>
	
		<td><input type="text" class="widefat" name="description[]" value="<?php if ($field['description'] != '') echo esc_attr( $field['description'] ); else echo ''; ?>" /></td>
	
		<td><a class="button remove-row" href="#">Remove</a></td>
	</tr>
	<?php
	}
	else :
	// show a blank one
	?>
	<tr>
		<td><input type="text" class="widefat" name="name[]" /></td>
	
		<td><input type="text" class="widefat" name="description[]" value="" /></td>
	
		<td><a class="button remove-row" href="#">Remove</a></td>
	</tr>
	<?php endif; ?>
	
	<!-- empty hidden one for jQuery -->
	<tr class="empty-row screen-reader-text">
		<td><input type="text" class="widefat" name="name[]" /></td>
	
		<td><input type="text" class="widefat" name="description[]" value="" /></td>
		  
		<td><a class="button remove-row" href="#">Remove</a></td>
	</tr>
	</tbody>
	</table>
	
	<p><a id="add-row" class="button" href="#">Add another</a></p>
	<?php
}

add_action('save_post', 'lms_course_repeatable_meta_box_save');
function lms_course_repeatable_meta_box_save($post_id) {
	if ( ! isset( $_POST['lms_course_repeatable_meta_box_nonce'] ) ||
	! wp_verify_nonce( $_POST['lms_course_repeatable_meta_box_nonce'], 'lms_course_repeatable_meta_box_nonce' ) )
		return;
	
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;
	
	if (!current_user_can('edit_post', $post_id))
		return;
	
	$old = get_post_meta($post_id, 'repeatable_fields', true);
	$new = array();
	
	$names = $_POST['name'];
	$descriptions = $_POST['description'];
	
	$count = count( $names );
	
	for ( $i = 0; $i < $count; $i++ ) {
		if ( $names[$i] != '' ) :
			$new[$i]['name'] = stripslashes( strip_tags( $names[$i] ) );
		endif;
		if ( $descriptions[$i] != '' ) :
			$new[$i]['description'] = stripslashes( $descriptions[$i] ); // and however you want to sanitize
		endif;
	}

	if ( !empty( $new ) && $new != $old )
		update_post_meta( $post_id, 'repeatable_fields', $new );
	elseif ( empty($new) && $old )
		delete_post_meta( $post_id, 'repeatable_fields', $old );
}
/** Add and save meta box for course units end */
?>