<?php

defined( 'ABSPATH' ) ?: exit;

class nyxitSeoMetaData
{
	protected $settings;

    public function __construct( $settings )
    {
		$this->settings = $settings;

        add_action( 'add_meta_boxes', [ $this, 'register_post_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_post_meta' ] );
		add_action( 'wp_head', [ $this, 'output_post_meta_data' ] );
		add_shortcode( 'nyxit_h1', [ $this, 'h1_shortcode' ] );
    }

    public function register_post_meta_box()
	{
		add_meta_box( 'nyxit-seo-metabox', 'SEO', [ $this, 'post_metabox_callback' ], [ 'post', 'page', 'product' ], 'normal' );
    }

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
				<input type="text" name="nyxit_fb_title" class="regular-text" value="<?= $this->get_meta( 'fb_title', $post->ID ); ?>"> 
			</td>
		</tr>
		<tr>
			<th>
				<label>Description - Open Graph (Optional)</label>
			</th>
			<td>
				<input type="text" name="nyxit_fb_desc" class="regular-text" value="<?= $this->get_meta( 'fb_desc', $post->ID ); ?>"> 
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
    
    protected function add_post_meta_to_db( $post_id, $seo_meta )
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
	
	protected function get_meta( $meta_name, $post_id )
	{
		$meta = get_post_meta( $post_id, '_nyxit_'.$meta_name , true );
		
		return empty( $meta ) ? null : $meta;
	}
    
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
			$seo_meta['_nyxit_fb_title'] = sanitize_text_field( $_POST['nyxit_fb_title'] );
			$seo_meta['_nyxit_fb_desc'] = sanitize_text_field( $_POST['nyxit_fb_desc'] );
		}

		if ( isset( $this->settings['activate_tc'] ) )
		{
			$seo_meta['_nyxit_tw_title'] = sanitize_text_field( $_POST['nyxit_tw_title'] );
			$seo_meta['_nyxit_tw_desc'] = sanitize_text_field( $_POST['nyxit_tw_desc'] );
		}

		$this->add_post_meta_to_db( $post_id, $seo_meta );
	}

	public function get_post_image( $post_id )
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
	
	public function output_post_meta_data()
	{
		if ( ! $id = nyxitSeoHelper::get_cur_view_id() )
		{
			return;
		}

		$desc = $this->get_meta( 'meta_desc', $id );
		$fb_title = $this->get_meta( 'fb_title', $id ) ?? get_the_title( $id );
		$fb_desc = $this->get_meta( 'fb_desc', $id ) ?? $desc;
		$tw_title = $this->get_meta( 'tw_title', $id ) ?? get_the_title( $id );
		$tw_desc = $this->get_meta( 'tw_desc', $id ) ?? $desc;
		$img = $this->get_post_image( $id );
		?>
<meta name="description" content="<?= $desc; ?>" />
<?php if ( isset( $this->settings['activate_og'] ) ): ?>
<!-- Open Graph -->
<meta property="og:title" content="<?= $fb_title; ?>" />
<meta property="og:type" content="<?= is_front_page() ? "website" : "article" ?>" />
<meta property="og:url" content="<?php the_permalink( $id ); ?>" />
<?php if ( $img ): ?>
<meta property="og:image" content="<?= $img[0]; ?>" />
<meta property="og:image:width" content="<?= $img[1] ?>" />
<meta property="og:image:height" content="<?= $img[2] ?>" />
<?php endif ?>
<meta property="og:description" content="<?= $fb_desc ?>" />
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