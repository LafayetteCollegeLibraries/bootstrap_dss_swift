<?php

  /**
   * @file
   * Theming for forms
   *
   */

function bootstrap_dss_elc_form_islandora_dss_solr_advanced_search_form_alter(&$form, $form_state, $form_id) {

  foreach($form['terms'] as $key => &$term) {

    if(is_numeric($key)) {

      $term['field-wrapper']['search']['#size'] = 40;
    }
  }
  }

/**
 * griffinj
 * Implements hook_form_alter()
 *
 * Theming for the advanced search form must be implemented using a hook which does not conflict with the actual form implementation within the global context
 *
 */
function bootstrap_dss_elc_form_alter(&$form, $form_state, $form_id) {

  switch($form_id) {

  case 'islandora_solr_simple_search_form':

    $form['simple']['#attributes']['id'] = 'islandora-simple-search-wrapper';

    // Increase the size of the text box
    $form['simple']['islandora_simple_search_query']['#size'] = 21;
    $form['simple']['islandora_simple_search_query']['#value'] = 'Search...';
    $form['simple']['islandora_simple_search_query']['#default_value'] = 'Search...';

    // Insert the glyphicon for searching
    //$form['simple']['submit']['#value'] = '<span class="icon-large"></span>';

    // Default to site-scoped search
    $form['simple']['submit']['#value'] = 'Search this site';

    // Append a button for repository-scoped search
    $form['simple']['submit-1'] = array(
					'#value' => 'Search the collections',
					);

    // Providing a div wrapper for styling
    $form['simple']['submit']['#prefix'] = '<div class="islandora-simple-search-button-container">';
    $form['simple']['submit']['#suffix'] = '</div>';

    $form['simple']['submit-1']['#prefix'] = '<div class="islandora-simple-search-button-1-container">';
    $form['simple']['submit-1']['#suffix'] = '</div>';

    //$form['simple']['submit']['#class'][] = 'icon-search';

    /*
    $form['simple']['#suffix'] = l('Advanced Search', '/', array('attributes' => array('data-toggle' => 'modal'),
								 'class' => array('btn', 'btn-primary', 'btn-lg'),
								 'fragment' => 'advanced-search-modal'));
    */
    //$form['simple']['#suffix'] = '<a href="#advanced-search-modal" data-toggle="lafayette-dss-modal">Advanced Search</a>';

    //if (isset($form['#form_id']) && !in_array($form['#form_id'], $form_ids) && !isset($form['#node_edit_form']) && isset($form['actions']) && ($form['actions']['#type'] == 'actions')) {

    break;
  }

  if(!array_key_exists('#type', $form['actions'])) {

    $form['actions']['#type'] = 'actions';
  }
}

/**
 * Preprocess variables for 
 *
 * @see islandora-solr-advanced-search-form.tpl.php
 */

/*
function bootstrap_dss_elc_preprocess_islandora_dss_solr_advanced_search_form(&$variables) {

}
*/
