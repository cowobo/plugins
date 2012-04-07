<?php
class wp_fb_comments
{
	protected $facebook;
	protected $options;
	protected $base_url;
	protected $scope="publish_stream,manage_pages,offline_access";
	protected $oauth_url;
	static $logs_name ;
	var $wpdb;
	static $option_name = 'wp_fb_comments_option';
	function __construct()
	{
		global $wpdb;
		$this->wpdb=$wpdb;
		
		self::$logs_name=$wpdb->prefix."wpfb_logs";
		$this->options = get_option(self::$option_name);
		$this->base_url = plugins_url('',__FILE__)."/";
		$this->facebook = new Facebook
		(
			array
			(
			  'appId'  => $this->options['app_id'],
			  'secret' => $this->options['app_secret'],
			  'cookie' => true
			)
		);
		$this->oauth_url= $this->get_facebook_url() . 'oauth/authorize?client_id=' . $this->options['app_id'] . '&scope='. $this->scope . '&redirect_uri=' . $this->base_url . 'token.php';
		$this->init();
	}
	function init()
	{
		add_filter('http_request_timeout',array($this,'mytimeout'));
		add_action('publish_post', array($this,'comments_share'));
		add_action('comment_post', array($this,'push_comments'));
		add_action('wp_fb_comments_daily', array($this,'daily_cron'));
		add_action('wp_fb_comments_hourly', array($this,'hourly_cron'));
		if(!wp_next_scheduled('wp_fb_comments_daily'))
		wp_schedule_event(time()+4600, "daily", 'wp_fb_comments_daily');
		if(!wp_next_scheduled('wp_fb_comments_hourly'))
		wp_schedule_event(time()+3600, "hourly", 'wp_fb_comments_hourly');
	}
	function get_facebook_url($key='graph')
	{
		return Facebook::$DOMAIN_MAP[$key];
	}
	
	#Cowobo: check if it's in the category watchlist
	function cowobo_sync_cat($post_ID) 
	{
		if (empty($this->options['cats'])) return false;
		foreach($this->options['cats'] as $value) {
			$post_cats = get_the_category($post_ID);
			foreach ($post_cats as $post_cat)
				if ($value == $post_cat->slug) return true;
		}
	}

	#CoWoBo: image attachment
	function get_first_image($postID) {
		$images = get_children(array('post_parent' => $postID, 'numberposts' => 1, 'post_mime_type' =>'image'));
		if(empty($images)) return false;
		$images = current($images);
		$src = wp_get_attachment_image_src($images->ID, $size = 'medium');
		return $src[0];
	}
	
	function comments_share($post_ID)
	{
		//Check if already shared
		$fbpostid = get_post_meta($post_ID,"fbpostid",TRUE);
		//Don't allow resharing
		if( ! $fbpostid && $this->cowobo_sync_cat($post_ID))
		{
			$post=get_post($post_ID,ARRAY_A);
			$title=$post['post_title'];
			$expt=$post['post_excerpt'];
			if(!$expt)
				$expt=$post['post_content'];
			if(strlen($expt)>300)
				$expt=substr($expt,0,300)."...";
			$permalink = get_permalink( $post_ID);
			$image = $this->get_first_image($post_ID);
			$update =  array
			(                        
				'name'          => "$title",
				'link'          => "$permalink",
				'picture'	=> "$image",
				'description'   => "$expt",
			);
			$update['access_token']=$this->options['ptoken'];
			//print_r($update);
			try
			{			
				$ret_code=$this->facebook->api('/'.$this->options['page_id'].'/feed', 'POST', $update);
				add_post_meta($post_ID,"fbpostid",$ret_code['id'],TRUE);
				$this->save_log("Sharing post",$post_ID,0,$ret_code['id'],"Success");
			}
			catch (FacebookApiException $e) 
			{ 
				$result = $e->getResult();
				$err_msg  = isset($result['error']) ? $result['error']['message'] : $result['error_msg'];
				$this->save_log("Sharing post",$post_ID,0,$ret_code['id'],"Error returned from FB".$err_msg);
			}
		}
	}
	function push_comments($comment_id)
	{
		$comment = get_comment($comment_id, ARRAY_A);
		$fbpostid = get_post_meta($comment['comment_post_ID'],"fbpostid",TRUE);
		if($fbpostid && $this->cowobo_sync_cat($post_ID))
		{
			$update['message']='On http://cowobo.org/, '.$comment['comment_author']." says\n".$comment['comment_content'];
			$update['access_token']=$this->options['ptoken'];
			try
			{
				$ret_code=$this->facebook->api('/'.$fbpostid.'/comments', 'POST', $update);
				add_comment_meta($comment_id,"fbcommentid",$ret_code['id'],TRUE);
				$this->save_log("Pushing comments",$comment['comment_post_ID'],$comment_id,$fbpostid,"Success");
			}
			catch (FacebookApiException $e) 
			{ 
				$result = $e->getResult();
				$err_msg  = isset($result['error']) ? $result['error']['message'] : $result['error_msg'];
				$this->save_log("Pushing comments",$comment['comment_post_ID'],$comment_id,$ret_code['id'],"Error returned from FB".$err_msg);
			}
			
		}
	}
	function pull_comments($post_id)
	{
		$fbpostid = get_post_meta($post_id,"fbpostid",TRUE);
		if($fbpostid)
		{
			$update['access_token']=$this->options['ptoken'];
			try{
					$ret_code=$this->facebook->api('/'.$fbpostid.'/comments', 'GET', $update);
				}
			catch (FacebookApiException $e) { 
					$result = $e->getResult();
					$err_msg  = isset($result['error']) ? $result['error']['message'] : $result['error_msg'];
					$this->save_log("Pulling comments",$post_id,0,$fbpostid,"Error returned from FB".$err_msg);
											}
			$ret_code=$ret_code['data'];
			if($ret_code)
			foreach($ret_code as $rows)
			{
				$querystr="select * from ".$this->wpdb->commentmeta." where meta_key='fbcommentid' AND meta_value='".$rows['id']."'";
				$temp=$this->wpdb->get_results($querystr, ARRAY_A);
				if(sizeof($temp)>0)
				continue;
				$data = array
				(
					'comment_post_ID' => $post_id,
					'comment_author' => $rows['from']['name'],
					'comment_author_email' => '',
					'comment_author_url' => '',
	   				'comment_content' => $rows['message'],
					'comment_parent' => 0,
					'user_id' => 1,
					'comment_author_IP' => '127.0.0.1',
	   				'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
					'comment_date' => $rows['created_time'],
					'comment_date_gmt' => $rows['created_time'],
					'comment_approved' => 1,
				);
				$commentid=wp_insert_comment( $data );
				if($commentid)
				{
					add_comment_meta($commentid,"fbcommentid",$rows['id'],TRUE);
					$this->save_log("Pulling comments",$post_id,$commentid,$rows['id'],"Success");
				}
				else
					$this->save_log("Pulling comments",$post_id,$commentid,$rows['id'],"Error with Wp comment insert");
			}
		}
	}
	function daily_cron()
	{
		 $querystr = "
    		SELECT wposts.* 
   			FROM ".$this->wpdb->posts." wposts, ".$this->wpdb->postmeta." wpostmeta
    		WHERE wposts.ID = wpostmeta.post_id 
   			AND wpostmeta.meta_key = 'fbpostid'
    		AND wposts.post_status = 'publish' 
   			AND wposts.post_type = 'post' 
   			ORDER BY wposts.post_date DESC";

 		$pageposts = $this->wpdb->get_results($querystr, ARRAY_A);
		if($pageposts)
		foreach($pageposts as $posts)
		$this->pull_comments($posts['ID']);

	}
	function hourly_cron()
	{
		 $querystr = "
    		SELECT wposts.* 
   			FROM ".$this->wpdb->posts." wposts, ".$this->wpdb->postmeta." wpostmeta
    		WHERE wposts.ID = wpostmeta.post_id 
   			AND wpostmeta.meta_key = 'fbpostid'
    		AND wposts.post_status = 'publish' 
   			AND wposts.post_type = 'post' 
   			ORDER BY wposts.post_date DESC LIMIT 0,10";

 		$pageposts = $this->wpdb->get_results($querystr, ARRAY_A);
		if($pageposts)
		foreach($pageposts as $posts)
		$this->pull_comments($posts['ID']);

	}
	function save_log($action,$post_id,$comment_id,$fbid,$msg)
	{
		$time=time();
		$rows_affected = $this->wpdb->insert( self::$logs_name, array( 'time' =>$time,'postid' => $post_id,'commentid' => $comment_id,'fbid' =>$fbid, 'action' => $action, 'msg' => $msg) );
	}
	function display_logs()
	{
		$rows=$this->wpdb->get_results("select * from ".self::$logs_name." order by time DESC limit 0,10",ARRAY_A);
		foreach($rows as $row)
		{
			$i++;
			$var.="<tr";
			if($i%2)
				$var.=" class=\"alternate\"";
			$var.="><td>$i</td><td>".$row['action']."</td><td>".$row['postid']."</td><td>".$row['commentid']."</td><td>".$row['fbid']."</td><td>".$row['msg']."</td><td>".date(DATE_RFC822,$row['time'])."</td></tr>";
		}
		return $var;
	}

	function mytimeout()
	{
		return 30;
	}
	function get_option( $key = 'all')
	{
		if($key=='all')
			return $this->options;
		elseif(array_key_exists($key,$this->options))
			return $this->options[$key];
	}
	function set_option( $key, $value )
	{
		$this->options[$key] = $value;
		update_option(self::$option_name,$this->options);
	}
	function get_base_url()
	{
		return $this->base_url;
	}
	function get_facebook()
	{
		return $this->facebook;
	}
}
