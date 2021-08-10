<?php

defined( 'ABSPATH' ) ?: exit;

class nyxitSeoSettings
{
    protected $default_options = [
        // uncomment to enable by default
        'activate_meta_data' => "1",
        //'activate_structured_data' => "1",
        'activate_og' => "1",
        'activate_tc' => "1",
        'twitter_name' => '',
    ];

    protected $options = false;

    /**
     * Add default plugin settings to database
     */
    public function add_default_settings_to_db()
    {
        if ( ! add_option( 'nyxit_seo_options', $this->default_options ) )
        {
            throw new Exception( "Failed to add default settings to database." );
        }
    }

    /**
     * Get the default plugin settings
     */
    public function get_default_settings()
    {
        return $this->default_options;
    }

    /**
     * Get settings from the database
     */
    public function get_settings_from_db() : array
    {
        $this->options = get_option( 'nyxit_seo_options', array() );

        return $this->options;
    }

    /**
     * Add settings administration UI and setup
     */
    public function init_ui()
    {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init',  array( $this, 'init_settings') );
    }

    /**
     * 
     */
    public function admin_menu()
	{
		add_options_page(
			'Nyxit SEO Settings',
			'Nyxit SEO Settings',
			'administrator',
			'nyxit-seo-settings',
			array( $this, 'settings_page' )
		);
	}

    /**
     * Settings page callback
     */
	public function settings_page()
	{
		?>
        <h1 style="margin-bottom: 50px;">Nyxit SEO Settings</h1>
		<form action='options.php' method='post'>
			<?php
			settings_fields( 'nyxit_seo_settings' );
			do_settings_sections( 'nyxit_seo_settings' );
			submit_button();
			?>
		</form>
		<?php
    }
    
    /**
     * Initialize sections and individual setting fields
     */
    public function init_settings()
	{
        register_setting( 'nyxit_seo_settings', 'nyxit_seo_options');
        
        /**
         * General
         **/
        add_settings_section(
			'nyxit_seo_general',
			'General Settings',
			function () {
                // no description
            },
			'nyxit_seo_settings'
        );

        add_settings_field(
			'activate_meta_data',
			'Activate Meta Data',
            function () {
                $checked = checked( isset( $this->options['activate_meta_data'] ), "1", false );
                echo '<input type="checkbox" name="nyxit_seo_options[activate_meta_data]" value="1" '.$checked.'>';
            },
			'nyxit_seo_settings',
			'nyxit_seo_general'
        );
        
		/** 
         * Meta Data Settings
         **/
		add_settings_section(
			'nyxit_seo_metadata',
			'Meta Data Settings',
            function () {
                // no description
            },
			'nyxit_seo_settings'
        );
        
        add_settings_field(
			'activate_og',
			'Activate Open Graph',
            function () {
                $checked = checked( isset( $this->options['activate_og'] ), "1", false );
                echo '<input type="checkbox" name="nyxit_seo_options[activate_og]" value="1" '.$checked.'>';
            },
			'nyxit_seo_settings',
			'nyxit_seo_metadata'
        );

        add_settings_field(
			'activate_tc',
			'Activate Twitter Card',
            function () {
                $checked = checked( isset( $this->options['activate_tc'] ), "1", false );
                echo '<input type="checkbox" name="nyxit_seo_options[activate_tc]" value="1" '.$checked.'>';
            },
			'nyxit_seo_settings',
			'nyxit_seo_metadata'
        );

        add_settings_field(
			'twitter_name',
			'Set Twitter Username for Twitter Card',
            function () {
                echo '<input type="text" name="nyxit_seo_options[twitter_name]" value="'.$this->options['twitter_name'].'"><p>You can find it in your profile URL: https://twitter.com/<b>username</b></p>';
            },
			'nyxit_seo_settings',
			'nyxit_seo_metadata'
        );

        /** 
         * Stuctured Data Settings
         **/
    }
}