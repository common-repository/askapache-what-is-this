<?php
/**
* Plugin Name: AskApache What Is This
* Short Name: AA Whats This
* Description: Displays what type of document is being displayed.
* Author: AskApache
* Version: 3.0
* Requires at least: 2.5
* Tested up to: 2.8-bleeding-edge
* Tags: askapache, single, is_page, is_archive, is_date, is_year, is_month, is_day, is_time, is_author, is_category, is_tag, is_tax, is_search, is_feed, is_comment_feed, is_trackback, is_home, is_404, is_paged, is_admin, is_attachment, is_singular, is_robots, is_posts_page, debug, developer, theme
* Contributors: AskApache, cduke250
* WordPress URI: http://wordpress.org/extend/plugins/askapache-debug-viewer/
* Author URI: http://www.askapache.com/
* Donate URI: http://www.askapache.com/donate/
* Plugin URI: http://www.askapache.com/wordpress/what-is-this-plugin.html
*
*
* AskApache What Is This - Displays Type of WordPress Query
* Copyright (C) 2009	AskApache.com
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.	If not, see <http://www.gnu.org/licenses/>.
*/


!defined( 'ABSPATH' ) || !function_exists( 'add_options_page' ) || !function_exists( 'add_action' ) || !function_exists( 'wp_die' ) && die( 'death by askapache firing squad' );
!defined( 'COOKIEPATH' ) && define( 'COOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_option('home') . '/') );
!defined( 'SITECOOKIEPATH' ) && define( 'SITECOOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_option('siteurl') . '/') );
!defined( 'ADMIN_COOKIE_PATH' ) && define( 'ADMIN_COOKIE_PATH', SITECOOKIEPATH . 'wp-admin' );
!defined( 'PLUGINS_COOKIE_PATH' ) && define( 'PLUGINS_COOKIE_PATH', preg_replace('|https?://[^/]+|i', '', WP_PLUGIN_URL) );
!defined( 'WP_CONTENT_DIR' ) && define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
!defined( 'WP_PLUGIN_DIR' ) && define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
!defined( 'WP_CONTENT_URL' ) && define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content' );
!defined( 'WP_PLUGIN_URL' ) && define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );

if (!function_exists('stripos')) :
	function stripos($haystack, $needle, $offset = 0) {return strpos(strtolower($haystack), strtolower($needle), $offset);}
endif;

if (!function_exists('str_ireplace')) :
function str_ireplace($search, $replace, $subject){
	$t = chr(1);
  $haystack = strtolower($subject);
  $needle = strtolower($search);
	$searchlen = strlen($search);
	
  while ( ($pos=strpos($haystack, $needle))!==false ){
		$subject = substr_replace($subject, $t, $pos, $searchlen);
		$haystack = substr_replace($haystack, $t, $pos, $searchlen);
	}
  return str_replace($t, $replace, $subject);
}
endif;



if ( !in_array('AskApacheWhatIsThis', (array)get_declared_classes() ) && !class_exists( 'AskApacheWhatIsThis' ) ) :
/**
 * AskApacheWhatIsThis
 *
 * @package 
 * @author webmaster@askapache.com
 * @copyright AskApache
 * @version 2009
 * @access public
 */
class AskApacheWhatIsThis
{
	var $options;
	var $plugin;


	/**
	 * AskApacheWhatIsThis::AskApacheWhatIsThis()
	 */
	function AskApacheWhatIsThis(){}



	function init()
	{
		
		$pb=preg_replace( '|^' . preg_quote(WP_PLUGIN_DIR, '|') . '/|', '', __FILE__ );
		$ph=str_replace('.php','','settings_page_'.basename(__FILE__));
		add_action( 'activate_' . $pb, array(&$this, 'activate') );
		add_action( 'deactivate_' . $pb, array(&$this, 'deactivate') );
		add_filter( 'plugin_action_links_' . $pb, array(&$this, 'plugin_action_links') );
		add_action( 'admin_menu', array(&$this, 'admin_menu') );
	}
	

	/**
	 * AskApacheWhatIsThis::activate()
	 */
	function activate()
	{
		foreach ( array('options', 'plugin') as $pn ) delete_option( "askapache_what_is_this_{$pn}" );
		$this->InitOptions();
	}



	/**
	 * AskApacheWhatIsThis::deactivate()
	 */
	function deactivate()
	{
		foreach ( array('options', 'plugin') as $pn ) delete_option( "askapache_what_is_this_{$pn}" );
	}



	/**
	 * AskApacheWhatIsThis::LoadOptions()
	 */
	function LoadOptions()
	{
		
		$this->plugin = get_option( 'askapache_what_is_this_plugin' );
		if(!$this->plugin || !is_array($this->plugin)) $this->plugin=$this->get_plugin_data();
		
		$this->options = get_option( 'askapache_what_is_this_options' );
	}



	/**
	 * AskApacheWhatIsThis::InitOptions()
	 */
	function InitOptions()
	{
		$this->plugin=$this->get_plugin_data();
		update_option( 'askapache_what_is_this_options', $this->options );
		update_option( 'askapache_what_is_this_plugin', $this->plugin );
	}



	/**
	 * AskApacheWhatIsThis::SaveOptions()
	 */
	function SaveOptions()
	{
		//error_log(__FUNCTION__.':'.__LINE__);		
		update_option( 'askapache_what_is_this_options', $this->options );
		update_option( 'askapache_what_is_this_plugin', $this->plugin );
	}



	/**
	 * AskApacheWhatIsThis::admin_menu()
	 */
	function admin_menu()
	{
		$this->LoadOptions();
		add_options_page( $this->plugin['Plugin Name'], $this->plugin['Short Name'], 'administrator', $this->plugin['page'], array(&$this, 'options_page') );
	}



	/**
	 * AskApacheWhatIsThis::plugin_action_links()
	 *
	 * @param mixed $links
	 */
	function plugin_action_links( $links )
	{
		return array_merge( array('<a href="' . admin_url($this->plugin['action']) . '">Settings</a>'), $links );
	}


	/**
	 * AskApacheWhatIsThis::shutdown()
	 *
	 * @param mixed $title
	 */
	function shutdown($title)
	{
		if(!isset($_COOKIE[LOGGED_IN_COOKIE]) || empty($_COOKIE[LOGGED_IN_COOKIE]))return $title;
		elseif( false === stripos( substr($title, 0, 5000), '<title>')) return $title;
		
		global $wp_query, $wpdb, $current_user;
		$current_user = wp_get_current_user();
		if(current_user_can('administrator') && ($type=$this->get_type())!==false ) return str_ireplace('<title>',"<title>{$type} - ", $title);
	}
	
	/**
	 * AskApacheWhatIsThis::get_type()
	 */
	function get_type()
	{
		global $wp_query, $wpdb;
		$type=array();
		
		if(!is_object($wp_query) && isset($GLOBALS['wp_query']))$wp_query&=$GLOBALS['wp_query'];
		if(!is_object($wp_query) || !function_exists('is_single')) return false;
		
		foreach(array('single','page','archive','date','year','month','day','time','author','category','tag','tax','search','feed','comment_feed','trackback','home','404','paged','admin','attachment','singular','robots','posts_page') as $k) :
			$f="is_{$k}";
			if(function_exists($f) && ($f()!==false))$type[]=$f;
		endforeach;

		if(sizeof($type)==0) return false;
		return (( $s==1 ) ? $type[0] : join(', ',$type));
	}






	/**
	 * AskApacheWhatIsThis::options_page()
	 */
	function options_page()
	{
		$this->LoadOptions();
		?>
		<div class="wrap" style="max-width:1400px;">
		<h3><?php echo $this->plugin['Plugin Name'];?> Options - <a style="font-size:12px;" href="http://feeds.askapache.com/apache/htaccess">News/Updates</a></h3>
				
		<form id="aawithis_main_settings" method="post" action="<?php echo $this->plugin['action']; ?>">
		<?php wp_original_referer_field( true, 'previous' ); wp_nonce_field( 'aawithis_whatisthis_form' ); ?>

		<p>Additional Features on the way... this is beta!</p>
		<div id="aawithis_opt1">
		</div>

		<p class="submit"><input type="submit" class="button" id="submit_aawithis_main_settings" name="submit_aawithis_main_settings" value="Save Changes &raquo;" /></p>
		</form>

		<div style="width:300px;float:left;">
		<p><br class="clear" /></p>
		<h3>Articles from AskApache</h3>
		<ul>
		<li><a href="http://www.askapache.com/seo/seo-secrets.html">SEO Secrets of AskApache.com</a></li>
		<li><a href="http://www.askapache.com/seo/seo-advanced-pagerank-indexing.html">Controlling Pagerank and Indexing</a></li>
		<li><a href="http://www.askapache.com/htaccess/apache-htaccess.html">Ultimate .htaccess Tutorial</a></li>
		<li><a href="http://www.askapache.com/seo/updated-robotstxt-for-wordpress.html">Robots.txt Info for WordPress</a></li>
		</ul>
		</div>
		</div>
		<?php
	}





	/**
	 * AskApacheWhatIsThis::get_plugin_data()
	 *
	 * @param mixed $find
	 */
	function get_plugin_data( $find = array('Description', 'Author', 'Version', 'DB Version', 'Requires at least', 'Tested up to', 'WordPress', 'Plugin', 'Plugin Name', 'Short Name', 'Domain Path', 'Text Domain', '(?:[a-z]{2,25})? URI') )
	{
		$fp = fopen( __FILE__, 'r' );
		if ( !is_resource($fp) ) return false;
		$data = fread( $fp, 1000 );
		fclose( $fp );

		$mtx = $plugin = array();
		preg_match_all( '/(' . join('|', $find) . ')\:[\s\t]*(.+)/i', $data, $mtx, PREG_SET_ORDER );
		foreach ( $mtx as $m ) $plugin[trim( $m[1] )] = str_replace( array("\r", "\n", "\t"), '', trim($m[2]) );

		$plugin['pb'] = preg_replace( '|^' . preg_quote(WP_PLUGIN_DIR, '|') . '/|', '', __FILE__ );
		$plugin['Title'] = '<a href="' . $plugin['Plugin URI'] . '" title="' . __( 'Visit plugin homepage' ) . '">' . $plugin['Plugin Name'] . '</a>';
		$plugin['Author'] = '<a href="' . $plugin['Author URI'] . '" title="' . __( 'Visit author homepage' ) . '">' . $plugin['Author'] . '</a>';
		$plugin['page'] = basename( __FILE__ );
		$plugin['hook'] = 'settings_page_' . rtrim( $plugin['page'], '.php' );
		$plugin['action'] = 'options-general.php?page=' . $plugin['page'];

		return $plugin;
	}

}
endif;


$AskApacheWhatIsThis = new AskApacheWhatIsThis();
ob_start(array(&$AskApacheWhatIsThis, 'shutdown'));
add_action('init',array(&$AskApacheWhatIsThis, 'init'));

?>