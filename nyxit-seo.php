<?php
/*
Plugin Name: Nyxit SEO
Plugin URI: https://nyxitsoft.com/
Description: Just another seo plugin. Simplicity is key.
Author: Nikolay Nikolaev
Author URI: https://nikolaynikolaev.com/
Text Domain: nyxit-seo
Domain Path: /languages/
Version: 1.0.0
*/

/*
	Copyright (C) 2021 Nikolay Nikolaev

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define( 'NYXIT_SEO_VERSION', '1.0.1' );

if ( ! class_exists( 'nyxitSEO' ) ):

class nyxitSEO
{
    protected $options;
    protected $settings;

    public function __construct()
    {
        if ( ! class_exists( 'nyxitSeoSettings' ) )
        {
            require "inc/settings.php";
            $this->options = new nyxitSeoSettings();

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
        require 'inc/helper.php';

        if ( isset( $this->settings['activate_meta_data'] ) &&
            ! class_exists( 'nyxitSeoMetaData' ) )
        {
            require 'inc/meta-data.php';
            new nyxitSeoMetaData( $this->settings );
        }

        if ( WP_DEBUG )
        {
            include 'tests/test.php';
        }
    }
}

new nyxitSEO();

endif;