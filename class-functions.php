<?php
/*
//get weibo string length 
*/
function hjyl_sync_weibo_WeiboLength($str)
{
    $arr = hjyl_sync_weibo_split_zh($str);   //let string to array
    foreach ($arr as $v){
        $temp = ord($v);        //to ASCII
        if ($temp > 0 && $temp < 127) {
            $len = $len+0.5;
        }else{
            $len ++;
        }
    }
    return ceil($len);        //to integer
}
/*
//split string only for gb2312
//form http://u-czh.iteye.com/blog/1565858
*/
function hjyl_sync_weibo_split_zh($tempaddtext){
    $tempaddtext = iconv("UTF-8", "GBK//IGNORE", $tempaddtext);
    $cind = 0;
    $arr_cont=array();
    for($i=0;$i<strlen($tempaddtext);$i++)
    {
        if(strlen(substr($tempaddtext,$cind,1)) > 0){
            if(ord(substr($tempaddtext,$cind,1)) < 0xA1 ){ //get byte if english
                array_push($arr_cont,substr($tempaddtext,$cind,1));
                $cind++;
            }else{
                array_push($arr_cont,substr($tempaddtext,$cind,2));
                $cind+=2;
            }
        }
    }
    foreach ($arr_cont as &$row)
    {
        $row=iconv("gb2312","UTF-8",$row);
    }
    return $arr_cont;
}
 
/**
* WordPress get post image By zhangge.net
*/
  function get_hjyl_sync_weibo_thumbnail($post_ID){
     if (has_post_thumbnail()) {
            $timthumb_src = wp_get_attachment_image_src( get_post_thumbnail_id($post_ID), 'full' ); 
            $url = $timthumb_src[0];
    } else {
        if(!$post_content){
            $post = get_post($post_ID);
            $post_content = $post->post_content;
        }
        preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', do_shortcode($post_content), $matches);
        if( $matches && isset($matches[1]) && isset($matches[1][0]) ){       
            $url =  $matches[1][0];
        }else{
            $url =  '';
        }
    }
    return $url;
  }
?>