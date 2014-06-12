<?php

  /**
   * @file Functionality for generating breadcrumbs
   * @author griffinj@lafayette.edu
   *
   */

define('BOOTSTRAP_DSS_DIGITAL_BREADCRUMBS_MAX', 52);

/**
 * Builds an array of drupal links for use in breadcrumbs.
 *
 * @todo Make fully recursive...
 *
 * @todo Could use some clean up, can't be called multiple times safely due to
 *   the use of static variables.
 *
 * @param string $pid
 *   THe object id whose parent will be fetched for the next link.
 * @param array $breadcrumbs
 *   The list of existing bread-crumb links in reverse order.
 * @param FedoraRepository $repository
 *   The fedora repository.
 *
 * @see islandora_get_breadcrumbs_recursive
 */
function bootstrap_dss_swift_islandora_get_breadcrumbs_recursive($pid, array &$breadcrumbs, FedoraRepository $repository, $level = -1, $is_root = FALSE) {

  // Before executing the query, we have a base case of accessing the top-level
  // collection.
  $max_level = 10;

  if (count($breadcrumbs) === 0) {

    $level = $max_level;
  }

  $root = variable_get('islandora_repository_pid', 'islandora:root');
  
  if(!$is_root) {

    $is_root = $pid == $root;

    $query_string = 'select $parentObject $title $content from <#ri>
                      where (
                        <info:fedora/' . $pid . '> <fedora-model:label> $title
                        and $parentObject <fedora-model:hasModel> $content
                        and (
                          <info:fedora/' . $pid . '> <fedora-rels-ext:isMemberOfCollection> $parentObject
                          or <info:fedora/' . $pid . '> <fedora-rels-ext:isMemberOf> $parentObject
                          or <info:fedora/' . $pid . '> <fedora-rels-ext:isPartOf> $parentObject
                        )
                        and $parentObject <fedora-model:state> <info:fedora/fedora-system:def/model#Active>
                      )
                      minus $content <mulgara:is> <info:fedora/fedora-system:FedoraObject-3.0>
                      minus $parentObject <mulgara:is> <info:fedora/' . $pid . '>
                      order by $title desc';
    $results = $repository->ri->itqlQuery($query_string);

    if (count($results) > 0  && $level > 0) {
      $parent = $results[0]['parentObject']['value'];
      $this_title = $results[0]['title']['value'];

      if (empty($this_title)) {
	$this_title = t('-');
      }

      $breadcrumbs[] = l($this_title, "islandora/object/$pid");

      $level--;
    
      bootstrap_dss_swift_islandora_get_breadcrumbs_recursive($parent, $breadcrumbs, $repository, $level, $is_root);
    } else {

      // Add an non-link, as we don't know how to get back to the root.
      $breadcrumbs[] = '...';
      // And render the last two links and break (on the next pass).
      bootstrap_dss_swift_islandora_get_breadcrumbs_recursive($root, $breadcrumbs, $repository, $level, $is_root);
    }
  }
}

/**
 * Override theme_breadrumb().
 *
 * Print breadcrumbs as a list, with separators.
 */
function bootstrap_dss_swift_breadcrumb($vars) {

  /**
   * Always construct the breadcrumbs from the root upwards
   *
   */
  $breadcrumb = $vars['breadcrumb'] ?: array();
  if(preg_match('/islandora\/object\/(.+)/', current_path(), $m)) {

    $object_pid = $m[1];

    module_load_include('inc', 'islandora', 'includes/breadcrumbs');
    module_load_include('inc', 'islandora', 'includes/utilities');

    $connection = islandora_get_tuque_connection();
    bootstrap_dss_swift_islandora_get_breadcrumbs_recursive($object_pid, $breadcrumb, $connection->repository);
    $breadcrumb = array_reverse($breadcrumb);
  } else {

    $title = drupal_get_title();

    /**
     * Implemented in response to there being no clear means by which to decode the HTML character entity references within the Drupal stack
     * EDDC-184
     * @todo Refactor
     */
    $title = preg_replace('/&amp;amp;/', '&', $title);
    $title = preg_replace('/&amp;/', '&', $title);
    $title = preg_replace('/&#039;/', "'", $title);

    $breadcrumb[] = '<a href="/' . drupal_get_path_alias() . '">' . $title . '</a>';
  }

  /**
   * Render the breadcrumbs into markup
   *
   */
  if (!empty($breadcrumb)) {
    $breadcrumbs = '<ul class="breadcrumb">';
    
    $count = count($breadcrumb) - 1;
    foreach ($breadcrumb as $key => $value) {

      if ($count != $key) {
        $breadcrumbs .= '<li>' . $value . '<span class="divider">/</span></li>';
      }
      else{
        $breadcrumbs .= '<li>' . $value . '</li>';
      }
    }
    $breadcrumbs .= '</ul>';
    
    return $breadcrumbs;
  }

}
