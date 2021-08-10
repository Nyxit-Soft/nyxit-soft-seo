<?php

defined( 'ABSPATH' ) ?: exit;

if ( ! class_exists( 'nyxitSeoHelper' ) ):

class nyxitSeoHelper
{
    /**
     * Get the ID of the current view
     * (home, front, pages, posts)
     */
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

    /**
     * Get the URL of the featured image or
     * the first image attachment
     * 
     * @param int $post_id
     */
    public static function get_post_image( int $post_id )
	{
		$image_id = 0;
		
		// check for post thumbnail
		if ( has_post_thumbnail( $post_id ) )
		{
			$image_id = get_post_thumbnail_id( $post_id );
		}
		else
		{
			// check for image attachment
			$attachments = get_children( [
				'post_parent'    => $post_id,
				'post_type'      => 'attachment',
				'numberposts'    => 1,
				'post_status'    => 'inherit',
				'post_mime_type' => 'image',
				'order'          => 'ASC',
				'orderby'        => 'menu_order ASC'
			], ARRAY_A );

			foreach ( $attachments as $image )
			{
				$image_id = $image['ID'];
			}
		}
		
		$image = wp_get_attachment_image_src( $image_id, 'large' );
		return $image;
	}
}

endif;