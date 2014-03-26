<?php

/* Plugin Name: FACEBOOK STAR RATING LIGHT
 * Plugin URI: http://www.intelligent-it.asia
 * Description: <strong>Import the Star rating &#10032;&#10032;&#10032;&#10032;&#10032; from your Facebook Page and display it on your WordPress blog.</strong>
 * Version: 1.0
 * Author: Intelligent IT
 * Author URI: http://www.intelligent-it.asia
 * @author Henry Krupp <henry.krupp@gmail.com> 
 * @copyright 2013 Intelligent IT 
 * @license http://www.php.net/license/3_01.txt PHP License 3.01 
 */


global $comparison,$c,$a,$ck,$ct,$headers,$fb_star_template;

register_activation_hook( __FILE__, 'fbstars_activate' );
register_deactivation_hook( __FILE__, 'fbstars_deactivation' );

/**
 * register styles only for posts with shortcode
 */
add_filter('the_posts', 'conditionally_add_fbs_style'); 
function conditionally_add_fbs_style($posts){
	if (empty($posts)) return $posts;
 
	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
	foreach ($posts as $post) {
		if (stripos($post->post_content, '[fb_stars]') !== false) {
			$shortcode_found = true; // bingo!
			break;
		}
	}
 
	if ($shortcode_found) {
		// enqueue here
		wp_enqueue_style( 'fbs-style', plugins_url('css/fb_stars.css', __FILE__));
	}
 
	return $posts;
}

/**
 * register styles for backend
 */
add_action( 'admin_enqueue_scripts', 'add_fbs_stylesheet_to_admin' );
function add_fbs_stylesheet_to_admin( $page ) {
	if( @$_GET['page']=="fbs-setting-admin")
	{
		wp_enqueue_style( 'fbs-style', plugins_url('css/fb_stars.css', __FILE__));
		wp_enqueue_style( 'fbs-comparision-table', plugins_url('css/style.css', __FILE__));
	}
		return;
}

//register default options on activation
function fbstars_activate() {
global $fb_star_template;
    //initialize plugin
	$fbs_options = get_option('fbs_options');
	if(null == get_option('fbs_options')){
			//defaults
			$fbs_options['fbs_page_name'] = 'your Facebook page name';
			$fbs_options['fbs_page_url'] = 'the URL to your Facebook page';
			$fbs_options['fbs_stars'] = '';
			$fbs_options['fbs_rating_text'] = '1';
			$fbs_options['fbs_rating_text_style'] = 'font-weight:normal;padding-left:5px;position:relative;color:grey;font-size:11px;';
			update_option('fbs_options', $fbs_options);
		}else{
			$x=1;
		}
	if(null == get_option('fbs_stars')){
		add_option('fbs_stars',$fb_star_template);
	}

}

/**
* remove settings on deactivation
*/
function fbstars_deactivation()
{
	delete_option( 'fbs_options' );
    delete_option( 'fbs_stars' );
}

class FBSSettingsPage
{	
    protected $option_name = 'fbs_options';
	
	//default settings
    protected $data = array(
		'fbs_page_name' => 'off',
		'fbs_page_url' => '',
		'fbs_stars' => '',
		'fbs_rating_text' => '1',
		'fbs_rating_text_style' => 'font-weight:normal;padding-left: 5px;position:relative;color:grey;font-size:12px;',
    );
	
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_fbs_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'fbs_page_init' ) );
		
    }

    /**
     * Add options page
     */
    public function add_fbs_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Facebook Rating', 
            'manage_options', 
            'fbs-setting-admin', 
            array( $this, 'create_fbs_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_fbs_admin_page()	
    { 	global $comparison;
	
			$logo = '<table>
				<tbody>
					<tr valign="middle">
						<td align="right">
							<a href="http://intelligent-it.asia/product/facebook-star-rating-light/" title="FACEBOOK STAR RATING Light by intelligent-it.asia"><img src="'.plugins_url( 'assets/images/facebook_star_Light_icon.png',__file__).'"  alt="FACEBOOK STAR RATING BRONZE"/></a> 
						</td>
						<td class="forminp" align="right"> 
						FACEBOOK STAR RATING LIGHT made by</br>
							<a href="http://intelligent-it.asia" title="intelligent-it.asia"><img alt="'. __('FACEBOOK STAR RATING LIGTH Plugin was brought to you by intelligent-it.asia.','FBS_Light').'" src="'.plugins_url('assets/images/intelligent-it-logo.png',__file__).'" /></a></br>
							The IT you deserve.
						</td>
						<td align="right">
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="889GLQNHSY2LG">
							<input type="image" src="https://www.paypalobjects.com/en_US/CH/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
						</td>
					</tr>
				</tbody>
				</table>';

		$fbs_plugin_file =  __FILE__ ;
		$fbs_data=get_plugin_data( $fbs_plugin_file);
		$currentsystem = $fbs_data['Name' ]." ".$fbs_data['Version' ];


        $this->options = get_option( 'fbs_options' );
		
		if (isset($this->options['fbs_page_url'])){
			$this->options['fbs_stars']=mysql_real_escape_string(get_fbs_stars($this->options['fbs_page_url']));
			}else{
				$this->options['fbs_stars']='Please register an existing Facebook Page with existing rating!';
			}
        ?>
        <div class="wrap">
            <?php screen_icon();?>
            <h2><?php echo $currentsystem?></h2>           
            <form method="post" action="options.php">
            <?php
                settings_fields( 'fbs_option_group' );   
                do_settings_sections( 'fbs-setting-admin' );
                submit_button(); 
			?>
            </form>
			<?php echo $logo.$comparison;?>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function fbs_page_init()
    {        
        register_setting(
            'fbs_option_group', // Option group
            'fbs_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'fbs_setting_section', // ID
            'Facebook ID', // Title
            array( $this, 'print_section_info' ), // Callback
            'fbs-setting-admin' // Page
        );  

        add_settings_field(
            'fbs_page_name', 
            'Facebook Page Name', 
            array( $this, 'fb_id_callback' ), 
            'fbs-setting-admin', 
            'fbs_setting_section'
        );      

		add_settings_field(
			'fbs_page_url',
			'Facebook Page URL',
			array( $this, 'select_fb_page'),
			'fbs-setting-admin',
			'fbs_setting_section'
		);		
		add_settings_field(
			'fbs_stars',
			'Facebook Stars',
			array( $this, 'show_fbs_stars'),
			'fbs-setting-admin',
			'fbs_setting_section'
		);				
		add_settings_field(
			'fbs_rating_text',
			'Rating text',
			array( $this, 'rating_text_callback'),
			'fbs-setting-admin',
			'fbs_setting_section'
		);	
		add_settings_field(
			'fbs_rating_text_style',
			'Rating text style',
			array( $this, 'rating_text_style_callback'),
			'fbs-setting-admin',
			'fbs_setting_section'
		);	
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

        if( isset( $input['fbs_page_name'] ) )
            $new_input['fbs_page_name'] = sanitize_text_field( $input['fbs_page_name'] );
        if( isset( $input['fbs_rating_text_style'] ) )
			$new_input['fbs_rating_text_style'] = sanitize_text_field($input['fbs_rating_text_style']);
        if( isset( $input['fbs_page_name'] ) )
			$new_input['fbs_page_url'] = sanitize_text_field($input['fbs_page_url']);
		$new_input['fbs_rating_text'] = $input['fbs_rating_text'];
		$new_input['fbs_stars'] = $input['fbs_stars'];
		

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fb_id_callback()
    {
        printf(
            '<input type="text" id="fbs_page_name" size="50" name="fbs_options[fbs_page_name]" value="%s" required/>',
            isset( $this->options['fbs_page_name'] ) ? esc_attr( $this->options['fbs_page_name']) : ''
        );
		
    }
	public function select_fb_page() {

		printf(
            '<input type="text" id="fbs_page_url" size="50" name="fbs_options[fbs_page_url]" value="%s" required/>',
            isset( $this->options['fbs_page_url'] ) ? esc_attr( $this->options['fbs_page_url']) : ''
        );
	}		

	public function show_fbs_stars() {
	$stars = html_entity_decode(nl2br(stripslashes($this->options['fbs_stars'])));
	echo $stars;
	echo "<br>Use <code>[fb_stars]</code> as shortcode in your page, posts, or sidebar";
	} 

    public function rating_text_callback()
    {
		echo '<input name="fbs_options[fbs_rating_text]" id="fbs_rating_text" type="checkbox" value="1" class="code" ' . checked( 1, $this->options['fbs_rating_text'], false ) . ' /> Rating text';

    }
    public function rating_text_style_callback()
    {
		echo '<input name="fbs_options[fbs_rating_text_style]" id="fbs_rating_text_style" type="textarea" value="'.$this->options['fbs_rating_text_style'].'" class="code"  style=";width:430px;" /><br> Use CSS to style the rating text.';

    }	
}

if( is_admin() )
    $my_settings_page = new FBSSettingsPage();

/**
 * load template
 */	
$fb_star_template = file_get_contents(dirname(__FILE__) .'/assets/fb_star_template/fb_star_template.txt'); 	//read fb_star_template
//fclose($this->fb_star_template);	//close fb_star_template 

	
/**
* FaceBookStar Shortcode
*/
function fb_star_shortcode( $atts, $content = null ) {
	$stars = html_entity_decode(nl2br(stripslashes(get_option('fbs_stars','na'))));
	return  $stars;
}
add_shortcode( 'fb_stars', 'fb_star_shortcode' );

$comparison='<table id="comparetable" class="blackbold form-table fbs_info">
           <tr>
				<td class="blank"><strong>Facebook Star Plugin Feature Comparison</strong></td>

					<th><a href="http://intelligent-it.asia/product/facebook-star-rating-bronze/" title="FACEBOOK STAR RATING LIGHT"><img class="fbs_icons" src="'. plugins_url( 'assets/images/facebook_star_light_icon.png',__file__).'" /><span>LIGHT</span></a></th>
					<th><a href="http://intelligent-it.asia/product/facebook-star-rating-bronze/" title="FACEBOOK STAR RATING BRONZE"><img class="fbs_icons" src="'. plugins_url( 'assets/images/facebook_star_bronze_icon.png',__file__).'" height="50px" /><span>BRONZE</span></a></th>
					<th><a href="http://intelligent-it.asia/product/facebook-star-rating-silver/" title="FACEBOOK STAR RATING SILVER"><img class="fbs_icons" src="'. plugins_url( 'assets/images/facebook_star_silver_icon.png',__file__).'" height="50px" /><span>SILVER</span></a></th>
					<th><a href="http://intelligent-it.asia/product/facebook-star-rating-gold/" title="FACEBOOK STAR RATING GOLD"><img class="fbs_icons" src="'. plugins_url( 'assets/images/facebook_star_gold_icon.png',__file__).'" height="50px" /><span>GOLD</span></a></th>
				 </tr>
				<tr>
				<td class="rowTitle">Custom fetch limit</td>    
					  <td class="no"></td>
					  <td class="no"></td>
					  <td class="no"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>
                <td class="rowTitle">Custom Star Picture</td>    
                      
					  <td class="no"></td>
					  <td class="no"></td>
					  <td class="no"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>
                <td class="rowTitle">Custom Star size    </td>    
                      
					  <td class="no"></td>
					  <td class="no"></td>
					  <td class="no"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>
                <td class="rowTitle">Custom Rating Language</td>    
                      
					  <td class="no"></td>
					  <td class="no"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>
                <td class="rowTitle">Custom update interval</td>    
                      
					  <td class="no"></td>
					  <td class="no"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>
                <td class="rowTitle">Multiple Facebook page ratings</td>    
                      
					  <td class="no"></td>
					  <td class="no"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>
                <td class="rowTitle">Automatic update</td>    
                      
					  <td class="no"></td>
					  <td class="yes">twice daily</td>
					  <td class="yes">variable</td>
					  <td class="yes">variable</td>
					</tr>
		   <tr>
                <td class="rowTitle">Last update indicator</td>    
                      
					  <td class="no"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>
				<td class="rowTitle">Rating text</td>    
                      
					  <td class="yes"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>
                <td class="rowTitle">Custom rating text style</td>    
                      
					  <td class="yes"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					</tr>		   
		   <tr>
				<td class="rowTitle">Single Facebook page rating</td>    
                      
					  <td class="yes"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>
                <td class="rowTitle">Shortcode</td>    
                      
					  <td class="yes"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					  <td class="yes"></td>
					</tr>
		   <tr>         
          </table>';
		 
	/**
	 * fetch the FB content
	 * 
	 * @return string        Modified content
	 */
	function get_fbs_stars($url){
		//camouflage
		global $headers;
		$options = get_option('fbs_options');
		$rand_header=$headers[rand(0,count($headers)-1)];
		$http_options = array(
		  'http'=>array(
			'method'=>"GET",
			'header'=>"Accept-language: ".get_option( "fbs_rating_language", "en" )."\r\nUser-Agent: " .$rand_header
			)
		);
		$context = stream_context_create($http_options);
		$content = @file_get_contents($url, false, $context);
			
		if (!$content === false) {	//valid  URL

			//fetch rating size
			$star_rating = array();
			$pattern = '/style="clip:\Wrect\(\d{1,2}px,\W(\d{1,2})px,\W\d{1,2}px,\W\d{1,2}px\)/i';
			preg_match($pattern, $content, $star_rating);
			//stars
			if (isset($star_rating[1])) {
					//scale stars
					$rating_scaling=$star_rating[1]; 

					//fetch rating text
					$rt = array();
					$pattern = '/<div.class="_6a._5wfv">(.*?)<\/div>+/';
					preg_match($pattern, $content, $rt);
					$rating_text= $rt[1];
				}else{
					$rating_scaling=0; //no rating found
					$rating_text="";
				}

			global $fb_star_template;
			
			//template replacements
			//#fb_url#
			$html_in = $fb_star_template;
			$html_out = preg_replace('/#fb_url#/i', $options['fbs_page_url'], $html_in);
			//#fb_name#
			$html_out = preg_replace('/#fb_name#/i', $options['fbs_page_name'], $html_out );		
			
			//#star_rating#
			$html_out = preg_replace('/#star_rating#/i', $rating_scaling, $html_out);
			
			//#star_size#
			$html_out = str_replace('#star_width#',14,$html_out);
			$html_out = str_replace('#star_height#',16,$html_out);
				
			//#rating-text# //<div class="_6a _rating_text" style="#rating_text_style#">'.$rating_text[1].'</div>
			if ($options['fbs_rating_text']=="1"){
				$html_out = str_replace('#rating-text#','<div class="_rating_text" style="#rating_text_style#">'.$rating_text.'</div>',$html_out);//_6a 
			}else{
				$html_out = str_replace('#rating-text#',"",$html_out);
			};

			//#grey_star#
			$image_attributes = plugins_url('assets/images/fbs_grey_star.png', __FILE__);
			$html_out = str_replace('#grey_star#',$image_attributes,$html_out);

			//#blue_star#
			$image_attributes = plugins_url('assets/images/fbs_blue_star.png', __FILE__);
			$html_out = str_replace('#blue_star#',$image_attributes,$html_out);

			//#rating_text_style	#
			$rating_text_style	 = $options['fbs_rating_text_style'];
			$html_out = str_replace('#rating_text_style#',$rating_text_style,$html_out);
					
			update_option('fbs_stars',$html_out);
			return $html_out;
		
		} else { //invalid URL
			return false;
		}
	}	
		 
$headers=array("Mozilla/1.22 (compatible; MSIE 10.0; Windows 3.1)",
"Mozilla/4.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; MS-RTC LM 8; InfoPath.3; .NET4.0C; .NET4.0E) chromeframe/8.0.552.224",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Zune 3.0)",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; msn OptimizedIE8;ZHCN)",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; InfoPath.2)",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; InfoPath.3; .NET4.0C; .NET4.0E; .NET CLR 3.5.30729; .NET CLR 3.0.30729; MS-RTC LM 8)",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; Media Center PC 6.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C)",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; Media Center PC 6.0; InfoPath.2; MS-RTC LM 8",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; Media Center PC 6.0; InfoPath.2; MS-RTC LM 8)",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:25.0) Gecko/20100101 Firefox/25.0",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:21.0) Gecko/20100101 Firefox/21.0",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:24.0) Gecko/20100101 Firefox/24.0",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1290.1 Safari/537.13",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1290.1 Safari/537.13",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1309.0 Safari/537.17",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36",
"Mozilla/5.0 (Windows NT 5.0; rv:21.0) Gecko/20100101 Firefox/21.0",
"Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36",
"Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.16 Safari/537.36",
"Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20100101 Firefox/21.0",
"Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20130331 Firefox/21.0",
"Mozilla/5.0 (Windows NT 5.1; rv:21.0) Gecko/20130401 Firefox/21.0",
"Mozilla/5.0 (Windows NT 6.0; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0",
"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1468.0 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.60 Safari/537.17",
"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1623.0 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0",
"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20130330 Firefox/21.0",
"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20130331 Firefox/21.0",
"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:21.0) Gecko/20130401 Firefox/21.0",
"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20130406 Firefox/23.0",
"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:16.0.1) Gecko/20121011 Firefox/21.0.1",
"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:22.0) Gecko/20130328 Firefox/22.0",
"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:23.0) Gecko/20131011 Firefox/23.0",
"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:25.0) Gecko/20100101 Firefox/25.0",
"Mozilla/5.0 (Windows NT 6.1; rv:14.0) Gecko/20100101 Firefox/18.0.1",
"Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20100101 Firefox/21.0",
"Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20130328 Firefox/21.0",
"Mozilla/5.0 (Windows NT 6.1; rv:21.0) Gecko/20130401 Firefox/21.0",
"Mozilla/5.0 (Windows NT 6.1; rv:22.0) Gecko/20130405 Firefox/22.0",
"Mozilla/5.0 (Windows NT 6.1; rv:6.0) Gecko/20100101 Firefox/19.0",
"Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1290.1 Safari/537.13",
"Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1464.0 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1467.0 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.13 (KHTML, like Gecko) Chrome/24.0.1290.1 Safari/537.13",
"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.14 (KHTML, like Gecko) Chrome/24.0.1292.0 Safari/537.14",
"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.15 (KHTML, like Gecko) Chrome/24.0.1295.0 Safari/537.15",
"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.2 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.17 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1667.0 Safari/537.36",
"Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:16.0.1) Gecko/20121011 Firefox/21.0.1",
"Mozilla/5.0 (Windows NT 6.2; Win64; x64; rv:21.0.0) Gecko/20121011 Firefox/21.0.0",
"Mozilla/5.0 (Windows NT 6.2; Win64; x64;) Gecko/20100101 Firefox/20.0",
"Mozilla/5.0 (Windows NT 6.2; rv:21.0) Gecko/20130326 Firefox/21.0",
"Mozilla/5.0 (Windows NT 6.2; rv:22.0) Gecko/20130405 Firefox/22.0",
"Mozilla/5.0 (Windows NT 6.2; rv:22.0) Gecko/20130405 Firefox/23.0",
"Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))",
"Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)",
"Mozilla/5.0 (X11; CrOS i686 3912.101.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.116 Safari/537.36",
"Mozilla/5.0 (X11; CrOS i686 4319.74.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.57 Safari/537.36",
"Mozilla/5.0 (X11; Linux i686; rv:21.0) Gecko/20100101 Firefox/21.0",
"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0",
"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20130331 Firefox/21.0",
"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:24.0) Gecko/20100101 Firefox/24.0",
"Mozilla/5.0 (compatible; MSIE 10.0; Macintosh; Intel Mac OS X 10_7_3; Trident/6.0)",
"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)",
"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)",
"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)",
"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
"Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0",
"Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 5.0; Trident/4.0; FBSMTWB; .NET CLR 2.0.34861; .NET CLR 3.0.3746.3218; .NET CLR 3.5.33652; msn OptimizedIE8;ENUS)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.0; Trident/4.0; InfoPath.1; SV1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 3.0.04506.30)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; SLCC1; .NET CLR 1.1.4322)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 1.1.4322)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.2; Trident/4.0; Media Center PC 4.0; SLCC1; .NET CLR 3.0.04320)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; .NET CLR 2.7.58687; SLCC2; Media Center PC 5.0; Zune 3.4; Tablet PC 3.6; InfoPath.3)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; InfoPath.1; SV1; .NET CLR 3.8.36217; WOW64; en-US)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)",
"Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; GTB7.4; InfoPath.2; SV1; .NET CLR 3.3.69573; WOW64; en-US)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/4.0; GTB7.4; InfoPath.3; SV1; .NET CLR 3.1.76908; WOW64; en-US)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0; chromeframe/11.0.696.57)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/4.0; GTB7.4; InfoPath.1; SV1; .NET CLR 2.8.52393; WOW64; en-US)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0) chromeframe/10.0.648.205",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; FunWebProducts)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; .NET CLR 1.1.4322; .NET4.0C; Tablet PC 2.0)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; chromeframe/11.0.696.57)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; chromeframe/13.0.782.215)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; yie8)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Zune 4.0; InfoPath.3; MS-RTC LM 8; .NET4.0C; .NET4.0E)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; chromeframe/12.0.742.112)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Zune 4.0; Tablet PC 2.0; InfoPath.3; .NET4.0C; .NET4.0E)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 7.1; Trident/5.0");