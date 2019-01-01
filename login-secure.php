<?php
/*
Plugin Name: 		Login Secure
Description: 		Secure login from unauthorized users. Blocks default WordPress login URLs and requires a special code in WordPress Login URL.
Plugin URI:			http://rizwanabbasi.com/login-secure/
Author:				Rizwan Abbasi
Author URI:			http://rizwanabbasi.com
Version: 			1.0
Tags:				brute force, secure login, security, custom login url, block login url, safety, unauthorized access
*/
//this function will execute on login page
add_action('login_form_login', 'login_secure');
function login_secure()
{	
	if(strpos($_SERVER["SCRIPT_NAME"],'wp-login.php')!== FALSE)
	{		
		$siteurl = site_url();
		$value = get_option('stringurl');
		if((!isset($_GET[$value])) && ($value!=""))
			header("Location:$siteurl");
	}//if
}

//creating an Options Page. These functions are for plugin settings page.
add_action('admin_menu', 'login_secure_register_options_page');
function login_secure_register_options_page() {
  add_options_page('Login Secure', 'Login Secure', 'manage_options', 'login-secure', 'login_secure_options_page');
}

function login_secure_options_page()
{
  //content on settings page goes here
  //check the privileges of the current user
  if(!current_user_can('manage_options'))
		wp_die(__('You do not have sufficient permissions to access this page.') );
  echo  "<h2>Login Secure</h2>
		<form method='post'>";
  //adding nonce for security purposes
  ?>
  <input type="hidden" name="login_secure_nonce" value="<?php echo wp_create_nonce('login-secure-nonce'); ?>"/>
  <div>
  <?php
  //sanitize the value fetched from options table
  $value = sanitize_text_field(get_option('stringurl'));
  ?>
  <p>Enter a unique string here to secure your WordPress login page.</p>
  <input type="text" id="login_secure_string" name="login_secure_string" value="<?php echo esc_attr($value); ?>" />
  <?php submit_button(); ?>
  <p><em><strong>Login URL:</strong> 
  <?php 
	if($value == "")
		echo esc_url(site_url()) . "/wp-login.php";
	else
		echo esc_url(site_url()) . "/wp-login.php?" .  $value; ?></em></p>
  </form>
  </div>
<?php
} //login_secure_options_page()

//this function verifies nonce and processes the form data
add_action('init', 'login_secure_process_form_data');
function login_secure_process_form_data() 
{
	if(isset($_POST['login_secure_nonce'])) 
	{
		if(wp_verify_nonce($_POST['login_secure_nonce'], 'login-secure-nonce')) 
		{
			// Nonce verified successfully, process form here			
			$login_secure_string = "";
  			//performing data validation on the input field value
			if(isset($_POST['login_secure_string']))
			{	 
				//sanitizing the text field value
				$login_secure_string = sanitize_text_field($_POST['login_secure_string']);
				//add the field to 'options' table in the database
				update_option('stringurl', $login_secure_string);
			} //if(isset)
			header("Location:options-general.php?page=login-secure");
		}//if(wp_verify_nonce)
		else 
		{
			//nonce verification failed
			wp_die(__('You do not have sufficient permissions to access this page.') );
		} //else
	} // main if(isset)
}//login_secure_process_form_data()
?>