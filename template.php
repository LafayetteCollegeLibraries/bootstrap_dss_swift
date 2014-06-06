<?php

/**
 * @file template.php
 * @author griffinj@lafayette.edu
 * This file contains the primary theme hooks found within any given Drupal 7.x theme
 * 
 * @todo Implement some Drupal theming hooks
 */

  // Includes functions to create Islandora Solr blocks.
require_once dirname(__FILE__) . '/includes/blocks.inc';
require_once dirname(__FILE__) . '/includes/forms.inc';
require_once dirname(__FILE__) . '/includes/menus.inc';
require_once dirname(__FILE__) . '/includes/dss_mods.inc';
require_once dirname(__FILE__) . '/includes/dss_dc.inc';
require_once dirname(__FILE__) . '/includes/pager.inc';
require_once dirname(__FILE__) . '/includes/apachesolr.inc';
require_once dirname(__FILE__) . '/includes/islandora_solr.inc';
require_once dirname(__FILE__) . '/includes/islandora_basic_collection.inc';

require_once dirname(__FILE__) . '/includes/breadcrumb.inc';

function bootstrap_dss_elc_process_node(&$vars) {

  if($vars['type'] == 'loan') {

    /*
    hide($vars['content']['field_loan_ledger']);
    hide($vars['content']['field_loan_volumes_loaned']);
    hide($vars['content']['field_loan_issues_loaned']);
    */
  }

}

function bootstrap_dss_elc_preprocess_node(&$vars) {

  /**
   * Theming for loans
   *
   */
  if($vars['type'] == 'loan') {

    drupal_add_css(drupal_get_path('theme', 'bootstrap_dss_elc') . '/css/node__loan.css');

    /*
    $vars['field_entries'] = array(
				   t('Shareholder:') => 'field_loan_shareholder',
				   t('Representative:') => 'field_loan_representative',
				   );
    */

    /**
     * For rendering the type of item loaned
     * Need to retrieve the individual Book/Periodical/Item Entity in order to retrieve the actual type
     *
     */
    $bib_rel_object_entity = $vars['field_bib_rel_object'][0]['entity'];
    $vars['bib_rel_object_type'] = $bib_rel_object_entity->type;

    /**
     * For rendering the loan duration separately
     *
     */
    $loan_duration_checkout = $vars['field_loan_duration'][0]['value'];
    $vars['loan_duration_checkout'] = date('Y-m-d', $loan_duration_checkout);

    $loan_duration_returned = $vars['field_loan_duration'][0]['value2'];
    $vars['loan_duration_returned'] = date('Y-m-d', $loan_duration_returned);
    hide($vars['content']['field_loan_duration']);

    /**
     * Generates the link to the Islandora Page Object
     * Parses the filename structure in order to construct the Fedora Commons PID: ELCv2_C2_082 -> ELCv2:C2_082
     *
     */
    $filename_term = $vars['field_loan_filename'][0]['taxonomy_term'];

    /**
     * Resolves EDDC-221
     * @todo Fully generate the path aliases for all newly migrated Page Objects
     */
    if($filename_term->name == 'ELCv2_C1_136') {

      $vars['islandora_object_link'] = l(t('View Ledger Image'), 'ledger/2/' . $filename_term->name);
    } else {

      preg_match('/(.+?)_(.+)/', $filename_term->name, $m); 
      $islandora_pid = $m[1] . ':' . $m[2];

      $vars['islandora_object_link'] = l(t('View Ledger Image'), 'islandora/object/' . $islandora_pid);
    }
    hide($vars['content']['field_loan_filename']);

    hide($vars['content']['field_loan_ledger']);
    hide($vars['content']['field_loan_volumes_text']);
    hide($vars['content']['field_loan_issues_text']);
    hide($vars['content']['body']);
  } else if($vars['type'] == 'human') {

    /**
     * Hide fields for the node
     *
     */
    hide($vars['content']['field_human_surname']);
    hide($vars['content']['field_person_location']);
    hide($vars['content']['field_human_middle_initials']);
    hide($vars['content']['field_pers_rel_object']);
    hide($vars['content']['field_pers_rel_role']);

    /**
     * Embed an actual view for the human
     */

    /** (Ensure that this is only rendered for Pages and not Teaser Views) */
    $loans_view = '';
    if(!$vars['teaser']) {

      $loans_view = views_embed_view('loans_by_human', 'default', $vars['nid']);
    }
    $vars['loans_view'] = $loans_view;

  } else if($vars['type'] == 'book'
	    or $vars['type'] == 'periodical'
	    or $vars['type'] == 'item'
	    ) {

    /**
     * Embed an actual view for the human
     */

    /** (Ensure that this is only rendered for Pages and not Teaser Views) */
    $loans_view = '';
    if(!$vars['teaser']) {

      // Retrieve the Manifestation Node for the Item Node
      // Loosely based upon the FRBR data model
      $manifestation_nid = '';

      /**
       * @todo Create an appropriate field and store this relationship
       */
      $drupalQuery = new EntityFieldQuery();
      $result = $drupalQuery->entityCondition('entity_type', 'node')
	->entityCondition('bundle', 'manifestation')
	->fieldCondition('field_artifact_title', 'value', $vars['field_artifact_title'][0]['value'])
	->execute();

      if(isset($result['node'])) {
	  
	//$bib_entities = array_merge(entity_load('node', array_keys($result['node'])));
	$manifestation_nid = array_shift(array_keys($result['node']));
      }

      $loans_view = views_embed_view('loans_by_item', 'default', $manifestation_nid);
    }
    $vars['loans_view'] = $loans_view;
  }

  if($vars['page']) {

    // Add header meta tag for IE to head
    global $base_url;
    $meta_element_open_graph_type = array('#type' => 'html_tag',
					  '#tag' => 'meta',
					  '#attributes' => array('property' =>  'og:type',
								 'content' => 'article'),
					  );

    $meta_element_open_graph_url = array('#type' => 'html_tag',
					 '#tag' => 'meta',
					 '#attributes' => array('property' =>  'og:url',
								'content' => $base_url . '/' . drupal_get_path_alias()
								),
					 );
    
    $meta_element_open_graph_author = array('#type' => 'html_tag',
					    '#tag' => 'meta',
					    '#attributes' => array('property' =>  'og:author',
								   'content' => 'https://www.facebook.com/LafayetteCollegeLibrary',
								   )
					    );
    
    $meta_element_open_graph_title = array('#type' => 'html_tag',
					   '#tag' => 'meta',
					   '#attributes' => array('property' =>  'og:title',
								  'content' => $vars['title'],
								  )
					   );

    // For all <meta> elements
    $meta_elements = array(
			   'meta_element_open_graph_type' => $meta_element_open_graph_type,
			   'meta_element_open_graph_url' => $meta_element_open_graph_url,
			   'meta_element_open_graph_author' => $meta_element_open_graph_author,
			   'meta_element_open_graph_title' => $meta_element_open_graph_title,
			   );
    $meta_elements['meta_element_open_graph_image'] = array('#type' => 'html_tag',
							    '#tag' => 'meta',
							    '#attributes' => array('property' =>  'og:image',
										   'content' => $base_url . '/' . drupal_get_path('theme', 'bootstrap_dss_elc') . '/files/dss_logo_full.png',
										   ),
							    );
    $meta_elements['meta_element_open_graph_site_name'] = array('#type' => 'html_tag',
								'#tag' => 'meta',
								'#attributes' => array('property' =>  'og:site_name',
										       'content' => 'Digital Scholarship Services',
										       )
								);

    foreach($meta_elements as $key => $meta_element) {

      // Add header meta tag for IE to head
      drupal_add_html_head($meta_element, $key);
    }
  }

  /**
   * Implements redirection for the Repository Migration page
   * @todo Refactor
   * Resolves DSSSM-826
   */
  if($vars['node_url'] == '/redirect') {

    drupal_add_js('jQuery(document).ready(function() { setTimeout(function() { window.location.replace("/"); }, 5000); });',
		  array('type' => 'inline', 'scope' => 'footer', 'weight' => 5)
		  );
  }

  /**
   * Implemented in response to there being no clear means by which to decode the HTML character entity references within the Drupal stack
   * EDDC-184
   * @todo Refactor
   */

  if($vars['type'] == 'loan'
     or $vars['type'] == 'manifestation'
     or $vars['type'] == 'item'
     or $vars['type'] == 'book'
     or $vars['type'] == 'periodical') {

    $title = $vars['title'];

    $title = preg_replace('/&amp;amp;/', '&', $title);
    $title = preg_replace('/&amp;/', '&', $title);
    $title = preg_replace('/&#039;/', "'", $title);
    $title = preg_replace('/&quot;/', "'", $title);

    $vars['title'] = $title;
    drupal_set_title($title);
  }
}

/**
 * Implements template_preprocess_hybridauth_widget
 * @griffinj
 *
 */
function bootstrap_dss_elc_preprocess_hybridauth_widget(&$vars) {

  // Refactor
  $i = 0;
  foreach (hybridauth_get_enabled_providers() as $provider_id => $provider_name) {

    //$vars['providers'][$i] = preg_replace('/(<\/span>)/', "</span><span>&nbsp;$provider_name</span>", $vars['providers'][$i]);
    $i++;
  }
}

function _bootstrap_dss_elc_user_logout($account) {

  if (variable_get('user_pictures', 0)) {
    
    if (!empty($account->picture)) {

      if (is_numeric($account->picture)) {

        $account->picture = file_load($account->picture);
      }
      if (!empty($account->picture->uri)) {

        $filepath = $account->picture->uri;
      }
    } elseif (variable_get('user_picture_default', '')) {

      $filepath = variable_get('user_picture_default', '');
    }

    if (isset($filepath)) {

      $alt = t("@user's picture", array('@user' => format_username($account)));
      // If the image does not have a valid Drupal scheme (for eg. HTTP),
      // don't load image styles.
      if (module_exists('image') && file_valid_uri($filepath) && $style = variable_get('user_picture_style', '')) {

        $user_picture = theme('image_style', array('style_name' => $style, 'path' => $filepath, 'alt' => $alt, 'title' => $alt));
      }
      else {

        $user_picture = theme('image', array('path' => $filepath, 'alt' => $alt, 'title' => $alt));
      }

      /*
       * Generate the CAS logout link
       *
       */
      $attributes = array('https' => TRUE,
			  'attributes' => array('title' => t('Log out.')),
			  'html' => TRUE,
			  );

      // If we're currently authenticated by CAS, this apparently does not function...
      if(cas_user_is_logged_in()) {

	global $base_url;
	$attributes['query'] = array('destination' => current_path());
	return l($user_picture, 'caslogout', $attributes);
      }

      return l($user_picture, "user/logout", $attributes);
    }
  }
}

/**
 * Preprocess variables for page.tpl.php
 *
 * @see page.tpl.php
 */

function bootstrap_dss_elc_preprocess_page(&$variables) {

  // Add information about the number of sidebars.
  if (!empty($variables['page']['sidebar_first']) && !empty($variables['page']['sidebar_second'])) {
    $variables['columns'] = 3;

  }
  elseif (!empty($variables['page']['sidebar_first'])) {
    $variables['columns'] = 2;
  }
  elseif (!empty($variables['page']['sidebar_second'])) {
    $variables['columns'] = 2;
  }
  else {
    $variables['columns'] = 1;
  }

  // Primary nav
  $variables['primary_nav'] = FALSE;
  if ($variables['main_menu']) {
    // Build links
    $variables['primary_nav'] = menu_tree(variable_get('menu_main_links_source', 'main-menu'));
    // Provide default theme wrapper function
    $variables['primary_nav']['#theme_wrappers'] = array('menu_tree__primary');
  }

  // Secondary nav
  $variables['secondary_nav'] = FALSE;
  if ($variables['secondary_menu']) {
    // Build links
    $variables['secondary_nav'] = menu_tree(variable_get('menu_secondary_links_source', 'user-menu'));
    // Provide default theme wrapper function
    $variables['secondary_nav']['#theme_wrappers'] = array('menu_tree__secondary');
  }

  /**
   * browscap integration
   *
   * @todo Resolve using EDDC-76
   * Capture from the User-Agent value the type of device being used to browse the page
   * (Probably should be decoupled and integrated into CSS and JavaScript)
   *
   */
  $browser = browscap_get_browser();

  //$is_smartphone_browser = $browser['ismobiledevice'] && preg_match('/iPhone|(?:Android.*?Mobile)|(?:Windows Phone)/', $browser['useragent']);
  $is_smartphone_browser = false;

  /**
   * Ensure that the "Contact Us" link directs users to the Drupal Node only for non-smartphone devices
   * Resolves DSSSM-635
   * @todo Refactor for specifying the path to the "Contact Us" form
   *
   */
  if($is_smartphone_browser) {

    // The "Contact Us" link (to the path "contact")
    $variables['contact_anchor'] = l(t('Contact Us'), 'contact');
  } else {
  
    // The "Contact Us" link
    $variables['contact_anchor'] = l(t('Contact Us'), '', array('attributes' => array('data-toggle' => 'lafayette-dss-modal',
										      'data-target' => '#contact',
										      'data-anchor-align' => 'false'),
								'fragment' => ' ',
								'external' => TRUE));
  }



  // Different images must be passed based upon the browser type

  // Shouldn't be parsing the string itself; refactor
  if($is_smartphone_browser) {
    //if(TRUE) {

    $variables['dss_logo_image'] = theme_image(array('path' => drupal_get_path('theme', 'bootstrap_dss_elc') . '/files/dss_logo_mobile.png',
						     'alt' => t('digital scholarship services logo'),
						     'attributes' => array()));
  } else {

    // Work-around for the logo image
    $variables['dss_logo_image'] = theme_image(array('path' => drupal_get_path('theme', 'bootstrap_dss_elc') . '/files/dss_logo.png',
						     'alt' => t('digital scholarship services logo'),
						     'attributes' => array()));
  }

  // The "Log In" link
  //$variables['auth_anchor'] = l(t('Log In'), '', array('attributes' => array('data-toggle' => 'lafayette-dss-modal',
  /*
  $variables['auth_anchor'] = l('<div class="auth-icon"><img src="/sites/all/themes/bootstrap_lafayette_lib_dss/files/UserIcon.png" /><span>Log In</span></div>', '', array('attributes' => array('data-toggle' => 'lafayette-dss-modal',
														    'data-target' => '#auth-modal',
																								  'data-width-offset' => '10px',
														    'data-height-offset' => '28px'),
											      'fragment' => ' ',
											      //'external' => TRUE));
											      'external' => TRUE,
											      'html' => TRUE
											      ));
  */

  /**
   * Disabled for the initial release of the site
   * @todo Re-integrate for cases requiring Facebook and Twitter authentication
   *
   */
  //  $variables['auth_anchor'] = '<a data-toggle="lafayette-dss-modal" data-target="#auth-modal" data-width-offset="0px" data-height-offset="30px"><div class="auth-icon navbar-icon"><img src="/sites/all/themes/bootstrap_dss_elc/files/UserIcon.png" /><span>Log In</span></div></a>';
  global $base_url;

  /**
   * Work-around for submitting GET parameters within the "destination" parameter for CAS redirection
   * Resolves DSS-192
   *
   */
  $GET_params = $_SERVER['QUERY_STRING'];

  $variables['auth_anchor'] = l('<div class="auth-icon navbar-icon"><img src="/sites/all/themes/bootstrap_dss_elc/files/UserIcon.png" /><span>Log In</span></div>',
				'cas',
				array('html' => TRUE,
				      'https' => true,
				      'query' => array('destination' => current_path() . '?' . $GET_params )
				      )
				);

  // The "Log Out" link
  // This needs to be refactored for integration with the CAS module
  // If we're currently authenticated by CAS, this apparently does not function...
  if(cas_user_is_logged_in()) {

    global $base_url;
    $variables['logout_anchor'] = l(t('Log Out'), 'caslogout', array('query' => array('destination' => current_path())));
  } else {

    $variables['logout_anchor'] = l(t('Log Out'), 'user/logout');
  }

  // The "Share" link
  //$variables['share_anchor'] = l(t('Share'), '', array('attributes' => array('data-toggle' => 'lafayette-dss-modal',
  /*
  $variables['share_anchor'] = l('<div class="share-icon"><img src="/sites/all/themes/bootstrap_lafayette_lib_dss/files/ShareIcon.png" /><span>Share</span></div>', '', array('attributes' => array('data-toggle' => 'lafayette-dss-modal',
									     'data-target' => '#share-modal',
																								    'data-width-offset' => '10px',
									     'data-height-offset' => '28px'
									     ),
						       'fragment' => ' ',
						       //'external' => TRUE));
						       'external' => TRUE,
						       'html' => TRUE
						       ));
  */

  $variables['share_anchor'] = '<a data-toggle="lafayette-dss-modal" data-target="#share-modal" data-width-offset="10px" data-height-offset="28px"><div class="share-icon navbar-icon"><img src="/sites/all/themes/bootstrap_dss_elc/files/ShareIcon.png" /><span>Share</span></div></a>';

  // Render thumbnails for authenticated users
  $variables['user_picture'] = '<span class="button-auth-icon"></span>';

  if(user_is_logged_in()) {

    // For the user thumbnail
    global $user;

    //$user_view = user_view($user);
    //$variables['user_picture'] = drupal_render($user_view['user_picture']);
    $variables['user_picture'] = _bootstrap_dss_elc_user_logout($user);
  }

  /**
   * Variables for the Islandora simple_search Block
   *
   */
  // A search button must be passed if this is being viewed with a mobile browser

  $search_icon = theme_image(array('path' => drupal_get_path('theme', 'bootstrap_dss_elc') . '/files/SearchIcon.png',
				   'alt' => t('search the site'),
				   'attributes' => array()));

  $simple_search_mobile = '<a data-toggle="lafayette-dss-modal" data-target="#advanced-search-modal" data-width-offset="-286px" data-height-offset="28px">
<div class="simple-search-icon">' . $search_icon . '<span>Search</span></div></a>' . render($variables['page']['simple_search']);
  unset($variables['page']['simple_search']);

  $variables['search_container'] = '<div class="modal-container container"><div id="simple-search-control-container" class="modal-control-container container">' . $simple_search_mobile . '</div></div>';


  // Refactor
  $auth_container = '
     <div class="auth-container modal-container container">
       <div id="auth-control-container" class="modal-control-container container">';

  if(!empty($variables['page']['auth'])) {

    $auth_container .= $variables['auth_anchor'];
  } else {
    
    $auth_container .= '
      <div class="auth-icon">' . $variables['user_picture'] . '</div>
      <div class="auth-link">' . $variables['logout_anchor'] . '</div>';
  }

  $auth_container .= '
       </div><!-- /#auth-control-container -->
     </div><!-- /.auth-container -->';

  $variables['auth_container'] = $auth_container;

  $share_container = '
     <div class="share-container modal-container container">
       <div id="share-control-container" class="modal-control-container container">

         ' . $variables['share_anchor'] . '
       </div><!-- /#share-control-container -->
     </div><!-- /.share-container -->';

  $variables['share_container'] = $share_container;

  $menu_toggle_image = theme_image(array('path' => drupal_get_path('theme', 'bootstrap_dss_elc') . '/files/MenuIcon.png',
					 'alt' => t('mobile menu'),
					 'attributes' => array()));

  $variables['menu_toggle_image'] = $menu_toggle_image;

  $menu_toggle_container = '

       <div id="menu-toggle-control-container" class="modal-control-container container">
<div class="navbar-collapse-toggle">
<!-- .btn-navbar is used as the toggle for collapsed navbar content -->
  <div data-toggle="collapse" data-target=".nav-collapse">
    <div id="menu-toggle-icon" class="navbar-icon btn-navbar">' . $menu_toggle_image . '<span id="btn-navbar-caption" class="">Menu</span></div>
  </div>
</div><!-- /.navbar-collapse-toggle -->
</div>';

  $variables['menu_toggle_container'] = $menu_toggle_container;
  $slide_panel_container = '';

  $variables['slide_panel_container'] = $slide_panel_container;

  $variables['breadcrumb'] = theme('breadcrumb', menu_get_active_trail());

  $variables['slide_drawers'] = TRUE;
}

/**
 * Implements template_preprocess_html
 *
 */
function bootstrap_dss_elc_preprocess_html(&$variables) {

  drupal_add_library('system', 'effects.drop');
  drupal_add_library('system', 'effects.slide');
}

/**
 * Work-around for ensuring that the search form is not forcibly displayed within search results
 * See https://drupal.org/comment/4573218#comment-4573218
 *
 */
function bootstrap_dss_elc_process_page(&$variables) {

  if(array_key_exists('search_form', $variables['page']['content']['system_main'])) {

    hide($variables['page']['content']['system_main']['search_form']);
  }
}

/**
 * Template preprocess function for hybridauth_widget.
 */
/*
function template_preprocess_hybridauth_widget(&$vars, $hook) {

}
*/

/**
 * Checks whether the user can access the given object.
 *
 * Checks for object existance, accessiblitly, namespace permissions,
 * and user permissions
 *
 * @param string $perm
 *   User permission to test for.
 * @param FedoraObject $object
 *   The object to test, if NULL given the object doesn't exist or is
 *   inaccessible.
 *
 * @return bool
 *   TRUE if the user is allowed to access this object, FALSE otherwise.
 */

/*
function bootstrap_dss_elc_object_access_callback($perm, $object = NULL) {

  return TRUE;
}
*/

function bootstrap_dss_elc_theme_registry_alter(&$registry) {

  $registry['hybridauth_widget']['file'] = 'template';
}

/**
 * Please see http://www.php.net/manual/en/function.ip2long.php#82397
 * @todo Integrate with islandora_dss_solr_net_match()
 * @see islandora_dss_solr_net_match().
 *
 * This assumes a subnet of 139.147.0.0/16 for Lafayette College servers
 * This assumes a subnet of 192.168.101.0/24 for the VPN
 */
function bootstrap_dss_elc_net_match($CIDR, $IP) {

  list($net, $mask) = explode('/', $CIDR);
  return ( ip2long ($IP) & ~((1 << (32 - $mask)) - 1) ) == ip2long ($net);
}

function bootstrap_dss_elc_preprocess_islandora_large_image(array &$variables) {

  /**
   * Work-around given the issues for hook_menu_alter() and hook_preprocess_HOOK() implementations
   * @todo Refactor either into hook_menu_alter() or hook_preprocess_HOOK() implementations
   *
   */
  $object = $variables['islandora_object'];

  $client_ip = ip_address();
  $headers = apache_request_headers();

  // ...not within the campus network...
  // (for proxy servers...)
  if(array_key_exists('X-Forwarded-For', $headers)) {

    // Not on the VPN...
    $is_anon_non_lafayette_user = !islandora_dss_solr_net_match('192.168.101.0/24', $headers['X-Forwarded-For']);
    $is_anon_non_lafayette_user &= (bool) !islandora_dss_solr_net_match('139.147.0.0/16', $headers['X-Forwarded-For']);
  } else {

    // Not on the VPN...
    $is_anon_non_lafayette_user = !islandora_dss_solr_net_match('192.168.101.0/24', $client_ip);
    $is_anon_non_lafayette_user &= (bool) !islandora_dss_solr_net_match('139.147.0.0/16', $client_ip);
  }
  $is_anon_non_lafayette_user &= !user_is_logged_in(); // ...and not authenticated.

  // This fully resolves DSS-280
  $is_anon_non_lafayette_user = (bool) $is_anon_non_lafayette_user;

  if(in_array('islandora:geologySlidesEsi', $object->getParents()) and $is_anon_non_lafayette_user) {

    /**
     * Functionality for redirecting authentication requests over HTTPS
     * @see securelogin_secure_redirect()
     * @todo Refactor
     *
     */

    global $is_https;

    // POST requests are not redirected, to prevent unintentional redirects which
    // result in lost POST data. HTTPS requests are also not redirected.
    if(!$is_https) {

      $path = $_GET['q'];
      $http_response_code = 301;
      // Do not permit redirecting to an external URL.
      $options = array('query' => drupal_get_query_parameters(), 'https' => TRUE, 'external' => FALSE);
      // We don't use drupal_goto() here because we want to be able to use the
      // page cache, but let's pretend that we are.
      drupal_alter('drupal_goto', $path, $options, $http_response_code);
      // The 'Location' HTTP header must be absolute.
      $options['absolute'] = TRUE;
      $url = url($path, $options);
      $status = "$http_response_code Moved Permanently";
      drupal_add_http_header('Status', $status);
      drupal_add_http_header('Location', $url);
      // Drupal page cache requires a non-empty page body for some reason.
      print $status;
      // Mimic drupal_exit() and drupal_page_footer() and then exit.
      module_invoke_all('exit', $url);
      drupal_session_commit();
      if (variable_get('cache', 0) && ($cache = drupal_page_set_cache())) {
	drupal_serve_page_from_cache($cache);
      } else {
	ob_flush();
      }

      exit;
    } else {

      drupal_goto('cas', array('query' => array('destination' => current_path())));
    }
  }

  // Refactor
  // Retrieve the MODS Metadata
  try {

    $mods_str = $object['MODS']->content;

    $mods_str = preg_replace('/<\?xml .*?\?>/', '', $mods_str);
    $mods_object = new DssMods($mods_str);
  } catch (Exception $e) {
    
    drupal_set_message(t('Error retrieving object %s %t', array('%s' => $object->id, '%t' => $e->getMessage())), 'error', FALSE);
  }

  $label_map = array_flip(islandora_solr_get_fields('result_fields', FALSE));
  //$facet_pages_fields_data = variable_get('islandora_solr_facet_pages_fields_data', array());
  //$label_map = array();

  //$element['facet'] = $label_map[$facet];

  /**
   * Resolves DSS-261
   *
   */
  $variables['mods_object'] = isset($mods_object) ? $mods_object->toArray($label_map) : array();
  
  $rendered_fields = array();
  foreach($variables['mods_object'] as $key => &$value) {

    if(!in_array($value['label'], $rendered_fields)) {

      //$value['class'] .= ' islandora-inline-metadata-displayed';
      $rendered_fields[] = $value['label'];
    } else {

      $value['label'] = '';
    }
  }

  /**
   * Work-around for appended site-generated resource metadata into the Object
   * Refactor (or, ideally, update the MODS when Drush creates or updates the path alias)
   * Resolves DSS-243
   *
   */

  global $base_url;
  // The proper approach (production)
  //$path_alias = $base_url . '/' . drupal_get_path_alias("islandora/object/{$object->id}");
  // The less proper approach (enforce HTTP while ensuring that other linked metadata field values are possibly tunneled through TLS/SSL)
  //$path_alias = str_replace('https', 'http', $base_url) . '/' . drupal_get_path_alias("islandora/object/{$object->id}");
  // Specific to the production environment
  $path_alias = 'http://digital.lafayette.edu/' . drupal_get_path_alias("islandora/object/{$object->id}");
  $variables['mods_object']['drupal_path'] = array('class' => '',
						   'label' => 'URL',
						   'value' => $path_alias,
						   'href' =>  $path_alias);
}

//module_load_include('inc', 'bootstrap_dss_elc', 'includes/dssMods');

function bootstrap_dss_elc_preprocess_islandora_book_book(array &$variables) {

  $object = $variables['object'];
}

function bootstrap_dss_elc_preprocess_islandora_book_page(array &$variables) {

  try {

    $object = $variables['object'];
  } catch (Exception $e) {
    
    drupal_set_message(t('Error retrieving object %s %t', array('%s' => $object->id, '%t' => $e->getMessage())), 'error', FALSE);
  }

  // Retrieve the related loan records for the Page Object
  
}

function bootstrap_dss_elc_preprocess_islandora_book_pages(array &$variables) {

  // View Links.
  $display = (empty($_GET['display'])) ? 'grid' : $_GET['display'];
  $grid_active = ($display == 'grid') ? 'active' : '';
  $list_active = ($display == 'active') ? 'active' : '';

  $query_params = drupal_get_query_parameters($_GET);

  $variables['view_links'] = array(
				   array(
					 'title' => 'Grid view',
					 'href' => url("islandora/object/{$object->id}/pages", array('absolute' => TRUE)),
					 'attributes' => array(
							       'class' => "islandora-view-grid $grid_active",
							       ),
					 'query' => $query_params + array('display' => 'grid'),
					 ),
				   array(
					 'title' => 'List view',
					 'href' => url("islandora/object/{$object->id}/pages", array('absolute' => TRUE)),
					 'attributes' => array(
							       'class' => "islandora-view-list $list_active",
							       ),
					 'query' => $query_params + array('display' => 'list'),
					 ),
				   );
}
