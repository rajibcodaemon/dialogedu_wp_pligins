

jQuery(document).ready(function() {
    jQuery("#pull-all-courses").click(function () {
        try{
            jQuery.get( "http://localhost/dialogEdu/wp-json/mytwentytwentytheme/v1/courses", function( data ) {
                console.log( data );
            });
        }catch(e){
            console.log( e );
        }
    });
});

function get_courses(){
    try{
        jQuery.get( "http://localhost/dialogEdu/wp-json/mytwentytwentytheme/v1/courses", function( data ) {
            console.log( data );
        });
    }catch(e){
        console.log( e );
    }
    
}