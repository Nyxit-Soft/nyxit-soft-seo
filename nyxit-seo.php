<?php
/*
Plugin Name: Nyxit SEO
Plugin URI: https://nyxit.com/
Description: Just another seo plugin. Simplicity is key.
Author: Nikolay Nikolaev
Author URI: https://nikolaynikolaev.com/
Text Domain: nyxit-seo
Domain Path: /languages/
Version: 1.0.0
*/

define( 'NYXIT_SEO_VERSION', '1.0.0' );

define( 'NYXIT_SEO_WP_VERSION', '5.5.1' );

if ( ! class_exists( 'nyxitSEO' ) ):

class nyxitSEO
{
    protected $options;
    protected $settings;

    public function __construct()
    {
        if ( ! class_exists( 'nyxitSettings' ) )
        {
            require "inc/settings.php";
            $this->options = new nyxitSettings();

            $this->settings = $this->options->get_settings_from_db();

            if ( $this->settings === false )
            {
                $this->install();
            }

            $this->options->init_ui();
            $this->init();
        }
    }

    public function install()
    {
        $this->options->add_default_settings_to_db();
        $this->settings = $this->options->get_default_settings();
        
    }

    public function init()
    {
        if ( isset( $this->settings['activate_meta_data'] ) &&
            ! class_exists( 'nyxitMetaData' ) )
        {
            require 'inc/meta-data.php';
            new nyxitMetaData( $this->settings );
        }

        if ( isset( $this->settings['activate_sitemap'] ) &&
            ! class_exists( 'nyxitSitemap' ) )
        {
            require 'inc/sitemap.php';
            new nyxitSitemap( $this->settings );
        }

        if ( isset( $this->settings['activate_breadcrumbs'] ) &&
            ! class_exists( 'nyxitBreadcrumbs' ) )
        {
            require 'inc/breadcrumbs.php';
            new nyxitBreadcrumbs();
        }
    }
}

$nyxit_seo = new nyxitSEO();

endif;