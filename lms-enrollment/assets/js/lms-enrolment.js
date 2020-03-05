jQuery(document).ready(function() {
  jQuery("#enrolmentTable").DataTable();

  jQuery("#pullEnrolment").click(function(e) {
    jQuery('#loading').css('visibility', 'visible');
    e.preventDefault();
    jQuery.ajax({
        type: "GET",
        url: jQuery(this).data('val')+'/lms-enrollment/include/pull-enrollment.php',
        
        success: function(result) {
          jQuery('#loading').css('visibility', 'hidden');
          location.reload();
        },
        error: function(error) {
            alert( "There are some error pulling data. Please try again" );
        }
    });
  });
});
