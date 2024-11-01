<?php
/**
* WordPress sync to Sina weibo headline title when published a post;
* Code from https://zhangge.net/5082.html
*/
 function transition_hjyl_sync_toutiao( $new_status, $old_status, $post ) {
	if ($old_status != 'publish' && $new_status == 'publish' && get_post_type( $post ) == 'post') {
		hjyl_sync_toutiao($post->ID);
	}
}
function hjyl_sync_toutiao($post_ID) {
	//if( wp_is_post_revision($post_ID) ) return;
   $get_post_info = get_post($post_ID);
   $get_post_centent = get_post($post_ID)->post_content;
   $get_post_title = get_post($post_ID)->post_title;
   $get_the_excerpt = get_post($post_ID)->post_excerpt;
   if(!empty(get_post_format($post_ID))){
	if(get_post_format($post_ID) == 'aside') $get_post_format = __('aside','wp-to-weibo');
	if(get_post_format($post_ID) == 'gallery') $get_post_format =  __('gallery','wp-to-weibo');
	if(get_post_format($post_ID) == 'link') $get_post_format =  __('link','wp-to-weibo');
	if(get_post_format($post_ID) == 'image') $get_post_format =  __('image','wp-to-weibo');
	if(get_post_format($post_ID) == 'quote') $get_post_format =  __('quote','wp-to-weibo');
	if(get_post_format($post_ID) == 'status') $get_post_format =  __('status','wp-to-weibo');
	if(get_post_format($post_ID) == 'video') $get_post_format =  __('video','wp-to-weibo');
	if(get_post_format($post_ID) == 'audio') $get_post_format =  __('audio','wp-to-weibo');
	if(get_post_format($post_ID) == 'chat') $get_post_format = __('chat','wp-to-weibo');
   }else{
	$get_post_format = __('Headline', 'wp-to-weibo');
   }
   if ($get_post_info->post_status == 'publish') {
       $appkey = get_option('hjyl_appkey');
       $username = get_option('hjyl_name');
       $userpassword = get_option('hjyl_password');
       $request = new WP_Http;
       $keywords = ""; 
 
       /* keywords of post */
       $tags = wp_get_post_tags($post_ID);
       foreach ($tags as $tag ) {
          $keywords = sprintf(__(' %1$s # %2$s # ', 'wp-to-weibo'), $keywords, $tag->name);$keywords.'#'.$tag->name."#";
       }
 
      /* add keywords to post topic*/
     $string1 = sprintf(__('【 %1$s 】 %2$s : ','wp-to-weibo'), strip_tags($get_post_format), strip_tags( $get_post_title ));
     $string2 = sprintf(__('%1$s Read More %2$s', 'wp-to-weibo'), $keywords, get_permalink($post_ID));
 
     /* post num, sync failed if more */
     $status = $string1.$get_post_centent.$string2;
       /* get the thumbnail, or get the first pic */ 
		$url = get_hjyl_sync_weibo_thumbnail($post_ID);
		//$url = preg_replace('/https:\/\//i','http://',$url);
        $api_url = 'https://api.weibo.com/proxy/article/publish.json'; 
        $body = array('title' => $get_post_title, 'text' => $get_the_excerpt, 'summary' => $get_the_excerpt, 'content' => $status, 'source' => $appkey, 'cover' => $url);
       $headers = array('Authorization' => 'Basic ' . base64_encode("$username:$userpassword"));
       $result = $request->post($api_url, array('body' => $body,'headers' => $headers));
       return json_encode($result);
    }
}

?>