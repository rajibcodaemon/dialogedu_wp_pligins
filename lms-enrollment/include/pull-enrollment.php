<?php
require "../../../../wp-load.php";

global $wpdb;

$access_token = get_option( 'access_token' );
$api_url = get_option( 'api_url' );
$site_id = get_option( 'site_id' );

$header = array("Authorization: Bearer $access_token");

// save lms user data

$curlUsers = curl_init();
curl_setopt_array($curlUsers, array(
    CURLOPT_URL => "$api_url/api/v1/users",
    CURLOPT_HTTPHEADER => $header,
    CURLOPT_RETURNTRANSFER => true
));
$responseUsers = curl_exec($curlUsers);
curl_close($curlUsers);
$user_data = json_decode($responseUsers, true);

if(!empty($user_data) && isset($user_data['users'])){
    $tablename_user = $wpdb->prefix.'lms_users';
    $wpdb->query("TRUNCATE TABLE $tablename_user");

    $user_data = $user_data['users'];
    foreach($user_data as $ukey => $uvalue) 
    {
        $data_user = array(
            'user_id' => $uvalue['id'], 
            'external_id' => $uvalue['external_id'],
            'screen_name' => $uvalue['screen_name'], 
            'email' => $uvalue['email'],
            'first_name' => $uvalue['first_name'], 
            'last_name' => $uvalue['last_name'], 
            'sex' => $uvalue['sex'],
            'active' => $uvalue['active'], 
            'created_at' => $uvalue['created_at'], 
            'updated_at' => $uvalue['updated_at'],
        );
        $wpdb->insert( $tablename_user, $data_user);
    }
}else {
    echo "<h1>Failed to find LMS Registered User</h1>";
}   

// save lms enrolment data

$curlCourse = curl_init();
curl_setopt_array($curlCourse, array(
    CURLOPT_URL => "$api_url/api/v1/sites/$site_id/courses",
    CURLOPT_HTTPHEADER => $header,
    CURLOPT_RETURNTRANSFER => true
));
$responseCourse = curl_exec($curlCourse);
curl_close($curlCourse);

$course_data = json_decode($responseCourse, true);

if(!empty($course_data) && isset($course_data['courses'])){
    $tablename = $wpdb->prefix.'lms_enrolments';
    $wpdb->query("TRUNCATE TABLE $tablename");

    $course_data = $course_data['courses'];
    
    foreach($course_data as $keyi => $valuei) 
    {
        $curlEnrolment = curl_init();
        curl_setopt_array($curlEnrolment, array(
            CURLOPT_URL => "$api_url/api/v1/sites/$site_id/courses/".$valuei['id']."/enrollments",
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true
        ));
        $responseEnrolment = curl_exec($curlEnrolment);
        curl_close($curlEnrolment);

        $enrolments_data = json_decode($responseEnrolment, true);
        
        if(!empty($enrolments_data) && isset($enrolments_data['enrollments'])){

            $enrolments_data = $enrolments_data['enrollments'];

            foreach($enrolments_data as $endkey => $endval) 
            {
                $data = array(
                            'enrolment_id' => $endval['id'], 
                            'course_id' => $endval['course_id'],
                            'user_id' => $endval['user_id'], 
                            'user_name' => $endval['user_name'],
                            'total_score' => $endval['total_score'], 
                            'user_active' => $endval['user_active'], 
                            'created_at' => $endval['created_at'],
                            'updated_at' => $endval['updated_at'], 
                            'status' => $endval['status'], 
                            'is_certificated' => $endval['is_certificated'],
                            'printed_certificate' => $endval['printed_certificate'], 
                            'certificate_earned_at' => $endval['certificate_earned_at'], 
                            'current_progress' => $endval['current_progress'], 
                            'current_state' => $endval['current_state'],
                            'current_state_updated_at' => $endval['current_state_updated_at'], 
                            'started_at' => $endval['started_at'],
                            'finished_at' => $endval['finished_at']
                        );
                
                $wpdb->insert( $tablename, $data);
            }
        }
    }
}