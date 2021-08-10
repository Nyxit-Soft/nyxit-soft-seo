<?php

defined( 'ABSPATH' ) ?: exit;

/**
 * Meta data handler
 * 
 * @package Nyxit SEO
 * 
 * @since 1.0.1
 */

class nyxitSeoMetaData
{
	protected $settings;

	/**
	 * Adding actions and shortcodes
	 * 
	 * @param array $settings
	 */
    public function __construct( $settings )
    {
		$this->settings = $settings;

        add_action( 'add_meta_boxes', [ $this, 'register_post_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_post_meta' ] );
		add_action( 'wp_head', [ $this, 'output_post_meta_data' ] );
		add_shortcode( 'nyxit_h1', [ $this, 'h1_shortcode' ] );
    }

	/**
	 * Register SEO metabox on posts, pages, and products
	 */
    public function register_post_meta_box()
	{
		add_meta_box( 'nyxit-seo-metabox', 'SEO', [ $this, 'post_metabox_callback' ], [ 'post', 'page', 'product' ], 'normal' );
    }

	/**
	 * Callback for register_post_meta_box()
	 */
    public function post_metabox_callback()
	{
		global $post;

		echo '<input type="hidden" name="nyxit_seo_nonce" value="' . wp_create_nonce( 'nyxit-seo-metabox-' . (string)$post->ID ) . '" />';
		?>
		<table class="form-table">
		<tr>
			<th>
				<label>Meta Description</label>
			</th>
			<td>
				<input type="text" name="nyxit_meta_desc" class="regular-text" value="<?= $this->get_meta( 'meta_desc', $post->ID ); ?>"> 
			</td>
		</tr>
		<tr>
			<th>
				<label>H1 (Optional)</label>
			</th>
			<td>
				<input type="text" name="nyxit_h1" class="regular-text" value="<?= $this->get_meta( 'h1', $post->ID ); ?>"> 
			</td>
		</tr>
		<?php if ( isset( $this->settings['activate_og'] ) ): ?>
		<tr>
			<th>
				<label>Title - Open Graph (Optional)</label>
			</th>
			<td>
				<input type="text" name="nyxit_og_title" class="regular-text" value="<?= $this->get_meta( 'og_title', $post->ID ); ?>"> 
			</td>
		</tr>
		<tr>
			<th>
				<label>Description - Open Graph (Optional)</label>
			</th>
			<td>
				<input type="text" name="nyxit_og_desc" class="regular-text" value="<?= $this->get_meta( 'og_desc', $post->ID ); ?>"> 
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( isset( $this->settings['activate_tc'] ) ): ?>
		<tr>
			<th>
				<label>Title - Twitter Card (Optional)</label>
			</th>
			<td>
				<input type="text" name="nyxit_tw_title" class="regular-text" value="<?= $this->get_meta( 'tw_title', $post->ID ); ?>"> 
			</td>
		</tr>
		<tr>
			<th>
				<label>Description - Twitter Card (Optional)</label>
			</th>
			<td>
				<input type="text" name="nyxit_tw_desc" class="regular-text" value="<?= $this->get_meta( 'tw_desc', $post->ID ); ?>"> 
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<th>
				<label>Mark as "noindex"</label>
			</th>
			<td>
				<input type="checkbox" name="nyxit_noindex" value="1"> 
			</td>
		</tr>
		</table>
		<?php
	}

	/**
	 * Save SEO meta to database
	 * 
	 * @param int $post_id
	 * @param array $seo_meta
	 * */    
    protected function add_post_meta_to_db( int $post_id, array $seo_meta )
	{
		foreach ( $seo_meta as $key => $value ) {

			$value = implode( ',', (array)$value );

			if ( get_post_meta($post_id, $key, FALSE) ) {
				update_post_meta($post_id, $key, $value);
			} else {
				add_post_meta($post_id, $key, $value);
			}

			if( ! $value ) {
				delete_post_meta($post_id, $key);
			}
		}
	}
	
	/**
	 * Get SEO meta from database
	 * 
	 * @param string $meta_name
	 * @param int $post_id
	 */
	protected function get_meta( string $meta_name, int $post_id )
	{
		$meta = get_post_meta( $post_id, '_nyxit_'.$meta_name , true );
		
		return empty( $meta ) ? null : $meta;
	}
    
	/**
	 * Sanitize SEO meta from user input
	 */
    public function save_post_meta( $post_id )
	{
		if ( ! isset( $_POST['nyxit_seo_nonce']) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['nyxit_seo_nonce'], 'nyxit-seo-metabox-' . $post_id) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$seo_meta['_nyxit_h1'] = sanitize_text_field( $_POST['nyxit_h1'] );
		$seo_meta['_nyxit_meta_desc'] = sanitize_text_field( $_POST['nyxit_meta_desc'] );
		$seo_meta['_nyxit_noindex'] = $_POST['nyxit_noindex'] ?? "0";

		if ( isset( $this->settings['activate_og'] ) )
		{
			$seo_meta['_nyxit_og_title'] = sanitize_text_field( $_POST['nyxit_og_title'] );
			$seo_meta['_nyxit_og_desc'] = sanitize_text_field( $_POST['nyxit_og_desc'] );
		}

		if ( isset( $this->settings['activate_tc'] ) )
		{
			$seo_meta['_nyxit_tw_title'] = sanitize_text_field( $_POST['nyxit_tw_title'] );
			$seo_meta['_nyxit_tw_desc'] = sanitize_text_field( $_POST['nyxit_tw_desc'] );
		}

		$this->add_post_meta_to_db( $post_id, $seo_meta );
	}

	/**
	 * Add H1 shortcode [nyxit_h1 id="" class=""]
	 */
	public function h1_shortcode( $atts )
	{
		$atts = shortcode_atts( [
			'id' => '',
			'class' => '',
		], $atts );
		$post_id = nyxitSeoHelper::get_cur_view_id();
		$h1_text = $this->get_meta( 'h1', $post_id );

		$h1 = '<h1';
		$h1 .= ! empty( $atts['id'] ) ? ' id="'.$atts['id'].'"' : '';
		$h1 .= ! empty( $atts['class'] ) ? ' class="'.$atts['class'].'"' : '';
		$h1 .= '>';
		$h1 .= ! empty( $h1_text ) ? $h1_text : get_the_title( $post_id );
		$h1 .= '</h1>';

		return $h1;
	}
	
	/**
	 * Add SEO meta to the front-end
	 */
	public function output_post_meta_data()
	{
		if ( ! $id = nyxitSeoHelper::get_cur_view_id() )
		{
			return;
		}

		$desc = $this->get_meta( 'meta_desc', $id );
		$og_title = $this->get_meta( 'og_title', $id ) ?? get_the_title( $id );
		$og_desc = $this->get_meta( 'og_desc', $id ) ?? $desc;
		$tw_title = $this->get_meta( 'tw_title', $id ) ?? get_the_title( $id );
		$tw_desc = $this->get_meta( 'tw_desc', $id ) ?? $desc;
		$img = nyxitSeoHelper::get_post_image( $id );
		?>
<meta name="description" content="<?= $desc; ?>" />
<?php if ( isset( $this->settings['activate_og'] ) ): ?>
<!-- Open Graph -->
<meta property="og:title" content="<?= $og_title; ?>" />
<meta property="og:type" content="<?= is_front_page() ? "website" : "article" ?>" />
<meta property="og:url" content="<?php the_permalink( $id ); ?>" />
<?php if ( $img ): ?>
<meta property="og:image" content="<?= $img[0]; ?>" />
<meta property="og:image:width" content="<?= $img[1] ?>" />
<meta property="og:image:height" content="<?= $img[2] ?>" />
<?php endif ?>
<meta property="og:description" content="<?= $og_desc ?>" />
<meta property="og:site_name" content="<?php bloginfo('name'); ?>" />
<meta property="og:locale" content="<?= get_locale() ?>" />
<?php endif; ?>
<?php if ( isset( $this->settings['activate_tc'] ) ): ?>
<!-- Twitter Card -->
<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="@<?= $this->settings['twitter_name']; ?>">
<meta name="twitter:title" content="<?= $tw_title ?>">
<meta name="twitter:description" content="<?= $tw_desc ?>">
<?php if ( $img ): ?>
<meta name="twitter:image" content="<?= $img[0] ?>">
<?php endif ?>
<?php endif;
echo $this->get_meta( 'noindex', $id ) == "1" ? '<meta name="robots" content="noindex, follow">' : '';
	}
}