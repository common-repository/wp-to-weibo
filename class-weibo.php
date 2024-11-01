<?php
/**
* WordPress sync to Sina weibo when published a post;
* Code from https://zhangge.net/5082.html
*/
 function transition_hjyl_sync_weibo( $new_status, $old_status, $post ) {
	if ($old_status != 'publish' && $new_status == 'publish' && get_post_type( $post ) == 'post') {
		hjyl_sync_weibo($post->ID);
	}
}
function hjyl_sync_weibo($post_ID) {
	//if( wp_is_post_revision($post_ID) ) return;
   $get_post_info = get_post($post_ID);
   $get_post_centent = get_post($post_ID)->post_content;
   $get_post_title = get_post($post_ID)->post_title;
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
     $wb_num = (138 - hjyl_sync_weibo_WeiboLength($string1.$string2))*2;
     $status = $string1.mb_strimwidth(strip_tags( apply_filters('the_content', $get_post_centent)),0, $wb_num,'...').$string2;
       /* get the thumbnail, or get the first pic */ 
		$url = get_hjyl_sync_weibo_thumbnail($post_ID);
		//$url = preg_replace('/https:\/\//i','http://',$url);
        $api_url = 'https://api.weibo.com/2/statuses/share.json'; /* API */
        $body = array('status' => $status,'source' => $appkey,'pic' => $url);
       $headers = array('Authorization' => 'Basic ' . base64_encode("$username:$userpassword"));
       $result = $request->post($api_url, array('body' => $body,'headers' => $headers));
       return json_encode($result);
    }
}

?>