<?php

defined( 'ABSPATH' ) ?: exit;

class nyxitSitemap
{
    protected $settings;

    public function __construct( $settings )
    {
        $this->settings = $settings;
        
        // disable wp build-in sitemap
        add_filter('wp_sitemaps_enabled', '__return_false');

        add_action( 'wp_loaded', [ $this, 'output_sitemap' ] );
    }

    public function output_sitemap()
    {
        if ( $_SERVER['REQUEST_URI'] === '/sitemap.xml' )
        {
            header("Content-type: text/xml");
            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			// todo: get noindex post ids; build but not tested!!
            $posts = get_posts( array(
                'posts_per_page' => -1,
                'post_type' => [ 'post', 'page', ],
                'post_status' => 'publish',
                'meta_key' => 'nyxit_noindex',
                'meta_value' => '0',
            ) );

            foreach ( $posts as $post ) {
                echo "<url><loc>".get_the_permalink( $post->ID )."</loc><lastmod>".get_the_date( "Y-m-d", $post->ID )."</lastmod></url>";
            }

            echo '</urlset>';
            exit;
        }
    }
}