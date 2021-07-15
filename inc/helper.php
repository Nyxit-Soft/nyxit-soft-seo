<?php

defined( 'ABSPATH' ) ?: exit;

if ( ! class_exists( 'nyxitSeoHelper' ) ):

class nyxitSeoHelper
{
    public static function get_cur_view_id()
    {
        $id = 0;

        if ( is_home() )
        {
            $id = get_option( 'page_for_posts' );
        }
        elseif ( is_front_page() )
        {
            $id = get_option( 'page_on_front' );
        }
        elseif ( is_page() || is_single() )
        {
            global $post;
            $id = $post->ID;
        }
        
        return $id;
    }
}

endif;