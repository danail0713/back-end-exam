<?php

namespace Drupal\tmgmt_deepl_glossary\Plugin\views\filter;

use Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator;
use Drupal\views\Plugin\views\filter\ManyToOne;

/**
 * Filter based on allowed languages for deepl_glossary.
 *
 * @deprecated in tmgmt_deepl:2.2.12 and is removed from tmgmt_deepl:2.2.15 since it's obsolete.
 * @see https://www.drupal.org/project/tmgmt_deepl/issues/3522010
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("tmgmt_deepl_glossary_allowed_languages")
 */
class DeeplGlossaryAllowedLanguages extends ManyToOne {

  /**
   * {@inheritdoc}
   */
  public function getValueOptions(): array {
    if (empty($this->valueOptions)) {
      // Clone the view to avoid affecting the original.
      $cloned_view = $this->view->createDuplicate();
      $display_id = 'page_1';

      // Remove this filter from the cloned view to prevent recursion.
      $cloned_view->removeHandler($display_id, 'filter', $this->options['id']);

      // Configure the cloned view to return all results.
      $cloned_view->setItemsPerPage(0);
      $cloned_view->setOffset(0);
      $cloned_view->setCurrentPage(0);

      // Execute the cloned view.
      $cloned_view->preExecute();
      $cloned_view->execute();

      // Extract languages from the cloned results.
      $source_languages = [];
      $target_languages = [];
      foreach ($cloned_view->result as $row) {
        $source_languages[] = $row->_entity->get('source_lang')->value;
        $target_languages[] = $row->_entity->get('target_lang')->value;
      }

      // Get language codes based on fields.
      $language_codes = [];
      if ($this->realField == 'source_lang') {
        $language_codes = array_unique($source_languages);
      }
      elseif ($this->realField == 'target_lang') {
        $language_codes = array_unique($target_languages);
      }

      $allowed_languages = DeeplTranslator::getSupportedRemoteSourceLanguages();
      $language_names = [];
      foreach ($language_codes as $code) {
        if (isset($allowed_languages[$code])) {
          $language_names[$code] = $allowed_languages[$code];
        }
      }

      // Sort by language name while preserving keys.
      asort($language_names);

      $this->valueOptions = $language_names;
    }

    return $this->valueOptions;
  }

}
