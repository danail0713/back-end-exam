<?php

namespace Drupal\tmgmt_deepl_glossary;

use Drupal\tmgmt\TranslatorInterface;

/**
 * Provides an interface defining DeepL glossary API batch service.
 */
interface DeeplMultilingualGlossaryApiBatchInterface {

  /**
   * Fetch glossaries and dictionaries for a translator.
   *
   * @param \Drupal\tmgmt\TranslatorInterface $translator
   *   The translator.
   * @param bool $delete_obsolete_free_glossaries
   *   Whether delete obsolete free glossaries after merging or not.
   * @param array $context
   *   Context for operation.
   */
  public static function fetchGlossariesAndDictionaries(TranslatorInterface $translator, bool $delete_obsolete_free_glossaries, array &$context): void;

  /**
   * Finished callback for glossary sync batch.
   *
   * @param bool $success
   *   Whether the batch run was successful.
   * @param array $results
   *   The collected results coming from batch context.
   * @param array $operations
   *   The processed operations.
   */
  public static function syncGlossariesFinishedCallback(bool $success, array $results, array $operations): void;

  /**
   * Clean up obsolete deepl_glossary, deepl_ml_glossary entities.
   *
   * @param string $translator
   *   The name of the translator.
   * @param array $glossary_ids
   *   Array of glossary ids to keep.
   */
  public static function cleanupObsoleteGlossaryEntities(string $translator, array $glossary_ids): void;

}
