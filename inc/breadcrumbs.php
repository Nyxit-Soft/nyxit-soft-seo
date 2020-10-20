<?php 

defined( 'ABSPATH' ) ?: exit;

class nyxitBreadcrumbs
{
    protected $breadcrumbs;

    public function __construct()
    {
        $this->breadcrumbs['Home'] = home_url("/");

        add_shortcode( 'nyxit_breadcrumbs', [ $this, 'breadcrumbs_shortcode' ] );
    }

    protected function set_post_breadcrumbs( $post_id )
    {
        $blog_id = get_option( 'page_for_posts' );

        if ( $blog_id )
        {
            $blog_title = get_the_title( $blog_id );
            $this->breadcrumbs[$blog_title] = get_the_permalink( $blog_id );
        }        

        $post_title = get_the_title( $post_id );
        $this->breadcrumbs[$post_title] = get_the_permalink( $post_id );
    }

    protected function set_page_breadcrumbs( $page_id )
    {
        $pages = [];

        do
        {
            $url = get_the_permalink( $page_id );
            $pages[get_the_title( $page_id )] = $url;
            $page_id = wp_get_post_parent_id( $page_id );
        }
        while( $page_id );

        $this->breadcrumbs = array_merge( $this->breadcrumbs, array_reverse( $pages ) );
    }

    protected function set_404_breadcrumbs()
    {
        $this->breadcrumbs['404'] = '';
    }

    protected function build_breadcrumbs()
    {
        global $post;

        if ( is_page() )
        {
            $this->set_page_breadcrumbs( $post->ID );
        }
        elseif ( is_single() )
        {
            $this->set_post_breadcrumbs( $post->ID );
        }
        elseif ( is_404() )
        {
            $this->set_404_breadcrumbs();
        }
    }

    public function breadcrumbs_shortcode( $atts )
    {
        $atts = shortcode_atts( [
            'tag' => 'ul',
            'tag_id' => '',
            'tag_class' => '',
            'item_class' => '',
        ], $atts );

        if ( $atts['tag'] === 'ul' || $atts['tag'] === 'ol' )
        {
            $item_tag = 'li';
        }
        elseif ( $atts['tag'] === 'div' )
        {
            $item_tag = 'a';
        }
        else
        {
            return '<p>Breadcrumbs failure. Invalid main tag: please, use only "ol", "ul" or "div" tags.<p>';
        }

        $this->build_breadcrumbs();
        
        $tag_atts = ! empty( $atts['tag_id'] ) ? ' id="'.$atts['tag_id'].'"' : '';
        $tag_atts .= ! empty( $atts['tag_class'] ) ? ' class="'.$atts['tag_class'].'"' : "";
        $item_atts = ! empty( $atts['item_class'] ) ? ' class="'.$atts['item_class'].'"' : "";

        $html = '<'.$atts['tag'].$tag_atts.'>';

        foreach ( $this->breadcrumbs as $name => $url )
        {
            if ( $item_tag === "li" )
            {
                $html .= '<'.$item_tag.$item_atts.'><a href="'.$url.'">'.$name.'</a></'.$item_tag.'>';
            }
            else
            {
                $html .= '<'.$item_tag.$item_atts.' href="'.$url.'">'.$name.'</'.$item_tag.'>';
            }
            
        }

        $html .= '</'.$atts['tag'].'>';

        return $html;
    }
}