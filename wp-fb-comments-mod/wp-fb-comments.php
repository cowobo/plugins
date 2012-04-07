<?php
/*

Plugin Name: WP-FB-comments (CoWoBo mod)
Plugin URI: http://ankitkumaragarwal.com/wp-fb-comments-the-next-generation-wp-fb-integration/
Description: Merge Wordpress and Facebook Comment system fully. Modded for category control (CoWoBo.org)
Version: 1.2.6 / 1.0
Author: Ankit Kumar Agarwal, Piyush Mishra, CoWoBo
Author URI: http://www.piyushmishra.com/plugins/wp-fb-comments.html
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

*/
/*  Copyright 2011  Ankit Kumar Agarwal (email : wpfb@ankitkumaragarwal.com), Piyush Mishra  (email : me@piyushmishra.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//Activation hook.
register_activation_hook(__FILE__,array('wp_fb_comments_admin','activate'));

//Deactivvation Hook
register_deactivation_hook(__FILE__,array('wp_fb_comments_admin','deactivate'));
//Include facebook sdk class
if( ! class_exists( 'Facebook' ) )
	require_once( ABSPATH . '/wp-content/plugins/social-connect/facebook/facebook.php' );
//Add the class.
require_once( 'wp-fb-comments-class.php' );
if(is_admin())
{
	//Add files for displaying admin panel properly
	require_once( 'admin/admin.php' );
}
else
{
	$wp_fb_comments_instance = new wp_fb_comments();
}
