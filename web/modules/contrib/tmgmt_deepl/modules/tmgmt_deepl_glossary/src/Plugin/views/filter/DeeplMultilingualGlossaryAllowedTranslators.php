<?php

namespace Drupal\tmgmt_deepl_glossary\Plugin\views\filter;

use Drupal\tmgmt_deepl_glossary\Entity\DeeplMultilingualGlossary;
use Drupal\views\Plugin\views\filter\ManyToOne;

/**
 * Filter based on allowed translators for deepl_glossary.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("tmgmt_deepl_ml_glossary_allowed_translators")
 */
class DeeplMultilingualGlossaryAllowedTranslators extends ManyToOne {

  /**
   * Gets the values of the options.
   *
   * @return array
   *   Returns options.
   */
  public function getValueOptions(): array {
    $this->valueOptions = DeeplMultilingualGlossary::getAllowedTranslators();
    return $this->valueOptions;
  }

}
