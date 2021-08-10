<?php

defined( 'ABSPATH' ) ?: exit;



class NyxitSeoTests
{
    public static function run()
    {
        global $post;
        $post_id = $post->ID;
        
        // Test helper class
        echo "<p><b>Testing nyxitSeoHelper class...</b></p>";

        echo "nyxitSeoHelper::get_cur_view_id()";
        var_dump( nyxitSeoHelper::get_cur_view_id() );

        echo "nyxitSeoHelper::get_post_image()";
        var_dump( nyxitSeoHelper::get_post_image( $post_id ) );
        
        // Test meta data
        echo "<p><b>Testing nyxitSeoMetaData class...</b></p>";

        echo "<p>Meta description for this page is:</p>";
        var_dump( get_post_meta( $post_id, '_nyxit_meta_desc' , true ) );

        echo "<p>Open Graph title for this page is:</p>";
		var_dump( get_post_meta( $post_id, '_nyxit_og_title' , true ) );

        echo "<p>Open Graph desc for this page is:</p>";
		var_dump( get_post_meta( $post_id, '_nyxit_og_desc' , true ) );

        echo "<p>Twitter title for this page is:</p>";
        var_dump( get_post_meta( $post_id, '_nyxit_tw_title' , true ) );

        echo "<p>Twitter desc for this page is:</p>";
		var_dump( get_post_meta( $post_id, '_nyxit_tw_desc' , true ) );
    }
}

?>
