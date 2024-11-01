<?php
/*
 * Plugin Name: wp-to-weibo
 * Plugin URI: https://hjyl.org/wp-to-weibo
 * Description: synchronize your post to sina weibo when you publisded..在发布文章时，形成一条短微博，自动同步到新浪微博的功能，支持文章头条形式。
 * Version: 1.2
 * Author: hjyl
 * Author URI: https://hjyl.org
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wp-to-weibo
 * Domain Path: /languages
*/
//ini_set('display_errors', true);
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'HJYL_SYNC_WEIBO_PATH', plugin_dir_path( __FILE__ ) );

include_once( HJYL_SYNC_WEIBO_PATH . "class-functions.php" );

function hjyl_sync_weibo_l10n(){
	load_plugin_textdomain( 'wp-to-weibo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'hjyl_sync_weibo_l10n' );

if(get_option('hjyl_share_toutiao') == 1){
	add_action('transition_post_status', 'transition_hjyl_sync_toutiao', 10, 3);
	include_once( HJYL_SYNC_WEIBO_PATH. "class-toutiao.php" );
}else{
	include_once( HJYL_SYNC_WEIBO_PATH . "class-weibo.php" );
	add_action('transition_post_status', 'transition_hjyl_sync_weibo', 10, 3);
}

add_action('admin_menu', 'hjyl_sync_weibo_page');
function hjyl_sync_weibo_page() {
    //call register settings function
	add_action( 'admin_init', 'register_hjyl_sync_weibo_settings' );
	// Add a new submenu under Options:
    add_options_page(__('WP TO WEIBO','wp-to-weibo'), __('WP TO WEIBO','wp-to-weibo'), 'manage_options', 'wp-to-weibo', 'hjyl_sync_weibo_settings_page' );
}

function register_hjyl_sync_weibo_settings() {
	//register our settings
	register_setting( 'hjyl-sync-weibo-settings', 'hjyl_share_toutiao' );
	register_setting( 'hjyl-sync-weibo-settings', 'hjyl_appkey' );
    register_setting( 'hjyl-sync-weibo-settings', 'hjyl_name' );
    register_setting( 'hjyl-sync-weibo-settings', 'hjyl_password' );
}

function hjyl_sync_weibo_settings_page() {
    //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
?>
<div class="wrap">
<h2><?php _e('Hjyl Sync Weibo Options','wp-to-weibo'); ?></h2><br />
<div id="poststuff" class="metabox-holder has-right-sidebar">
	<div class="inner-sidebar">
		<div style="position:absolute;" class="meta-box-sortabless ui-sortable" id="side-sortables">
			<div class="postbox" id="sm_pnres">
						<h3 class="hndle"><span><?php _e('Donation','wp-to-weibo'); ?></span></h3>
						<div class="inside" style="margin:0;padding-top:10px;background-color:#ffffe0;">
								<?php printf(__('Created, Developed and maintained by %s . If you feel my work is useful and want to support the development of more free resources, you can donate me. Thank you very much!','wp-to-weibo'), '<a href="'.esc_url( __( 'https://hjyl.org/', 'wp-to-weibo' ) ).'">HJYL.ORG</a>'); ?>
									<br /><br />
									<table>
										<tr>
											<form name="_xclick" action="<?php echo esc_url( __( 'https://www.paypal.com/cgi-bin/webscr', 'wp-to-weibo' ) ); ?>" method="post">
												<input type="hidden" name="cmd" value="_xclick">
												<input type="hidden" name="business" value="i@hjyl.org">
												<input type="hidden" name="item_name" value="hjyl WordPress Theme">
												<input type="hidden" name="charset" value="utf-8" >
												<input type="hidden" name="currency_code" value="USD">
												<input type="image" src="<?php echo esc_url( __( 'https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif', 'wp-to-weibo' ) ); ?>" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
											</form>
										</tr>
										<tr>
											<img src="<?php echo esc_url( __( 'https://hilau.com/wp-content/uploads/2019/10/alipay.jpg', 'wp-to-weibo' ) ); ?>" alt="<?php _e('Alipay', 'wp-to-weibo'); ?>" />
										</tr>
									</table>
						</div>
				</div>
		</div>
	</div>

	<div class="has-sidebar-content" id="post-body-content">
		<form method="post" action="options.php">
		  <?php settings_fields( 'hjyl-sync-weibo-settings' ); ?>
			<table class="form-table">
				<tr valign="top">
				<th scope="row"><?php _e('Choose Sync Type','wp-to-weibo'); ?></th>
				<td>
					<input name="hjyl_share_toutiao" type="radio" id="hjyl_share" value="0"<?php checked(0, get_option('hjyl_share_toutiao')); ?> />
					<label class="description"><?php _e("share post", 'wp-to-weibo' ); ?></label>
					<br/>
					<input name="hjyl_share_toutiao" type="radio" id="hjyl_toutiao" value="1"<?php checked(1, get_option('hjyl_share_toutiao')); ?> />
					<label class="description"><?php _e("Headline article", 'wp-to-weibo' ); ?></label>
				</td>
				</tr>
				<tr valign="top">
				<th scope="row"><?php _e('Sina Weibo name','wp-to-weibo'); ?></th>
				<td>
					<input name="hjyl_name" type="text" id="hjyl_name" value="<?php echo get_option('hjyl_name'); ?>" class="regular-text" />
				</td>
				</tr>
				<tr valign="top">
				<th scope="row"><?php _e('Sina Weibo password','wp-to-weibo'); ?></th>
				<td>
					<input name="hjyl_password" type="password" id="hjyl_password" value="<?php echo get_option('hjyl_password'); ?>" class="regular-text" />
				</td>
				</tr>
				<tr valign="top">
				<th scope="row"><?php _e('Sina Weibo appkey','wp-to-weibo'); ?></th>
				<td>
					<input name="hjyl_appkey" type="text" id="hjyl_appkey" value="<?php echo get_option('hjyl_appkey'); ?>" class="regular-text" />
				</td>
				</tr>
			</table>


		  <p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
</div>
</div>



<?php
}

?>