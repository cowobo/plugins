<?php

include("../../../wp-load.php");
//error_reporting(E_ALL);
$wp_fb_token_url = $wp_fb_comments_instance->get_facebook_url('graph')."oauth/access_token"."?client_id="
    . $wp_fb_comments_instance->get_option('app_id') . "&redirect_uri=" . urlencode($wp_fb_comments_instance->get_base_url()."token.php") . "&client_secret="
    . $wp_fb_comments_instance->get_option('app_secret') . "&code=" . $_REQUEST["code"];
$wp_fb_get_token = wp_remote_get($wp_fb_token_url);
if($wp_fb_get_token['response']['code']=='200')
{
	$wp_fb_comments_instance->set_option('token',str_replace('access_token=','',$wp_fb_get_token['body']));
	$wp_fb_comments_pages = $wp_fb_comments_instance->get_facebook()->api('/me/accounts',array('access_token'=>$wp_fb_comments_instance->get_option('token')));
	$wp_fb_comments_user = $wp_fb_comments_instance->get_facebook()->api('/me',array('access_token'=>$wp_fb_comments_instance->get_option('token')));
	if( isset( $wp_fb_comments_user[ 'id' ] ) )
		$wp_fb_comments_instance->set_option('uid',$wp_fb_comments_user['id']);
		$wp_fb_comments_instance->set_option('name',$wp_fb_comments_user['name']);
	if( is_array( $wp_fb_comments_pages ) )
		foreach($wp_fb_comments_pages['data'] as $wp_fb_comments_page)
			if( $wp_fb_comments_page['id'] == $wp_fb_comments_instance->get_option( 'page_id') )
			{
				$wp_fb_comments_instance->set_option('ptoken',$wp_fb_comments_page['access_token']);
				break;
			}
	
}
else
{
	echo "Error: ",$wp_fb_get_token['response']['code']," : ",$wp_fb_get_token['response']['message'];
}
wp_redirect(admin_url('admin.php?page=wp-fb-comments'));
