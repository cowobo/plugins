<?php

class wp_fb_comments_admin extends wp_fb_comments
{
	function init()
	{
		parent::init();
		add_action( 'fb_merge_home', array($this,'home'));
		register_setting( 'wp_fb_comments_main', 'wp_fb_comments_option', array( $this ,'validate') );
		add_settings_section( 'wp_fb_comments_section', 'App Settings', array( $this , 'settings_pre'), 'wp-fb-comments');
		add_settings_field( 'wp_fb_app_id', 'Application id', array( $this ,'app_id_field'), 'wp-fb-comments', 'wp_fb_comments_section');
		add_settings_field( 'wp_fb_app_key', 'API key', array( $this ,'app_key_field'), 'wp-fb-comments', 'wp_fb_comments_section' );
		add_settings_field( 'wp_fb_app_secret', 'Application secret', array( $this ,'app_secret_field'), 'wp-fb-comments', 'wp_fb_comments_section' );
		add_settings_field( 'wp_fb_page_id', 'Facebook Page id', array( $this ,'page_id_field'), 'wp-fb-comments', 'wp_fb_comments_section' );
		add_settings_field( 'wp_fb_cats', 'Cowobo: Sync Categories', array( $this ,'cowobo_cat_field'), 'wp-fb-comments', 'wp_fb_comments_section' );
		//add_submenu_page("fb-merge", "WP-FB Comments", "WP-FB Comments", 'manage_options', "wp-fb-comments", array( $this, "admin_page" ) );
		//add_submenu_page("cowobo-social", "WP-FB Comments", "WP-FB Comments", 'manage_options', "wp-fb-comments", array( $this, "admin_page" ) );
		if(strlen($this->options['ptoken'])==0)
			add_action( 'admin_notices', array( $this ,'activation_notice' ));
	}
	function activation_notice()
	{
?>
	<div class="error fade">
		<p>
			<strong>
				WordPress Facebook Comments Integration must be configured. 
			</strong><br />
			Go to <a href="<?php echo admin_url( 'admin.php?page=wp-fb-comments' ); ?>">the config page</a> configure the plugin.<br /> 
			Make sure you fill everything click
			<strong>
				Save Changes
			</strong> and then
			<strong>
				Connect Admin FBacc
			</strong>
		</p>
	</div>
<?php
	}
	function home()
	{
	?>
	<div id="poststuff" class="metabox-holder">
		<div class="stuffbox">
			<h3>WordPress Facebook Comment Integration </h3>
			<div class="inside">
				<p>
					Integrates Facebook comments on WordPress. <strong>For instructions <a class="thickbox" title="More info" href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin=wp-fb-comments&TB_iframe=true&width=640' )?>">Click here</a> </strong>
				</p>
				<h4>Crons: </h4> 
				<p>
					<b>Hourly :</b> <?php echo date(DATE_RFC822,wp_next_scheduled('wp_fb_comments_hourly'));?><br />
					<b>Daily :</b> <?php echo date(DATE_RFC822,wp_next_scheduled('wp_fb_comments_daily'));?><br />
					<b>Current Time :</b> <?php echo date(DATE_RFC822,wp_next_scheduled('wp_fb_comments_daily'));?><br />
				</p>
				<p>
				<h4>Logs: </h4>
				<table class="widefat">
		<thead>
			<tr>
				<th>Slno</th>
				<th>Action</th>
				<th>Associated Post Id</th>
				<th>Associated Comment Id</th>
				<th>Associated FB Id</th>
				<th>Message</th>
				<th>Time of Action</th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th>Slno</th>
				<th>Action</th>
				<th>Associated Post Id</th>
				<th>Associated Comment Id</th>
				<th>Associated FB Id</th>
				<th>Message</th>
				<th>Time of Action</th>
			</tr>
		</tfoot>
		<tbody>
			<?php echo $this->display_logs() ?>
		</tbody>
			
		</table>
				</p>
			</div>
		</div>
	</div>	
	<?php
	}
	static function activate()
	{
		global $wpdb;
		if( ! is_array( get_option( self::$option_name ) ) )
			update_option
			(
				'wp_fb_comments_option',
				array
				(
					'app_id'		=> '',
					'app_key'		=> '',
					'app_secret'	=> '',
					'page_id'		=> '',
					'name'			=> '',
					'uid'			=> '',
					'token'			=> '',
					'ptoken'		=> '',
					#CoWoBo: extra option for category filter
					'cats'			=> ''
				)
			);			

		
		
		      $table_name =$wpdb->prefix . "wpfb_logs";
	 		 if($wpdb->get_var("show tables like '$table_name'") != $table_name) { 
      		$sql = "CREATE TABLE " . $table_name . " (
	  		id mediumint(9) NOT NULL AUTO_INCREMENT,
	  		time bigint(11) DEFAULT '0' NOT NULL,
	  		action varchar(220) NOT NULL,
	  		postid bigint(20) DEFAULT '0' NOT NULL,
	  		commentid bigint(20) DEFAULT '0' NOT NULL,
	  		fbid varchar(70) DEFAULT '0' NOT NULL,
	  		msg varchar(220) NOT NULL,
	  		PRIMARY KEY  (id)
			);";

      		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      		dbDelta($sql);
	  		$rows_affected = $wpdb->insert( $table_name, array( 'time' =>time(), 'action' => 'installing', 'msg' => 'Plugin installed!!') );
			add_option("wpfb_db_version", $wpfb_db_version);
   			}
	}
	static function deactivate()
	{
		
       	global $wpdb;
		$table_name =$wpdb->prefix . "wpfb_logs";
	  	$sql = "DROP TABLE " . $table_name . ";";
		$wpdb->query($sql);
		wp_clear_scheduled_hook('wp_fb_comments_daily');
		wp_clear_scheduled_hook('wp_fb_comments_hourly');
	}
	
	
	
	function settings_pre()
	{
		echo '<p>You must have <a href="http://www.facebook.com/developers" target="_blank">Facebook developer</a> added to your account before you can create an APP.</p>';
	}
	function app_id_field() 
	{
?>
		<input id='wp_fb_app_id' name='<?php echo self::$option_name; ?>[app_id]' size='40' type='text' value='<?php echo $this->options['app_id']; ?>' /> <a href="http://www.facebook.com/developers/createapp.php" target="_blank">Create one?</a>
<?php
	}
	function app_key_field() 
	{
		echo "<input id='wp_fb_app_key' name='".self::$option_name."[app_key]' size='40' type='text' value='{$this->options['app_key']}' />";
	}
	function app_secret_field() 
	{
		echo "<input id='wp_fb_app_secret' name='".self::$option_name."[app_secret]' size='40' type='text' value='{$this->options['app_secret']}' />";
	}
	function page_id_field() 
	{
		?>
		<input id='wp_fb_page_id' name='<?php echo self::$option_name; ?>[page_id]' size='40' type='text' value='<?php echo $this->options['page_id']; ?>' /> <a href="http://www.facebook.com/pages/create.php" target="_blank">Create one?</a>
		<?php
	}
	# CoWoBo mod
	function cowobo_cat_field() 
	{
		echo '<ul>';
		$categories= get_categories(array('hide_empty' => 0)); 
		foreach ($categories as $category) {
			$option = '<li><input type="checkbox" name="'.self::$option_name.'[cats][]" value="'.$category->slug.'" ';
			if (!empty($this->options['cats']))
				foreach ($this->options['cats'] as $value)
					if ($value == $category->slug) $option.= "checked";
			$option.= '><label>'.$category->cat_name.'</label>';
			$option .= ' ('.$category->category_count.')</li>';
			echo $option;
		}
		echo '</ul>';
	}	
	function validate($input)
	{
		// clean for script tags
		$this->options['app_id'] = wp_filter_nohtml_kses($input['app_id']);
		$this->options['app_secret'] = wp_filter_nohtml_kses($input['app_secret']);
		$this->options['app_key'] = wp_filter_nohtml_kses($input['app_key']);
		$page_id = wp_filter_nohtml_kses($input['page_id']);
		//Do any checks with page_id		
		$this->options['page_id'] = $page_id;
		// CoWoBo
		$this->options['cats'] = $input['cats'];
		return $this->options;
	}
	
	function admin_page()
	{

	?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2>WordPress Facebook Comments Settings</h2>
	<h4>Crons: </h4> 
	<p>
		<b>Hourly :</b> <?php echo date(DATE_RFC822,wp_next_scheduled('wp_fb_comments_hourly'));?><br />
		<b>Daily :</b> <?php echo date(DATE_RFC822,wp_next_scheduled('wp_fb_comments_daily'));?><br />
		<b>Current Time :</b> <?php echo date(DATE_RFC822,wp_next_scheduled('wp_fb_comments_daily'));?><br />
	</p>
		<h4>Links: </h4> 
	<p>
		<a href="http://wordpress.org/extend/plugins/wp-fb-comments/installation/">INSTALLATION GUIDE</a><br />
		<a href="http://wordpress.org/extend/plugins/wp-fb-comments/faq/">FAQ</a><br />
		<a href="http://wordpress.org/extend/plugins/wp-fb-comments/errors/">Common Errors</a><br />
		<a href="http://wordpress.org/tags/wp-fb-comments?forum_id=10">SUPPORT FORUM</a><br />
	</p>
	<form action="options.php" method="post">
	<?php settings_fields('wp_fb_comments_main'); ?>

	<?php do_settings_sections('wp-fb-comments'); ?>
	<br />
	<input name="Submit" class="button-primary" type="submit" value="<?php _e('Save Changes'); ?>" />
	</form>
	<br />
	
	<script type="text/javascript">
	function redirectPage()
	{
		window.location = "<?php echo $this->oauth_url;?>";
	}
	</script>
	<table class="form-table">
			<tr><td>FB CANVAS url</td>
			<td><input id="canvas_url" name="canvas_url" onclick="select()" type="text" size="150" maxlength="100" value='<?php echo($this->base_url); ?>' readonly="true"/></td></tr>
			<tr><td>FB Admin name</td>
			<td><input  type="text" size="150" maxlength="100" value='<?php echo($this->options['name']); ?>' readonly="readonly"/></td></tr>
			<tr><td>FB Admin UID</td>
			<td><input  type="text" size="150" maxlength="100" value='<?php echo($this->options['uid']); ?>' readonly="readonly"/></td></tr>
			<tr><td>FB Admin token</td>
			<td><input  type="text" size="150" maxlength="100" value='<?php echo($this->options['token']); ?>' readonly="readonly"/></td></tr>
			<tr><td>FB Admin Page token</td>
			<td><input  type="text" size="150" maxlength="100" value='<?php echo($this->options['ptoken']); ?>' readonly="readonly"/></td></tr>
	</table>
			<input class="button-primary" type="button" onclick="redirectPage()"value="<?php _e('Connect Admin FBacc') ?>" />
	<?php
	}
}

