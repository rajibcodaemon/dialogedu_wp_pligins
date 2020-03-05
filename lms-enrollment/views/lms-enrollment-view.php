<?php
    global $wpdb;
    $table_name_enrol = $wpdb->prefix . 'lms_enrolments';
    $enrol_data = $wpdb->get_results("SELECT `enrolment_id`, `course_id`, `user_id`, `user_name`, `total_score`, `user_active`, `status` FROM $table_name_enrol");
?>
<div class="wrap mt-2">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h2>Enrollment Details</h2>
            </div>
            <div class="col-sm-6">
                <button type="button" class="btn btn-success" id="pullEnrolment"
                    data-val="<?php echo plugins_url(); ?>">Pull Enrollment Data</button>
            </div>
        </div>
    </div>
    <div class="container-fluid mt-5">
        <table <?php echo (!empty($enrol_data)) ?'id="enrolmentTable"':''; ?> class="table table-striped table-bordered"
            style="width:100%">
            <thead>
                <tr>
                    <th>Enrollment ID</th>
                    <th>Course Name</th>
                    <th>User Name</th>
                    <th>Total Score</th>
                    <th>User Status</th>
                    <th>Enrollment Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
        if(!empty($enrol_data)){
            foreach($enrol_data as $enrol_key => $enrol_val){
                $access_token = get_option( 'access_token' );
                $api_url = get_option( 'api_url' );
                $site_id = get_option( 'site_id' );

                $header = array("Authorization: Bearer $access_token");

                $cid = $enrol_val->course_id;
				$curlc = curl_init();
				curl_setopt_array($curlc, array(
					CURLOPT_URL => "$api_url/api/v1/sites/$site_id/courses/$cid",
					CURLOPT_HTTPHEADER => $header,
					CURLOPT_RETURNTRANSFER => true
				));
				$responsec = curl_exec($curlc);
				curl_close($curlc);
                $responsec = json_decode($responsec, true);
        ?>
                <tr>
                    <td><?php echo $enrol_val->enrolment_id; ?></td>
                    <td><?php echo $responsec['title']; ?></td>
                    <td><?php echo $enrol_val->user_name; ?></td>
                    <td><?php echo $enrol_val->total_score; ?></td>
                    <td><?php echo $enrol_val->user_active == '1'?'Active':'Inactive'; ?></td>
                    <td><?php echo ucfirst($enrol_val->status); ?></td>
                </tr>
                <?php
            }
        }else{
        ?>
                <tr>
                    <td colspan="6" align="center">No Enrollment Record Found</td>
                </tr>
                <?php
        }
        ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Enrollment ID</th>
                    <th>Course Name</th>
                    <th>User Name</th>
                    <th>Total Score</th>
                    <th>User Status</th>
                    <th>Enrollment Status</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<div id="loading"><img src="<?php echo get_site_url(); ?>/wp-content/plugins/lms-enrollment/assets/image/loader.gif"
        alt="Loading.." /></div>