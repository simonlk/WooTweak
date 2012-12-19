<?php
/*
Plugin Name: WooTweak 2
Plugin URI: 
Description: WooCommerce Tweaker 2
Author: Dark Delphin
Version: 1.1
Author URI: 
*/

class WooTweak2 {
     
    public $options;
    
    public $billing_array = array(
				'billing_first_name',
				'billing_last_name',
				'billing_company',
				'billing_address_1',
				'billing_address_2',
				'billing_city',
				'billing_postcode',
				'billing_country',
				'billing_state',
				'billing_email',
				'billing_phone'
				  );
    public $shipping_array = array(
				'shipping_first_name',
				'shipping_last_name',
				'shipping_company',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_city',
				'shipping_postcode',
				'shipping_country',
				'shipping_state'
				   );
    public $order_array = array(
				'order_comments'
				);
     
    function __construct()
    {
    $o = get_option('WooTweak2_options');

	add_action('admin_init', array($this, 'wt2_init'));

	add_action('admin_menu', array($this, 'add_pages'));

	add_filter( 'woocommerce_checkout_fields' , array($this, 'wt2_override_checkout_fields'));
	
	add_action( 'woocommerce_before_checkout_form', array($this, 'wt2_checkout_form_width_function'));
	
	add_action('woocommerce_after_single_product_summary', array($this, 'wt2_remove_tabs_in_product_details'), 1);
	add_action('woocommerce_after_single_product_summary', array($this, 'wt2_remove_panels_in_product_details'), 2);
	
	add_action('woocommerce_before_add_to_cart_form', array($this, 'wt2_variations_enhancement'));
	
	add_action('woocommerce_after_add_to_cart_button', array($this, 'wt2_variations_enhancement_meta_table'));
	
	// In Admin panel
	add_action('woocommerce_product_write_panel_tabs', array($this, 'wt2_variations_description_tab'));
	add_action('woocommerce_product_write_panels', array($this, 'wt2_variations_description_tab_fields'));
	add_action('woocommerce_process_product_meta_variable', array($this, 'wt2_variations_description_tab_fields_process'));

	add_action('woocommerce_product_tabs', array($this, 'wt2_variations_tab'));
	add_action('woocommerce_product_tab_panels', array($this, 'wt2_variations_panel'));
	
	add_action('woocommerce_init', array($this, 'wt2_tweak_shop_manager_role'));
	add_action('woocommerce_init', array($this, 'wt2_use_wp_pagenavi_func'));
	add_action('woocommerce_init', array($this, 'wt2_remove_related_products_on_product_page'));
	
	add_action('woocommerce_init', array($this, 'wt2_show_sorting_feild_before_products'));
	
	add_filter('single_add_to_cart_text', array($this,'wt2_custom_addtocart_button_text_func'));
	add_filter('add_to_cart_text', array($this,'wt2_custom_addtocart_button_text_func'));
	
	add_action('admin_notices', array($this,'wt2_admin_notice'));

	add_action('plugins_loaded', array($this, 'wt2_translate'));

	add_action( 'wp_before_admin_bar_render', array($this, 'wt2_remove_admin_bar_links') );

	add_action('get_header',array($this, 'wt2_remove_woo_commerce_generator_tag'));

		if($o['wt2_disable_cart_functions'])
		{
			// Remove cart button from the product loop
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10, 2);
			 
			// Remove cart button from the product details page
			// remove_action( 'woocommerce_before_add_to_cart_form', 'woocommerce_template_single_product_add_to_cart', 10, 2);
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			 
			//disabled actions (add to cart, checkout and pay)
			remove_action( 'init', 'woocommerce_add_to_cart_action', 10);
			remove_action( 'init', 'woocommerce_checkout_action', 10 );
			remove_action( 'init', 'woocommerce_pay_action', 10 );
		}
    }
    
    function wt2_init()
    {
	//delete_option('WooTweak2'); // use to clear previous data if needed
	$this->options = get_option('WooTweak2_options');
	$this->reg_settings_and_fields();
	
	// add_filter( 'woocommerce_checkout_fields' ,'wt2_override_checkout_fields');
    }
    function wt2_translate()
    {
    // $locale = get_locale();
    // load_textdomain( 'WooTweak2', dirname( plugin_basename( __FILE__ ) ) . '/languages/'.$locale.'.mo' );
    load_plugin_textdomain( 'WooTweak2', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );	
    // load_plugin_textdomain( 'WooTweak2', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
    function add_pages()
    {
	//add_options_page('Page Title', 'Menu Title', 'administrator', __FILE__, array('WooTweak2', 'display_options_page'));
	$page = add_submenu_page('woocommerce', 'WooTweak', 'WooTweak', 'administrator', __FILE__, array('WooTweak2', 'display_options_page'));
	
	add_action('admin_print_styles-' . $page, array($this, 'wt2_admin_scripts'));
    }
    function wt2_admin_notice()
    {
	if(isset($_GET['page']) && $_GET['page'] == 'wootweak/wootweak2.php' && isset($_GET['settings-updated']) && $_GET['settings-updated'] == true)
	{
	    ?>
	    <div id="message" class="updated fade"><p><?php echo __( 'Your settings have been saved.', 'woocommerce' ); ?></p></div>
	    <?php
	}
    }
    function wt2_admin_scripts()
    {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-tabs');
	// wp_register_style('jquery-ui-tabs', plugins_url('/css/jquery.ui.tabs.css', __FILE__) );
	if ( 'classic' == get_user_option( 'admin_color' ) ) {
        wp_register_style ( 'wootweak-jquery-ui-css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui-classic.css' );
    } else {
        wp_register_style ( 'wootweak-jquery-ui-css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui-fresh.css' );
    }
    wp_enqueue_style('wootweak-jquery-ui-css');
	wp_enqueue_style('jquery-ui-tabs');
	//wp_register_style('jquery-ui-base', plugins_url('/css/jquery.ui.base.css', __FILE__) );
	//wp_enqueue_style('jquery-ui-base');
	//wp_register_style('jquery-ui-theme', plugins_url('/css/jquery.ui.theme.css', __FILE__) );
	//wp_enqueue_style('jquery-ui-theme');
    }
     
    
    function display_options_page()
    {
    ?>
    
    <div class="wrap">
	<?php //screen_icon();
	$o = get_option('WooTweak2_options');
	//echo '<pre>';
	//print_r($o);
	//echo '</pre>';
	?>
	<script>
	    jQuery(document).ready(function($) {
		var count = 0;
		var ul = '<ul>';
		$('h3').each(function(){
		    ul = ul + '<li><a href="#tabs-'+count+'">'+$(this).text()+'</a></li>';
		    $(this).next('table').andSelf().wrapAll('<div class="tab" id="tabs-'+count+'"/>');
		    count++;
		});
		ul = ul + '</ul><div style="clear:both;">';
		$('.tab').wrapAll('<div id="tabs" />');
		$('#tabs').prepend(ul).tabs();
	    });
	</script>
	<div class="icon32" style="background-image: url(<?php echo plugins_url(); ?>/woocommerce/assets/images/icons/woocommerce-icons.png)!important; background-position: -359px -6px;"><br></div>
	<h2><?php echo __('Settings', 'woocommerce'); ?></h2>
	<form method="post" action="options.php" enctype="multipart/form-data">
	<?php settings_fields('WooTweak2_plugin_options_group'); ?>
	<?php do_settings_sections(__FILE__); ?>
	
	<p class="submit">
	    <input type="submit" name="submit" value="<?php echo __('Save changes', 'woocommerce'); ?>" class="button-primary"  />
	</p>
	</form>
    </div>
    <?php
    }
     
    function reg_settings_and_fields()
    {
    register_setting('WooTweak2_plugin_options_group', 'WooTweak2_options', array($this, 'WooTweak2_validate_settings')); //3rd param optional callback func
    add_settings_section('WooTweak2_main_section', __('General Options', 'woocommerce'), array($this, 'WooTweak2_main_section_cb'), __FILE__); //id, title, callback, page
    
    add_settings_section('WooTweak2_order_section', __('Checkout Page', 'woocommerce').' - '.__('Billing', 'woocommerce'), array($this, 'WooTweak2_order_section_cb'), __FILE__); //id, title, callback, page
    add_settings_section('WooTweak2_shipping_section', __('Checkout Page', 'woocommerce').' - '.__('Shipping', 'woocommerce'), array($this, 'WooTweak2_shipping_section_cb'), __FILE__); //id, title, callback, page
    add_settings_section('WooTweak2_order_comments_section', __('Checkout Page', 'woocommerce').' - '.__('Customer Notes', 'woocommerce'), array($this, 'WooTweak2_order_comments_section_cb'), __FILE__); //id, title, callback, page
    
    // ADD ALL add_settings_field FUNCTIONS HERE
    add_settings_field('wt2_disable_tabs_on_product_page', __('Disable tabs on product page', 'WooTweak2'), array($this,'wt2_disable_tabs_on_product_page_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
    add_settings_field('wt2_disable_tabs_on_product_page_float_description', __('Float description to right', 'WooTweak2'), array($this,'wt2_disable_tabs_on_product_page_float_description_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
    add_settings_field('wt2_disable_product_attributes_show', __('Disable attributes on product page', 'WooTweak2'), array($this,'wt2_disable_product_attributes_show_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
    add_settings_field('wt2_checkout_form_width', __('One column checkout form', 'WooTweak2'), array($this,'wt2_checkout_form_width_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
    add_settings_field('wt2_variations_descriptions', __('Add description field for variantions', 'WooTweak2'), array($this,'wt2_variations_descriptions_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
    add_settings_field('wt2_variations_tab_on_product_page', __('Add variations tab on product page', 'WooTweak2'), array($this,'wt2_variations_tab_on_product_page_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
    add_settings_field('wt2_use_wp_pagenavi', __('Use WP PageNavi plugin for pagination (if installed and active)', 'WooTweak2'), array($this,'wt2_use_wp_pagenavi_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
    add_settings_field('wt2_show_sort_before_products', __('Show sorting field before products', 'WooTweak2'), array($this,'wt2_show_sort_before_products_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
    add_settings_field('wt2_custom_addtocart_button_text', __('Custom text for "Add to Cart" button (Single product)', 'WooTweak2'), array($this,'wt2_custom_addtocart_button_text_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
	add_settings_field('wt2_manage_pages', __('Remove edit pages capability from "Shop Manager" role', 'WooTweak2'), array($this,'wt2_manage_pages_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
	add_settings_field('wt2_disable_dashbord_logo_menu', __('Disable logo menu in admin dashboard'), array($this,'wt2_disable_dashbord_logo_menu_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
	add_settings_field('wt2_disable_checkout_fields_customization', __('Disable checkout fields customization','WooTweak2'), array($this,'wt2_disable_checkout_fields_customization_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
	add_settings_field('wt2_remove_related_products_on_product_page', __('Remove related products on product page'), array($this,'wt2_remove_related_products_on_product_page_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section
	add_settings_field('wt2_disable_cart_functions', __('Disable cart funcionality to simulate catalog'), array($this,'wt2_disable_cart_functions_generate_field'), __FILE__, 'WooTweak2_main_section'); // id, title, cb func, page , section


    foreach($this->billing_array as $item)
	{
	    $name = str_replace('billing_','', $item);
	    $name = str_replace('_',' ',$name);
	    $name = ucwords($name);
	    add_settings_field('wt2_disabled_'.$item, __($name, 'woocommerce'), array($this,'wt2_billing_disabled_fields'), __FILE__, 'WooTweak2_order_section',$item); // id, title, cb func, page , section
	}
    foreach($this->shipping_array as $item)
	{
	    $name = str_replace('shipping_','', $item);
	    $name = str_replace('_',' ',$name);
	    $name = ucwords($name);
	    add_settings_field('wt2_disabled_'.$item, __($name, 'woocommerce'), array($this,'wt2_billing_disabled_fields'), __FILE__, 'WooTweak2_shipping_section',$item); // id, title, cb func, page , section
	}
    foreach($this->order_array as $item)
	{
	    	    add_settings_field('wt2_disabled_'.$item, __('Order Notes', 'woocommerce'), array($this,'wt2_billing_disabled_fields'), __FILE__, 'WooTweak2_order_comments_section',$item); // id, title, cb func, page , section
	}
    }
     
    function WooTweak2_main_section_cb(){// Optional
	}
    function WooTweak2_order_section_cb(){// Optional
	}
    function WooTweak2_shipping_section_cb(){// Optional
	}
    function WooTweak2_order_comments_section_cb(){// Optional
	}
    function WooTweak2_validate_settings($plugin_options)
    {
	return $plugin_options;
    }
     
    // Input functions *************************************************************************************************************************
     
    function wt2_disable_tabs_on_product_page_generate_field()
    {
	$checked = ( 1 == $this->options['wt2_disable_tabs_on_product_page'] ) ? 'checked="checked"' : '' ;
	echo '<input name="WooTweak2_options[wt2_disable_tabs_on_product_page]" type="checkbox" value="1" '.$checked.'>';
    }
    
    function wt2_disable_tabs_on_product_page_float_description_generate_field()
    {
	$o = get_option('WooTweak2_options');
	$enabled = ($o['wt2_disable_tabs_on_product_page']) ? '' : ' disabled="disabled"' ;
	$checked = ( 1 == $this->options['wt2_disable_tabs_on_product_page_float_description'] ) ? ' checked="checked"' : '' ;
	echo '<input name="WooTweak2_options[wt2_disable_tabs_on_product_page_float_description]" type="checkbox" value="1"'.$checked.''.$enabled.'>';
    }
    
    function wt2_disable_product_attributes_show_generate_field()
    {
	$checked = ( 1 == $this->options['wt2_disable_product_attributes_show'] ) ? 'checked="checked"' : '' ;
	echo '<input name="WooTweak2_options[wt2_disable_product_attributes_show]" type="checkbox" value="1" '.$checked.'>';
    }
    
    function wt2_checkout_form_width_generate_field()
    {
	$checked = ( 1 == $this->options['wt2_checkout_form_width'] ) ? 'checked="checked"' : '' ;
	echo '<input name="WooTweak2_options[wt2_checkout_form_width]" type="checkbox" value="1" '.$checked.'>';
    }
	 
    function wt2_billing_disabled_fields($item)
    {	
	    $o = get_option('WooTweak2_options');
	    $enabled = ($o['wt2_disabled_'.$item]) ? ' disabled="disabled"' : '' ;
	    $checked = ( 1 == $this->options['wt2_disabled_'.$item] ) ? 'checked="checked"' : '' ;
	    echo '<label><input name="WooTweak2_options[wt2_disabled_'.$item.']" type="checkbox" value="1" '.$checked.'> '.__('Disabled', 'WooTweak2').'</label>&nbsp;&nbsp;&nbsp;';
	    $checked2 = ( 1 == $this->options['wt2_required_'.$item] ) ? ' checked="checked"' : '' ;
	    echo '<label><input name="WooTweak2_options[wt2_required_'.$item.']" type="checkbox" value="1"'.$checked2.''.$enabled.'> '.__('Required', 'WooTweak2').'</label>';
    }
    
    function wt2_variations_descriptions_generate_field()
	{
	$checked = ( 1 == $this->options['wt2_variations_descriptions'] ) ? 'checked="checked"' : '' ;
	echo '<input name="WooTweak2_options[wt2_variations_descriptions]" type="checkbox" value="1" '.$checked.'>';
	}
	
    function wt2_variations_tab_on_product_page_generate_field()
	{
	$o = get_option('WooTweak2_options');
	$enabled = ($o['wt2_variations_descriptions']) ? '' : ' disabled="disabled"' ;
	$checked = ( 1 == $this->options['wt2_variations_tab_on_product_page'] ) ? 'checked="checked"' : '' ;
	echo '<input name="WooTweak2_options[wt2_variations_tab_on_product_page]" type="checkbox" value="1" '.$checked.''.$enabled.'>';
	}
	
    function wt2_use_wp_pagenavi_generate_field()
	{
	    if(is_plugin_active('wp-pagenavi/wp-pagenavi.php'))
	    {
	    $checked = ( 1 == $this->options['wt2_use_wp_pagenavi'] ) ? 'checked="checked"' : '' ;
	    echo '<input name="WooTweak2_options[wt2_use_wp_pagenavi]" type="checkbox" value="1" '.$checked.'>';
	    }
	}
    
    function wt2_show_sort_before_products_generate_field()
	{
	$checked = ( 1 == $this->options['wt2_show_sort_before_products'] ) ? 'checked="checked"' : '' ;
	echo '<input name="WooTweak2_options[wt2_show_sort_before_products]" type="checkbox" value="1" '.$checked.'>';
	}
	
    function wt2_custom_addtocart_button_text_generate_field()
	{
	echo '<input name="WooTweak2_options[wt2_custom_addtocart_button_text]" type="text" value="'.$this->options['wt2_custom_addtocart_button_text'].'">';
	}

	function wt2_manage_pages_generate_field()
	    {
		$checked = ( 1 == $this->options['wt2_manage_pages'] ) ? 'checked="checked"' : '' ;
		echo '<input name="WooTweak2_options[wt2_manage_pages]" type="checkbox" value="1" '.$checked.'>';
	    }
	
	function wt2_disable_dashbord_logo_menu_generate_field()
	     {
	 	$checked = ( 1 == $this->options['wt2_disable_dashbord_logo_menu'] ) ? 'checked="checked"' : '' ;
	 	echo '<input name="WooTweak2_options[wt2_disable_dashbord_logo_menu]" type="checkbox" value="1" '.$checked.'>';
	     }

	function wt2_disable_checkout_fields_customization_generate_field()
	     {
	 	$checked = ( 1 == $this->options['wt2_disable_checkout_fields_customization'] ) ? 'checked="checked"' : '' ;
	 	echo '<input name="WooTweak2_options[wt2_disable_checkout_fields_customization]" type="checkbox" value="1" '.$checked.'>';
	     }

	 function wt2_remove_related_products_on_product_page_generate_field()
	 {
	 	$checked = ( 1 == $this->options['wt2_remove_related_products_on_product_page'] ) ? 'checked="checked"' : '' ;
	 	echo '<input name="WooTweak2_options[wt2_remove_related_products_on_product_page]" type="checkbox" value="1" '.$checked.'>';
	 }

	  function wt2_disable_cart_functions_generate_field()
	  {
	  	$checked = ( 1 == $this->options['wt2_disable_cart_functions'] ) ? 'checked="checked"' : '' ;
	  	echo '<input name="WooTweak2_options[wt2_disable_cart_functions]" type="checkbox" value="1" '.$checked.'>';
	  } 
    
    // Disable (hide) tabs on product page *************************************************************************************************************************
    
    function wt2_remove_tabs_in_product_details()
    {
	    $o = get_option('WooTweak2_options');
	    if($o['wt2_disable_tabs_on_product_page'])
	    {
	    remove_action( 'woocommerce_product_tabs', 'woocommerce_product_description_tab');
	    remove_action( 'woocommerce_product_tabs', 'woocommerce_product_attributes_tab');
	    remove_action( 'woocommerce_product_tabs', 'woocommerce_product_reviews_tab');
	    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs');
	    }
	    
	    if($o['wt2_disable_product_attributes_show'])
	    {
	    remove_action( 'woocommerce_product_tabs', 'woocommerce_product_attributes_tab', 20);
	    }
    }

    // Remove related products on product page *************************************************************************************************************************

    function wt2_remove_related_products_on_product_page()
    {
    	$o = get_option('WooTweak2_options');
    	if($o['wt2_remove_related_products_on_product_page'])
    	{
    		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    	}
    }
    
    // Output all product info on one panel without tabs *************************************************************************************************************************
    
    function wt2_remove_panels_in_product_details()
    {
	    global $woocommerce, $post, $product;
	    
	    $o = get_option('WooTweak2_options');
	    
	    if($o['wt2_disable_product_attributes_show'])
	    {
		remove_action( 'woocommerce_product_tab_panels', 'woocommerce_product_attributes_panel', 20);		
	    }
	    
	    if($o['wt2_disable_tabs_on_product_page'])
	    {
		remove_action( 'woocommerce_product_tab_panels', 'woocommerce_product_description_panel');
		remove_action( 'woocommerce_product_tab_panels', 'woocommerce_product_attributes_panel');
		remove_action( 'woocommerce_product_tab_panels', 'woocommerce_product_reviews_panel');
		?>
		
		<?php $float = ($o['wt2_disable_tabs_on_product_page_float_description']) ? ' class="product_description"' : '' ; ?>
		<div<?php echo $float; ?>>
		<?php
		
		$heading = apply_filters('woocommerce_product_description_heading', __('Product Description', 'woocommerce'));
		
		echo '<h2>'.$heading.'</h2>'; 
		
		the_content();
			
		if(!$o['wt2_disable_product_attributes_show'])
		{
		    $heading = apply_filters('woocommerce_product_additional_information_heading', __('Additional Information', 'woocommerce'));
		    
		    echo '<h2>'.$heading.'</h2>'; 
		    
		    $product->list_attributes();
		}
		if($o['wt2_variations_descriptions'])
		{
		?>
		<br>
		<table class="shop_attributes" id="wt2_variation_meta">
		    <tbody>
			<tr class="">
			    <th><?php echo __('SKU', 'woocommerce'); ?></th>
			    <td id="wt2_var_meta_sku"></td>
			</tr>
			<tr class="alt">
			    <th><?php echo __('Weight', 'woocommerce'); ?></th>
			    <td id="wt2_var_meta_weight"></td>
			</tr>
			<tr class="">
			    <th><?php echo __('Dimensions', 'woocommerce'); ?></th>
			    <td id="wt2_var_meta_dimentions"></td>
			</tr>
			<?php
			$o = get_option('WooTweak2_options');
			if($o['wt2_variations_descriptions'])
			{
			    ?>
			    <tr class="alt">
				<th><?php echo __('Description', 'woocommerce'); ?></th>
				<td id="wt2_var_meta_description"></td>
			    </tr>
			    <?php
			}
			?>
		    </tbody>
		</table>
		<?php
		}
		comments_template();
		
		?>
		</div>
		<?php
	    }
    }
    
    
    // Manage checkout fields *************************************************************************************************************************
    
    function wt2_override_checkout_fields( $fields )
    {
	$o = get_option('WooTweak2_options');
	
	if(!$o['wt2_disable_checkout_fields_customization'])
	{
		foreach($this->billing_array as $item)
		{
		    if($o['wt2_required_'.$item])
		    {
			$fields['billing'][$item]['required'] = true;
		    }
		    else if(!$o['wt2_required_'.$item])
		    {
			$fields['billing'][$item]['required'] = false;
		    }
		    
		    if($o['wt2_disabled_'.$item])
		    {
			unset($fields['billing'][$item]);
		    }
		}
		foreach($this->shipping_array as $item)
		{
		    if($o['wt2_required_'.$item])
		    {
			$fields['shipping'][$item]['required'] = true;
		    }
		    else if(!$o['wt2_required_'.$item])
		    {
			$fields['shipping'][$item]['required'] = false;
		    }
		    if($o['wt2_disabled_'.$item])
		    {
			unset($fields['shipping'][$item]);
		    }
		}
		foreach($this->order_array as $item)
		{
		    if($o['wt2_required_'.$item])
		    {
			$fields['order'][$item]['required'] = true;
		    }
		    else if(!$o['wt2_required_'.$item])
		    {
			$fields['order'][$item]['required'] = false;
		    }
		    if($o['wt2_disabled_'.$item])
		    {
			unset($fields['order'][$item]);
		    }
		
		}	
	}
		// $fields['billing']['billing_company']['placeholder'] = __('Company Name', 'woocommerce');
		// $fields['billing']['billing_address_2']['placeholder'] = __('Address 2', 'woocommerce');
		// $fields['shipping']['shipping_company']['placeholder'] = __('Company Name', 'woocommerce');
		// $fields['shipping']['shipping_address_2']['placeholder'] = __('Address 2', 'woocommerce');
		// $fields['billing']['billing_email']['placeholder'] = 'email@yourmail.com';
    
    return $fields;
    }
    
    // Set all checkout fields to one column and 100% wide
    
    function wt2_checkout_form_width_function()
    {
	$o = get_option('WooTweak2_options');
	if($o['wt2_checkout_form_width'])
	{
	    ?>
	    <style type="text/css">
	    form .form-row, form .form-row-first, form .form-row-last {
		float: none;
		width: 100%;
		}
		.col2-set .col-1, .col2-set .col-2 {
		float: none;
		width: 100%;
		}
	    </style>
	    <?php
	}
    }
    
    // Shop manager role: remove links and pages capabilities.
    
    function wt2_tweak_shop_manager_role()
    {
	global $wp_roles;
	
	$o = get_option('WooTweak2_options');
	if($o['wt2_manage_pages']) 
	{
		$wp_roles->remove_cap('shop_manager', 'edit_pages');
		$wp_roles->remove_cap('shop_manager', 'edit_published_pages');
		$wp_roles->remove_cap('shop_manager', 'edit_private_pages');
		$wp_roles->remove_cap('shop_manager', 'publish_pages');
		$wp_roles->remove_cap('shop_manager', 'delete_pages');
		$wp_roles->remove_cap('shop_manager', 'delete_private_pages');
		$wp_roles->remove_cap('shop_manager', 'delete_published_pages');
		$wp_roles->remove_cap('shop_manager', 'delete_others_pages');
	}
	else
	{
		$wp_roles->add_cap('shop_manager', 'edit_pages');
		$wp_roles->add_cap('shop_manager', 'edit_published_pages');
		$wp_roles->add_cap('shop_manager', 'edit_private_pages');
		$wp_roles->add_cap('shop_manager', 'publish_pages');
		$wp_roles->add_cap('shop_manager', 'delete_pages');
		$wp_roles->add_cap('shop_manager', 'delete_private_pages');
		$wp_roles->add_cap('shop_manager', 'delete_published_pages');
		$wp_roles->add_cap('shop_manager', 'delete_others_pages');	
	}
	

	$wp_roles->remove_cap('shop_manager', 'manage_links');
	$wp_roles->remove_cap('shop_manager', 'woocommerce_debug');
	$wp_roles->remove_cap('shop_manager', 'manage_woocommerce');
    }
    
   // Use WP PageNavi plugin for pagination instead of kit pagination 
    
   function wt2_use_wp_pagenavi_func()
    {
	$o = get_option('WooTweak2_options');
	if ($o['wt2_use_wp_pagenavi'])
	{
	remove_action('woocommerce_pagination', 'woocommerce_pagination', 10);
	function new_woocommerce_pagination() {
	    wp_pagenavi();
	    }
	add_action( 'woocommerce_pagination', 'new_woocommerce_pagination', 10);
	}
    } 
    
    // Chose where to show sorting form
    
    function wt2_show_sorting_feild_before_products()
    {
	$o = get_option('WooTweak2_options');
	if ($o['wt2_show_sort_before_products'])
	{
	remove_action( 'woocommerce_pagination', 'woocommerce_catalog_ordering', 20 );
	add_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 20);
	}
    }
    
    // Custom text for Add ot Cart button
    
    function wt2_custom_addtocart_button_text_func()
    {
	$o = get_option('WooTweak2_options');
	if ($o['wt2_custom_addtocart_button_text'])
	{
	    return __($o['wt2_custom_addtocart_button_text'], 'woocommerce');
	}
	else
	{
	    return __('Add to cart', 'woocommerce');
	}
    }
    
    // Disable logo menu in admin dashboard

    function wt2_remove_admin_bar_links() {
    	$o = get_option('WooTweak2_options');
		if ($o['wt2_disable_dashbord_logo_menu'])
		{
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu('wp-logo');
			$wp_admin_bar->remove_menu('updates');
		}
	}

	// Disable generator tag in head

	function wt2_remove_woo_commerce_generator_tag()
	{
	    remove_action('wp_head',array($GLOBALS['woocommerce'], 'generator'));
	}

    // Variations stuff *************************************************************************************************************************
    
    function wt2_variations_enhancement()
    {
	global $post, $woocommerce, $product;
	
	$attributes = $product->get_available_attribute_variations();
	$default_attributes = (array) maybe_unserialize(get_post_meta( $post->ID, '_default_attributes', true ));
	$selected_attributes = apply_filters( 'woocommerce_product_default_attributes', $default_attributes );
	
	// Put available variations into an array and put in a Javascript variable (JSON encoded)
	$available_variations = array();
	
	foreach($product->get_children() as $child_id) {
		
		    $variation = $product->get_child( $child_id );
		
		    if($variation instanceof WC_Product_Variation) {
		
		    	if (get_post_status( $variation->get_variation_id() ) != 'publish') continue; // Disabled
		    	
		    	if (!$variation->is_visible()) continue; // Visible setting - may be hidden if out of stock
		
		        $variation_attributes = $variation->get_variation_attributes();
		        $availability = $variation->get_availability();
		        $availability_html = (!empty($availability['availability'])) ? apply_filters( 'woocommerce_stock_html', '<p class="stock '.$availability['class'].'">'. $availability['availability'].'</p>', $availability['availability'] ) : '';
		
		        if (has_post_thumbnail($variation->get_variation_id())) {
		            $attachment_id = get_post_thumbnail_id( $variation->get_variation_id() );
		            $large_thumbnail_size = apply_filters('single_product_large_thumbnail_size', 'shop_single');
		            $image = current(wp_get_attachment_image_src( $attachment_id, $large_thumbnail_size ));
		            $image_link = current(wp_get_attachment_image_src( $attachment_id, 'full' ));
		        } else {
		            $image = '';
		            $image_link = '';
		        }
		
		        $available_variations[] = apply_filters('woocommerce_available_variation', array(
		            'variation_id' => $variation->get_variation_id(),
		            'attributes' => $variation_attributes,
		            'image_src' => $image,
		            'image_link' => $image_link,
		            'price_html' => '<span class="price">'.$variation->get_price_html().'</span>',
		            'availability_html' => $availability_html,
		            'sku' => __('SKU:', 'woocommerce') . ' ' . $variation->sku,
		            'min_qty' => 1,
		            'max_qty' => $variation->stock,
		            'is_downloadable' => $variation->is_downloadable(),
		            'is_virtual' => $variation->is_virtual()
		        ), $product, $variation);
		    }
		}
	
	
	
	?>
	<script type="text/javascript">
	    var wt2_product_variations_meta = new Array();
	<?php
	$vars = array();
	foreach($available_variations as $var)
	{
	    $info = get_post_custom($var['variation_id']);
	    ?>
	    wt2_product_variations_meta[<?php echo $var['variation_id']; ?>] = <?php echo json_encode($info); ?>;
	    <?php
	}
	?>
	</script>
	<?php
    }
    
    function wt2_variations_enhancement_meta_table()
    {
	$o = get_option('WooTweak2_options');
	if($o['wt2_variations_descriptions'] && !$o['wt2_variations_tab_on_product_page'])
	{
	?>
	<br>
	<table class="shop_attributes" id="wt2_variation_meta">
	    <tbody>
		<tr class="">
		    <th><?php echo __('SKU', 'woocommerce'); ?></th>
		    <td id="wt2_var_meta_sku"></td>
		</tr>
		<tr class="alt">
		    <th><?php echo __('Weight', 'woocommerce'); ?></th>
		    <td id="wt2_var_meta_weight"></td>
		</tr>
		<tr class="">
		    <th><?php echo __('Dimensions', 'woocommerce'); ?></th>
		    <td id="wt2_var_meta_dimentions"></td>
		</tr>
		<?php
		$o = get_option('WooTweak2_options');
		if($o['wt2_variations_descriptions'])
		{
		    ?>
		    <tr class="alt">
			<th><?php echo __('Description', 'woocommerce'); ?></th>
			<td id="wt2_var_meta_description"></td>
		    </tr>
		    <?php
		}
		?>
	    </tbody>
	</table>
	<?php
	}
    }
    
    function wt2_variations_description_tab()
    {
	$o = get_option('WooTweak2_options');
	if($o['wt2_variations_descriptions'])
	{
    ?>
	<li class="variations_tab show_if_variable variation_options"><a href="#second_variable_product_options"><?php _e('Variations', 'woocommerce'); echo ' ('; _e('Description', 'woocommerce'); echo ')'; ?></a></li>
    <?php
	}
    }
    
    function wt2_variations_description_tab_fields()
    {
	$o = get_option('WooTweak2_options');
	if($o['wt2_variations_descriptions'])
	{
	    global $post, $woocommerce, $product;
	
	    $attributes = (array) maybe_unserialize( get_post_meta($post->ID, '_product_attributes', true) );
	
	    // See if any are set
	    $variation_attribute_found = false;
	    if ($attributes) foreach($attributes as $attribute){
		    if (isset($attribute['is_variation'])) :
			    $variation_attribute_found = true;
			    break;
		    endif;
	    }
	
	?>
	<div id="second_variable_product_options" class="panel woocommerce_options_panel">
	<?php
				$args = array(
					'post_type'	=> 'product_variation',
					'post_status' => array('private', 'publish'),
					'numberposts' => -1,
					'orderby' => 'menu_order',
					'order' => 'asc',
					'post_parent' => $post->ID
				);
				
				$variations = get_posts($args);
				$loop = 0;
				if ($variations) foreach ($variations as $variation) : 
				
				    $variation_data = get_post_custom( $variation->ID );
				    ?>
				    <p class="form-field options_group">
					    <input type="hidden" name="variable_post_id[<?php echo $loop; ?>]" value="<?php echo esc_attr( $variation->ID ); ?>" />
					    <input type="hidden" class="variation_menu_order" name="variation_menu_order[<?php echo $loop; ?>]" value="<?php echo $loop; ?>" />
					    
					    <!--<label><?php _e('Description', 'woocommerce') ?></label>-->
					    <label>
					    	#<?php echo $variation->ID; ?> &mdash;
						    <?php
							    $variation_selected_value = get_post_meta( $variation->ID, 'attribute_' . sanitize_title($attribute['name']), true );
							    echo $variation_selected_value;
						    ?><br />
						    <?php echo '<strong>SKU:</strong> ' . $variation_data['_sku'][0]; ?>
						</label>
					    <textarea cols="70" rows="10" placeholder="Custom description. Leave blank to show product title." class="wt2_variable_description short" name="variable_description[<?php echo $loop; ?>]" id=""><?php if (isset($variation_data['_description'][0])) echo $variation_data['_description'][0]; ?></textarea>
					    <!--<input type="text" size="5" name="variable_description[<?php echo $loop; ?>]" value="<?php if (isset($variation_data['_description'][0])) echo $variation_data['_description'][0]; ?>" />-->
				    </p>				
				    <?php
				    $loop++; endforeach;
	?>
	</div>
	<?php
	}
    }
    
    function wt2_variations_description_tab_fields_process( $post_id )
    {
	$o = get_option('WooTweak2_options');
	if($o['wt2_variations_descriptions'])
	{
	    global $woocommerce, $wpdb;
	    
	    if (isset($_POST['variable_sku']))
	    {
		    $variable_post_id 	= $_POST['variable_post_id'];
		    $variable_description	= $_POST['variable_description'];
		    $variable_sku 		= $_POST['variable_sku'];
    
		    for ($i=0; $i<sizeof($variable_sku); $i++)
		    {
			    $variation_id = (int) $variable_post_id[$i];
			    update_post_meta( $variation_id, '_description', $variable_description[$i] );
		    }		
	    }
	}
	
    }
    
    function wt2_variations_tab()
    {
	$o = get_option('WooTweak2_options');
	if($o['wt2_variations_tab_on_product_page'])
	{
	    echo '<li><a href="#tab-variations">'.__('Variation', 'woocommerce').' ('.__('Description', 'woocommerce').')</a></li>';
	}
    }
    function wt2_variations_panel()
    {
	$o = get_option('WooTweak2_options');
	if($o['wt2_variations_tab_on_product_page'])
	{
	?>
	<div class="panel" id="tab-variations">
	<table class="shop_attributes" id="wt2_variation_meta">
	    <tbody>
		<tr class="">
		    <th><?php echo __('SKU', 'woocommerce'); ?></th>
		    <td id="wt2_var_meta_sku"></td>
		</tr>
		<tr class="alt">
		    <th><?php echo __('Weight', 'woocommerce'); ?></th>
		    <td id="wt2_var_meta_weight"></td>
		</tr>
		<tr class="">
		    <th><?php echo __('Dimensions', 'woocommerce'); ?></th>
		    <td id="wt2_var_meta_dimentions"></td>
		</tr>
		<?php
		$o = get_option('WooTweak2_options');
		if($o['wt2_variations_descriptions'])
		{
		    ?>
		    <tr class="alt">
			<th><?php echo __('Description', 'woocommerce'); ?></th>
			<td id="wt2_var_meta_description"></td>
		    </tr>
		    <?php
		}
		?>
	    </tbody>
	</table>
	</div>
	<?php
	}
    }
}

    
add_action('wp_enqueue_scripts', 'wt2_styles');
function wt2_styles()
    {
	wp_register_style('wootweak2', plugins_url('/css/wootweak2.css', __FILE__) );
	wp_enqueue_style('wootweak2');

    }
add_action('wp_enqueue_scripts', 'wt2_scripts');
function wt2_scripts()
    {
	wp_enqueue_script('wootweak2', plugins_url('/js/wootweak2.js', __FILE__), array('jquery'), '1.0', true );
    }

$woot = new WooTweak2();

// Cart widget ----------------------------------------------------------------------------------------

class WooTweak2_cart_widget extends WP_Widget {
    
    function __construct()
    {
    $params = array(
	'name' => 'WooTweak2 '.__('Cart', 'woocommerce'),
	'description' => __('Small cart. Amount and total only', 'WooTweak2') // plugin description that is showed in Widget section of admin panel
    );
    
    parent::__construct('WooTweak2_cart_widget', '', $params);
    }
    
    function form($instance)
    {
    extract($instance);
    
    ?>
	<!--some html with input fields-->
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woocommerce'); ?></label>
		<input
		type="text"
		class="widefat"
		id="<?php echo $this->get_field_id('title'); ?>"
		name="<?php echo $this->get_field_name('title'); ?>"
		value="<?php if(isset($title)) echo esc_attr($title) ?>"
		/>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Custom text before price:', 'WooTweak2'); ?></label>
		<input
		type="text"
		class="widefat"
		id="<?php echo $this->get_field_id('prepricetext'); ?>"
		name="<?php echo $this->get_field_name('prepricetext'); ?>"
		value="<?php if(isset($prepricetext)) echo esc_attr($prepricetext) ?>"
		/>
	</p>
    <?php
    }
    
    function widget($args, $instance)
    {
    global $woo_options, $woocommerce;
    if ( is_cart() || is_checkout() ) return;

    extract($args);
    extract($instance);
    
    echo $before_widget;
	echo $before_title . $title . $after_title;
	?>
	<ul class="cart_list product_list_widget widget_shopping_cart">
	<a href="<?php echo $woocommerce->cart->get_cart_url(); ?>" title="<?php _e('View your shopping cart', 'WooTweak2'); ?>">
	    <span> 
	    <?php
	    if(isset($prepricetext)) 
	    {
	    	echo $prepricetext;
	    }
	    else
	    {
	    	echo sprintf(_n('%d item &ndash; ', '%d items &ndash; ', $woocommerce->cart->cart_contents_count, 'WooTweak2'), $woocommerce->cart->cart_contents_count);	
	    }	
	    echo $woocommerce->cart->get_cart_subtotal();
	    ?>
	    </span>
	</a>
	</ul>
	<?php
    echo $after_widget;
    }
}

function WooTweak2_cart_widget_register_function()
{
    register_widget('WooTweak2_cart_widget');
}

add_action('widgets_init', 'WooTweak2_cart_widget_register_function');



?>