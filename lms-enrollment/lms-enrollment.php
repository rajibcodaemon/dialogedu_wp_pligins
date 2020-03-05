<?php
/**
 * Plugin Name:       LMS User Enrollment
 * Plugin URI:        https://www.codaemon.com/contact-us/
 * Description:       This is simple plugin to pull users enrollment details from LMS api. The plugin also push the new user registration details to LMS and user's enrollment details to LMS api. It will also generate a SSO link to access LMS site.
 * Version:           1.0.0
 * Author:            Codaemon Softwares LLP
 * Author URI:        https://www.codaemon.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       LMS pull enrollments and enrolled users details.
 * Domain Path:       https://www.codaemon.com/
 */


define("PLUGIN_DIR_PATH",plugin_dir_path( __FILE__ ));
define("PLUGIN_URL",plugins_url());

 // Activate plugin
register_activation_hook( __FILE__, 'lms_enrollment_on_activation' );
function lms_enrollment_on_activation() {
    if(! current_user_can('activate_plugin')) return;
    global $wpdb;
    $table_name_enrol = $wpdb->prefix . 'lms_enrolments';
    $sqlEnrol = "CREATE TABLE $table_name_enrol (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `enrolment_id` int(11) NOT NULL,
                `course_id` int(11) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `user_name` varchar(255) DEFAULT NULL,
                `total_score` int(11) DEFAULT NULL,
                `user_active` varchar(255) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                `status` varchar(255) DEFAULT NULL,
                `is_certificated` varchar(255) DEFAULT NULL,
                `printed_certificate` varchar(255) DEFAULT NULL,
                `certificate_earned_at` timestamp NULL DEFAULT NULL,
                `current_progress` int(11) DEFAULT NULL,
                `current_state` int(11) DEFAULT NULL,
                `current_state_updated_at` timestamp NULL DEFAULT NULL,
                `type` varchar(255) DEFAULT NULL,
                `started_at` timestamp NULL DEFAULT NULL,
                `finished_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (id)
            );";

    $table_name_user = $wpdb->prefix . 'lms_users';
    $sqlUser = "CREATE TABLE $table_name_user (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` varchar(255) DEFAULT NULL,
                    `external_id` varchar(255) DEFAULT NULL,
                    `screen_name` varchar(255) DEFAULT NULL,
                    `email` varchar(255) DEFAULT NULL,
                    `first_name` varchar(255) DEFAULT NULL,
                    `last_name` varchar(255) DEFAULT NULL,
                    `sex` varchar(255) DEFAULT NULL,
                    `active` varchar(255) DEFAULT NULL,
                    `created_at` varchar(255) DEFAULT NULL,
                    `updated_at` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (id)
                );";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sqlEnrol ); dbDelta( $sqlUser );
}

// Deactivate plugin
register_deactivation_hook( __FILE__, 'lms_enrollment_on_deactivation' );
function lms_enrollment_on_deactivation() {
    if(! current_user_can('activate_plugin')) return;
    // Delete table related to plugin
     global $wpdb;
     
     $table_name_enrol = $wpdb->prefix . 'lms_enrolments';
     $sql_enrol = "DROP TABLE IF EXISTS $table_name_enrol";
     $wpdb->query($sql_enrol);

     $table_name_user = $wpdb->prefix . 'lms_users';
     $sql_user = "DROP TABLE IF EXISTS $table_name_user";
     $wpdb->query($sql_user);
}

// Add Plugin Admin Menu
add_action("admin_menu", "lms_enrollment_topmenu");
function lms_enrollment_topmenu() {
	add_menu_page( 'LMS Enrollments',
        'LMS Enrollments',
        'manage_options',
        'lms-enrollment',
        'lms_enrollment',
        'dashicons-welcome-learn-more',
        10
    );
}

// LMS Enrolment show
function lms_enrollment() {
    global $wpdb;
    $access_token = get_option('access_token');
    $api_url = get_option('api_url');
    $site_id = get_option('site_id');
    if($access_token !='' && $api_url !=''  && $site_id !='') {
        include_once PLUGIN_DIR_PATH."views/lms-enrollment-view.php";
    } else {
        include_once PLUGIN_DIR_PATH."views/error-setting.php";
    }
}

function lms_courses_view() {
    echo "<h1>LMS Course Dashboard<h1>";
}

// Adding Css/Js for the plugin
add_action( 'admin_enqueue_scripts', 'lms_enrollment_scripts' );
function lms_enrollment_scripts() {
    wp_enqueue_script( 'jquery', plugins_url('assets/js/jquery.min.js', __FILE__), array ( 'jquery' ), '3.3.1', true);
    wp_enqueue_script( 'datatable', plugins_url('assets/js/jquery.dataTables.min.js', __FILE__), array ( 'jquery' ), '1.10.18', true);
    wp_enqueue_script( 'datatable-bootstrap', plugins_url('assets/js/dataTables.bootstrap4.min.js', __FILE__), array ( 'jquery' ), '1.10.18', true);
    wp_enqueue_style( 'datatable-css', plugins_url('assets/css/dataTables.bootstrap4.min.css', __FILE__), array(), '1.10.18', 'all' );
    wp_enqueue_style( 'datatable-bootscss', plugins_url('assets/css/bootstrap.min.css', __FILE__), array(), '4.1.3', 'all' );
    wp_enqueue_script( 'lms-enrollment', plugins_url('assets/js/lms-enrolment.js', __FILE__), '','1.1', true );
    wp_enqueue_style( 'lms-enrollment', plugins_url('assets/css/lms-enrolment.css', __FILE__) );
}

// Remove Admin Notice From Top
add_action('in_admin_header', function () {
    remove_all_actions('admin_notices');
}, 1000);

// Enrolment to LMS End
//add_action('user_register','enroll_lms_user');
add_action('woocommerce_thankyou', 'enroll_lms_user');
function enroll_lms_user() {
    // For logged in users only
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id(); // The current user ID
    }
    // Get the WC_Customer instance Object for the current user
    $customer = new WC_Customer( $user_id );
    // Get the last WC_Order Object instance from current customer
    $last_order = $customer->get_last_order();
    $order_id     = $last_order->get_id(); // Get the order id
    $order_data   = $last_order->get_data(); // Get the order unprotected data in an array
    $order_status = $last_order->get_status(); // Get the order status
    
    $args = array( 
        'status' => array( 'active', 'complimentary', 'pending' ),
    );  
    $memberships_info = wc_memberships_get_user_active_memberships($user_id, $args); // adding the args bit to get all memberships

    $membership_id = array();
    foreach ($memberships_info as $membership) {
        $membership_data = $membership->plan;
        $membership_id[] = $membership_data->id;
    }

    $user_info_pre = get_userdata($user_id);
    $user_email = $user_info_pre->user_email;
    $checkIsRegister = check_lms_user_by_emailid($user_email);

    if(!empty($membership_id)){
        foreach($membership_id as $mid){
            if(!empty($checkIsRegister['users'])) {
                $lms_user_id =   $checkIsRegister['users'][0]['id'];
                $product_ids = get_member_restrict_product($mid);
                foreach($product_ids as $product_id){
                    if ( metadata_exists( 'post', $product_id, 'course_id' ) ) {
                            $course_id = get_post_meta($product_id, 'course_id'); 
                            enrol_plan_course($course_id[0], $lms_user_id); 
                    }
                }
            } else {
                $userRegisterStatus = register_user_lms($user_id); 
                if(!empty($userRegisterStatus)) {
                // Assign role to user future reference
                // $role_status = assign_lms_user_role($userRegisterStatus->id);

                // Assign to site
                $siteuser_response = assign_user_tosite($userRegisterStatus->id);
                if(!empty($siteuser_response)){
                    $product_ids = get_member_restrict_product($mid);
                    if(!empty($product_ids)){
                        foreach($product_ids as $product_id){
                            if ( metadata_exists( 'post', $product_id, 'course_id' ) ) {
                                $course_id = get_post_meta($product_id, 'course_id'); 
                                enrol_plan_course($course_id[0], $userRegisterStatus->id); 
                            }
                        }
                    } 
                }
                }else {
                    // Failed to register
                }  
            }
        }
    }
}

// Function to enroll a user to a course
function enrol_plan_course($course_id = null,$lms_user_id=null){
    global $wpdb;
    $access_token = get_option( 'access_token' );
    $api_url = get_option( 'api_url' );
    $site_id = get_option( 'site_id' );
    $header = array("Authorization: Bearer $access_token");
    $enrol_user_course = array(
        'user_id' => $lms_user_id
    );  
    $enrol_user_string = json_encode($enrol_user_course);                                                                                   
                                                                                                                  
    $ch = curl_init($api_url.'/api/v1/sites/'.$site_id.'\/courses/'.$course_id.'/enrollments');                                                                      
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $enrol_user_string);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(      
        "Authorization: Bearer $access_token",                                                                    
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($enrol_user_string))                                                                       
    );
    $enrol_user_output = curl_exec($ch);
    curl_close ($ch);
    $enrol_user_info = json_decode($enrol_user_output); 
    return $enrol_user_info;
}

// Assign user to LMS site
function assign_user_tosite($lms_user_id = null){
    global $wpdb;
    $access_token = get_option( 'access_token' );
    $api_url = get_option( 'api_url' );
    $site_id = get_option( 'site_id' );
    $header = array("Authorization: Bearer $access_token");
    $site_user_data = array(
        'site_id' => $site_id,
        'user_id' => $lms_user_id
    );  
    $site_user_string = json_encode($site_user_data);                                                                                                                                                                                               
    $ch = curl_init($api_url.'/api/v1/sites/'.$site_id.'/users');                                                                      
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $site_user_string);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(      
        "Authorization: Bearer $access_token",                                                                    
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($site_user_string))                                                                       
    );
    $site_user_output = curl_exec($ch);
    curl_close ($ch);
    $site_user_info = json_decode($site_user_output); 
    return $site_user_info;
}


// Get membership plan restricted product
function get_member_restrict_product($plan_id = null){
    $plans = wc_memberships_get_membership_plans();
        $restricted_prod_ids = array();
        if(!empty($plans)){ 
            foreach($plans as $plan){
                if($plan->id == $plan_id){
                    $restricted_ids = $plan->get_product_restriction_rules();
                    if(!empty($restricted_ids)){
                        foreach ( $restricted_ids as $rule ) {
                            if($rule->get_content_type_name() == 'product'){
                                $restricted_prod_ids = $rule->get_object_ids();
                            }
                        }
                    }
                }
            }
        }
    return $restricted_prod_ids;
}

// LMS user role assignment
function assign_lms_user_role($lms_user_id = null){
    global $wpdb;
    $access_token = get_option( 'access_token' );
    $api_url = get_option( 'api_url' );
    $site_id = get_option( 'site_id' );
    $header = array("Authorization: Bearer $access_token");
    $role_data = array(
        'user_id' => $lms_user_id,
        'role_id' => 0
    );                                                                    
    $role_string = json_encode($role_data);                                                                                                                                                                                                      
    $ch = curl_init($api_url.'/api/v1/users/'.$lms_user_id.'/assign_role');                                                                      
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $role_string);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(      
        "Authorization: Bearer $access_token",                                                                    
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($role_string))                                                                       
    );
    $role_output = curl_exec($ch);
    curl_close ($ch);
    $role_info = json_decode($role_output); 
    return $role_info;
}

// Check user at LMS site by user email id
function check_lms_user_by_emailid($user_email = null){
    if($user_email != null){
        global $wpdb;
        $access_token = get_option( 'access_token' );
        $api_url = get_option('api_url');
        $site_id = get_option('site_id');
        $header = array("Authorization: Bearer $access_token");
        // save lms user data
        $curlUser = curl_init();
        curl_setopt_array($curlUser, array(
            CURLOPT_URL => $api_url.'/api/v1/users/index2?query='.$user_email,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true
        ));
        $responseUser = curl_exec($curlUser);
        curl_close($curlUser);
        $user_data = json_decode($responseUser, true);
        return $user_data;
    }  
}


// Register WP user at LMS site
function register_user_lms($user_id = null) {
    global $wpdb;
    $access_token = get_option( 'access_token' );
    $api_url = get_option( 'api_url' );
    $site_id = get_option( 'site_id' );
    $header = array("Authorization: Bearer $access_token");
    $user_info = get_userdata($user_id);
    // WP User Information
    $wp_user_first_name = $user_info->first_name;
    $wp_user_last_name = $user_info->last_name;
    $wp_user_email = $user_info->user_email;
    $wp_user_screen_name = $user_info->user_nicename;
    $wp_user_registered = $user_info->user_registered;

    $user_data = array(
        'first_name' => isset($wp_user_first_name) ? $wp_user_first_name : null,
        'last_name' => isset($wp_user_last_name) ? $wp_user_last_name : null,
        'email' => isset($wp_user_email) ? $wp_user_email : null,
        'screen_name' => isset($wp_user_screen_name) ? $wp_user_screen_name : null,
        'active' => '1',
        'created_at' => isset($wp_user_registered) ? $wp_user_registered : null,
    );                                                                    
    $data_string = json_encode($user_data);                                                                                                                                                                                                    
    $ch = curl_init($api_url.'/api/v1/users');                                                                      
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(      
        "Authorization: Bearer $access_token",                                                                    
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data_string))                                                                       
    );

    $server_output = curl_exec($ch);
    curl_close ($ch);
    $register_info = json_decode($server_output); 
    return $register_info;
}

// Get single asscess token
function get_single_access_token($user_id = null) {
    $access_token = get_option( 'access_token' );
    $api_url = get_option( 'api_url' );
    $site_id = get_option( 'site_id' );
    $header = array("Authorization: Bearer $access_token");
    $curlUser = curl_init();
    curl_setopt_array($curlUser, array(
        CURLOPT_URL => $api_url.'/api/v1/users/'.$user_id,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_RETURNTRANSFER => true
    ));
    $responseUser = curl_exec($curlUser);
    curl_close($curlUser);
    $user_data = json_decode($responseUser, true);
    return $user_data;
}

//Get Site Name 
function get_lms_site_name() {
    global $wpdb;
    $access_token = get_option( 'access_token' );
    $api_url = get_option( 'api_url' );
    $site_id = get_option( 'site_id' );
    $header = array("Authorization: Bearer $access_token");
    $curlSite = curl_init();
    curl_setopt_array($curlSite, array(
        CURLOPT_URL => $api_url.'/api/v1/sites/'.$site_id,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_RETURNTRANSFER => true
    ));
    $responseSite = curl_exec($curlSite);
    curl_close($curlSite);
    $site_data = json_decode($responseSite, true);
    return $site_data;
}

// SSO Link Generate
function add_sso_link_script(){
    if ( is_user_logged_in() ) {
        global $wpdb;
        $user_id = get_current_user_id();
        $user_info_pre = get_userdata($user_id);
        $user_email = $user_info_pre->user_email;
        $uesrbyemailRes = check_lms_user_by_emailid($user_email);

        if(!empty($uesrbyemailRes['users'])){
            $lmsuserid = $uesrbyemailRes['users'][0]['id']; 
            $api_url = get_option( 'api_url' );
            $site_id = get_option( 'site_id' );
            $access_token = get_option( 'access_token' );
            $singleAccessTokenResponse = get_single_access_token($lmsuserid);
            $site_name_data = get_lms_site_name();
            ?>
<script>
jQuery(document).ready( function(e){        
    jQuery('.item').find('.product_container div:first a, .product_container div:last a').on('click', function(e) {
        e.preventDefault(); 
        var clickedURL = jQuery(this).attr('href');
                var courseName = clickedURL,
                parts = clickedURL.split("/"),
                last_part = parts[parts.length - 2];
        jQuery.ajax({
            url : '<?php echo $api_url; ?>/api/v1/users/<?php echo $lmsuserid;?>',
            type : 'get',
            data : {
                access_token: '<?php echo $access_token ; ?>'
            },
            success : function( response ) {
              var sso_link_redirect = '<?php echo $api_url; ?>/callback/'+response.single_access_token+'?site=<?php echo $site_name_data['host'] ?>&return_to=courses/' + last_part;
              window.open(sso_link_redirect, '_blank');
            }
        });
         
    });     
});
</script>
<?php
        }
    }
}
add_action('wp_footer', 'add_sso_link_script');

// Hook for sso after next login by user
add_filter('wp_login', 'login_success_sso_generate');
function login_success_sso_generate() {
    add_action('wp_footer', 'add_sso_link_script');
}
