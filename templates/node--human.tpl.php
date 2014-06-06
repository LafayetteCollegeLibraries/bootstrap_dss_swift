<article id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix"<?php print $attributes; ?>>


  <header>
    <?php print render($title_prefix); ?>
    <?php if (!$page && $title): ?>
      <h2<?php print $title_attributes; ?>><a href="<?php print $node_url; ?>"><?php print $title; ?></a></h2>
    <?php endif; ?>
    <?php print render($title_suffix); ?>

    <?php if ($display_submitted): ?>
      <span class="submitted">
        <?php print $user_picture; ?>
        <?php print $submitted; ?>
      </span>
    <?php endif; ?>
  </header>

  <?php
    // Hide comments, tags, and links now so that we can render them later.
    hide($content['comments']);
    hide($content['links']);
    hide($content['field_tags']);
?>

<?php
  //foreach( ):
?>
<div class="field-entries">
      <?php print render($content['field_person_name']); ?>
      <?php print render($content['field_human_gender']); ?>
      <?php print render($content['field_human_occupation']); ?>
      <?php print render($content['field_person_membership']); ?>
      <?php print render($content['field_human_pers_rels']); ?>
      <?php print render($content['field_person_type']); ?>
</div>
<?php
  //endforeach;
?>

<?php
    print render($content);
?>

<?php print $loans_view ?>

  <?php if (!empty($content['field_tags']) || !empty($content['links'])): ?>
    <footer>
      <?php print render($content['field_tags']); ?>
      <?php print render($content['links']); ?>
    </footer>
  <?php endif; ?>

  <?php print render($content['comments']); ?>

</article> <!-- /.node -->
