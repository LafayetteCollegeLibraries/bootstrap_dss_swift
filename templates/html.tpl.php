<!DOCTYPE html>
<html lang="<?php print $language->language; ?>" dir="<?php print $language->dir; ?>"<?php print $rdf_namespaces;?>>
<head profile="<?php print $grddl_profile; ?>">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php print $head; ?>
  <title><?php print $head_title; ?></title>
  <?php print $styles; ?>
  <!-- HTML5 element support for IE6-8 -->
  <!--[if lt IE 9]>
    <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  <?php print $scripts; ?>
  <!-- @author griffinj@lafayette.edu: Work-around for views caching issues -->
  <!-- @todo Resolve EDDC-160 -->
  <style>@import url("/sites/all/libraries/DataTables/media/css/jquery.dataTables.css");</style>
  <script src="/sites/all/libraries/DataTables/media/js/jquery.dataTables.js"></script>
  <script src="/sites/all/modules/islandora_dss_solution_pack_elc/js/build/dss_elc_views_filter.min.js"></script>
</head>
<body class="<?php print $classes; ?>" <?php print $attributes;?> data-spy="scroll" data-target="#navbar-header">



    <div id="skip-link">
      <a href="#main-content" class="element-invisible element-focusable"><?php print t('Skip to main content'); ?></a>
    </div>
    <?php print $page_top; ?>
    <?php print $page; ?>
    <?php print $page_bottom; ?>

</body>
</html>
