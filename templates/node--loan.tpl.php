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

<div class="field field-name-field-loan-filename field-type-taxonomy-term-reference field-label-above">
<div class="field-label">Facsimile: </div>
<div class="field-items">
<div class="field-item even">
<!-- <a datatype="" property="rdfs:label skos:prefLabel" typeof="skos:Concept" href="/taxonomy/term/748">ELCv2_C2_082</a> -->
      <?php print $islandora_object_link; ?>
</div>
</div>
</div>

      <?php print render($content['field_loan_shareholder']); ?>
      <?php print render($content['field_bib_rel_subject']); ?>
      <?php print render($content['field_bib_rel_object']); ?>

<div class="field field-name-field-loan-volumes-loaned field-type-text field-label-above">
<div class="field-label">Item Type: </div>
<div class="field-items">
<div class="field-item even">
      <?php print $bib_rel_object_type; ?>
</div><!--/.field-item -->
</div><!--/.field-items -->
</div><!--/.field -->

      <?php print render($content['field_loan_volumes_loaned']); ?>
      <?php print render($content['field_loan_issues_loaned']); ?>

<div class="field field-name-field-loan-volumes-loaned field-type-text field-label-above">
<div class="field-label">Checkout Date: </div>
<div class="field-items">
<div class="field-item even">
      <?php print $loan_duration_checkout; ?>
</div><!--/.field-item -->
</div><!--/.field-items -->
</div><!--/.field -->

<div class="field field-name-field-loan-volumes-loaned field-type-text field-label-above">
<div class="field-label">Return Date: </div>
<div class="field-items">
<div class="field-item even">
      <?php print $loan_duration_returned; ?>
</div><!--/.field-item -->
</div><!--/.field-items -->
</div><!--/.field -->

      <?php print render($content['field_loan_notes']); ?>
      <?php print render($content['field_loan_fine']); ?>
      <?php print render($content['field_loan_other_notes']); ?>
      <?php print render($content['field_bib_rel_type']); ?>
</div>
<?php
  //endforeach;
?>

<?php
    print render($content);
?>

  <?php if (!empty($content['field_tags']) || !empty($content['links'])): ?>
    <footer>
      <?php print render($content['field_tags']); ?>
      <?php print render($content['links']); ?>
    </footer>
  <?php endif; ?>

  <?php print render($content['comments']); ?>

</article> <!-- /.node -->
