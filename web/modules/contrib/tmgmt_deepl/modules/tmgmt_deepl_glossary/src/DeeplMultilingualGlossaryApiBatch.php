<?php

namespace Drupal\tmgmt_deepl_glossary;

use Drupal\tmgmt\TranslatorInterface;

/**
 * A service for managing DeepL glossary API batch.
 */
class DeeplMultilingualGlossaryApiBatch implements DeeplMultilingualGlossaryApiBatchInterface {

  /**
   * {@inheritDoc}
   */
  public static function fetchGlossariesAndDictionaries(TranslatorInterface $translator, bool $delete_obsolete_free_glossaries, array &$context): void {
    if (!is_array($context['sandbox'])) {
      $context['sandbox'] = [];
    }
    if (!is_array($context['results'])) {
      $context['results'] = [];
    }
    if (!isset($context['results']['translators']) || !is_array($context['results']['translators'])) {
      $context['results']['translators'] = [];
    }
    $translator_id = $translator->id();
    assert(is_string($translator_id));

    // Build context to fetch data for later processing.
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_translator'] = $translator_id;
      $context['results']['translators'][$translator_id] = [
        'translator' => $translator,
        'glossaries' => [],
      ];
    }
    // Get glossary api service.
    $glossary_api = \Drupal::service('tmgmt_deepl_glossary.ml.api');
    $glossary_api->setTranslator($translator);

    // Check account type.
    if (!is_array($context['results']['translators'][$translator_id])) {
      $context['results']['translators'][$translator_id] = [];
    }
    $is_free_account = $translator->getPluginId() == 'deepl_free';
    $context['results']['translators'][$translator_id]['is_free_account'] = $is_free_account;
    $context['results']['translators'][$translator_id]['delete_obsolete_free_glossaries'] = $is_free_account ? $delete_obsolete_free_glossaries : FALSE;

    // Get all glossaries for translator.
    $glossaries = $glossary_api->getMultilingualGlossaries();
    foreach ($glossaries as $glossary) {
      assert(is_array($glossary));
      assert(is_string($glossary['glossary_id']));
      assert(is_array($glossary['dictionaries']));

      // Build defaults.
      $glossary_data = [
        'glossary' => [
          'name' => $glossary['name'] ?? 'Glossary',
          'glossary_id' => $glossary['glossary_id'],
        ],
        'dictionaries' => $glossary['dictionaries'],
      ];

      // Get all dictionaries for glossary.
      foreach ($glossary['dictionaries'] as $key => $dictionary) {
        assert(is_array($dictionary));
        $source_lang = $dictionary['source_lang'];
        assert(is_string($source_lang));
        $target_lang = $dictionary['target_lang'];
        assert(is_string($target_lang));
        // Get all dictionary entries.
        $dictionary_entries = $glossary_api->getMultilingualGlossaryEntries($glossary['glossary_id'], $source_lang, $target_lang);
        if ($dictionary_entries) {
          if (!is_array($glossary_data['dictionaries'][$key])) {
            $glossary_data['dictionaries'][$key] = [];
          }
          $glossary_data['dictionaries'][$key]['entries'] = $dictionary_entries;
        }
      }

      assert(is_array($context['results']['translators'][$translator_id]));
      if (!isset($context['results']['translators'][$translator_id]['glossaries']) || !is_array($context['results']['translators'][$translator_id]['glossaries'])) {
        $context['results']['translators'][$translator_id]['glossaries'] = [];
      }
      $context['results']['translators'][$translator_id]['glossaries'][$glossary['glossary_id']] = $glossary_data;
      if (!is_int($context['sandbox']['progress'])) {
        $context['sandbox']['progress'] = 0;
      }
      $context['sandbox']['progress']++;
    }

    $context['message'] = t('Processed @count glossaries for translator @translator', [
      '@count' => count($glossaries),
      '@translator' => $translator->label(),
    ]);
    $context['finished'] = 1;
  }

  /**
   * {@inheritDoc}
   */
  public static function syncGlossariesFinishedCallback(bool $success, array $results, array $operations): void {
    if ($success) {
      assert(is_array($results['translators']));

      // Process results for each translator.
      foreach ($results['translators'] as $translator_id => $translator_data) {
        assert(is_array($translator_data));
        if ($translator_data['is_free_account']) {
          // Get all glossaries of free account and merge those in a single
          // glossary with multiple dictionaries.
          $glossary_ids = static::handleFreeAccountGlossaries($translator_data);

          // Clean up obsolete glossaries for free account.
          $translator = $translator_data['translator'];
          assert($translator instanceof TranslatorInterface);
          $glossary_id = reset($glossary_ids);
          assert(is_string($glossary_id));
          assert(is_bool($translator_data['delete_obsolete_free_glossaries']));
          static::processObsoleteGlossariesForFreeAccount($translator, $glossary_id, $translator_data['delete_obsolete_free_glossaries']);
        }
        else {
          $glossary_ids = static::handleProAccountGlossaries($translator_data);
        }

        // Cleanup obsolete glossary entities.
        if (count($glossary_ids) > 0) {
          static::cleanupObsoleteGlossaryEntities($translator_id, $glossary_ids);
        }
      }
      \Drupal::messenger()->addMessage(t('Glossary synchronization completed successfully.'));
    }
    else {
      \Drupal::messenger()->addError(t('Glossary synchronization encountered errors.'));
    }
  }

  /**
   * Handle glossaries for free accounts - merge all into one.
   *
   * @param array $translator_data
   *   Array containing all available glossaries for a translator.
   *
   * @return array
   *   Array of processed glossary ids.
   */
  protected static function handleFreeAccountGlossaries(array $translator_data): array {
    $translator = $translator_data['translator'];
    assert($translator instanceof TranslatorInterface);
    $all_glossaries = $translator_data['glossaries'];

    if (empty($all_glossaries) && !is_array($all_glossaries) || (is_array($all_glossaries) && count($all_glossaries) === 0)) {
      return [];
    }
    // Ensure to have array of glossaries.
    assert(is_array($all_glossaries));
    // Get first glossary.
    $first_glossary = reset($all_glossaries);
    assert(is_array($first_glossary));
    assert(is_array($first_glossary['glossary']));
    $glossary_id = $first_glossary['glossary']['glossary_id'] ?? NULL;

    // Naming of glossary in case of having more than one glossary.
    $num_glossaries = count($all_glossaries);
    $glossary_name = ($num_glossaries == 1) ? $first_glossary['glossary']['name'] : 'Merged Glossary';

    // Collect all dictionaries from all glossaries.
    $all_dictionaries = [];

    foreach ($all_glossaries as $glossary_data) {
      assert(is_array($glossary_data));
      assert(array_key_exists('dictionaries', $glossary_data));
      assert(is_array($glossary_data['dictionaries']));

      // Get all dictionaries of glossary.
      foreach ($glossary_data['dictionaries'] as $dictionary_data) {
        assert(is_array($dictionary_data));
        // Build language pair key.
        assert(is_string($dictionary_data['source_lang']));
        assert(is_string($dictionary_data['target_lang']));
        $lang_pair_key = $dictionary_data['source_lang'] . '_' . $dictionary_data['target_lang'];
        if (!isset($all_dictionaries[$lang_pair_key])) {
          $all_dictionaries[$lang_pair_key] = [
            'source_lang' => $dictionary_data['source_lang'],
            'target_lang' => $dictionary_data['target_lang'],
            'entries' => [],
          ];
        }

        // Create a set to track existing entries for this language pair.
        $existing_entries_set = [];
        foreach ($all_dictionaries[$lang_pair_key]['entries'] as $existing_entry) {
          $existing_entries_set[$existing_entry['subject']] = TRUE;
        }

        // Get all entries of glossary.
        assert(is_array($dictionary_data['entries']));
        foreach ($dictionary_data['entries'] as $entry) {
          assert(is_array($entry));
          if (!isset($existing_entries_set[$entry[0]])) {
            $all_dictionaries[$lang_pair_key]['entries'][] = [
              'subject' => $entry[0],
              'definition' => $entry[1],
            ];
            $existing_entries_set[$entry[0]] = TRUE;
          }
        }
      }
    }

    // Create/ update the single merged glossary.
    $merged_glossary = [
      'name' => $glossary_name,
      'glossary_id' => $glossary_id,
      'dictionaries' => array_values($all_dictionaries),
    ];

    // Build Drupal glossary/ glossary dictionary entities.
    $glossary = static::syncSingleGlossary($translator, $merged_glossary);

    // In case state for fetch_after_update is not set, we need to push the new
    // glossary to deepL.
    $state = \Drupal::state();
    $fetch_complete = $state->get('tmgmt_deepl_glossary.fetch_after_update', FALSE);
    if (!$fetch_complete && $glossary_id !== NULL) {
      $state->set('tmgmt_deepl_glossary.fetch_after_update', TRUE);
      // Check for existing glossaries and delete all, except the one we want
      // to update.
      /** @var \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiInterface $glossary_api */
      $glossary_api = \Drupal::service('tmgmt_deepl_glossary.ml.api');
      $glossary_api->setTranslator($translator);
      $dictionaries = $merged_glossary['dictionaries'];
      foreach ($dictionaries as $dictionary) {
        // Prepare dictionary entries for saving to API.
        $tsv_lines = [];
        foreach ($dictionary['entries'] as $entry) {
          /** @var string $subject */
          $subject = $entry['subject'];
          /** @var string $definition */
          $definition = $entry['definition'];
          $tsv_lines[] = $subject . "\t" . $definition;
        }
        $prepared_dictionary = [
          'source_lang' => $dictionary['source_lang'],
          'target_lang' => $dictionary['target_lang'],
          'entries' => implode("\r\n", $tsv_lines),
          'entries_format' => 'tsv',
        ];
        // Save dictionary to existing glossary.
        assert(is_string($glossary_id));
        assert(is_string($glossary_name));
        $glossary_api->editMultilingualGlossary($glossary_id, $glossary_name, [$prepared_dictionary]);
      }
    }

    return [$glossary->getGlossaryId()];
  }

  /**
   * Handle glossaries for pro accounts - process each separately.
   *
   * @param array $translator_data
   *   Array containing all available glossaries for a translator.
   *
   * @return array
   *   Array of glossary ids.
   */
  protected static function handleProAccountGlossaries(array $translator_data): array {
    $translator = $translator_data['translator'];
    assert($translator instanceof TranslatorInterface);
    $all_glossaries = $translator_data['glossaries'];

    if (empty($all_glossaries) && !is_array($all_glossaries)) {
      return [];
    }
    // Ensure to have array of glossaries.
    assert(is_array($all_glossaries));

    // Collect all dictionaries from all glossaries.
    $glossary_ids = [];
    foreach ($all_glossaries as $glossary_data) {
      assert(is_array($glossary_data));
      assert(array_key_exists('dictionaries', $glossary_data));
      assert(is_array($glossary_data['dictionaries']));

      $merged_dictionaries = [];
      foreach ($glossary_data['dictionaries'] as $dictionary_data) {
        assert(is_array($dictionary_data));
        // Build language pair key.
        assert(is_string($dictionary_data['source_lang']));
        assert(is_string($dictionary_data['target_lang']));
        $lang_pair_key = $dictionary_data['source_lang'] . '_' . $dictionary_data['target_lang'];

        if (!isset($merged_dictionaries[$lang_pair_key])) {
          $merged_dictionaries[$lang_pair_key] = [
            'source_lang' => $dictionary_data['source_lang'],
            'target_lang' => $dictionary_data['target_lang'],
            'entries' => [],
          ];
        }

        // Create a set to track existing entries for this language pair.
        $existing_entries_set = [];
        foreach ($merged_dictionaries[$lang_pair_key]['entries'] as $existing_entry) {
          $existing_entries_set[$existing_entry['subject']] = TRUE;
        }

        // Get all entries of glossary.
        assert(is_array($dictionary_data['entries']));
        foreach ($dictionary_data['entries'] as $entry) {
          assert(is_array($entry));
          if (!isset($existing_entries_set[$entry[0]])) {
            $merged_dictionaries[$lang_pair_key]['entries'][] = [
              'subject' => $entry[0],
              'definition' => $entry[1],
            ];
            $existing_entries_set[$entry[0]] = TRUE;
          }
        }
      }

      // Create/ update the glossary.
      assert(is_array($glossary_data['glossary']));
      $processed_glossary = [
        'name' => $glossary_data['glossary']['name'] ?? 'Unnamed Glossary',
        'glossary_id' => $glossary_data['glossary']['glossary_id'] ?? NULL,
        'dictionaries' => array_values($merged_dictionaries),
      ];
      $glossary = static::syncSingleGlossary($translator, $processed_glossary);
      $glossary_ids[] = $glossary->getGlossaryId();
    }

    return $glossary_ids;
  }

  /**
   * Sync a single glossary with its dictionaries.
   *
   * @param \Drupal\tmgmt\TranslatorInterface $translator
   *   The translator.
   * @param array $glossary_data
   *   Array containing all glossary data.
   *
   * @return \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryInterface
   *   The glossary entity object.
   */
  protected static function syncSingleGlossary(TranslatorInterface $translator, array $glossary_data): DeeplMultilingualGlossaryInterface {
    assert(array_key_exists('glossary_id', $glossary_data));
    assert(array_key_exists('name', $glossary_data));
    $glossary_id = $glossary_data['glossary_id'];

    // Get available glossaries in Drupal.
    $deepl_ml_glossary_storage = \Drupal::entityTypeManager()->getStorage('deepl_ml_glossary');
    $deepl_ml_glossary_dictionary_storage = \Drupal::entityTypeManager()->getStorage('deepl_ml_glossary_dictionary');
    $existing_glossaries = $deepl_ml_glossary_storage->loadByProperties(['glossary_id' => $glossary_id]);
    // Update existing glossary.
    if (count($existing_glossaries) > 0) {
      // Update deepl_ml_glossary entity.
      $existing_glossary = reset($existing_glossaries);
      assert($existing_glossary instanceof DeeplMultilingualGlossaryInterface);
      $existing_glossary->set('label', $glossary_data['name']);
      $existing_glossary->save();

      // Delete all deepl_ml_glossary_dictionary entities for glossary.
      $existing_dictionaries = $deepl_ml_glossary_dictionary_storage->loadByProperties(['glossary_id' => $existing_glossary->id()]);
      foreach ($existing_dictionaries as $existing_dictionary) {
        $existing_dictionary->delete();
      }
      $glossary = $existing_glossary;
    }
    else {
      // Create new glossary.
      $deepl_ml_glossary = $deepl_ml_glossary_storage->create(
        [
          'label' => $glossary_data['name'],
          'glossary_id' => $glossary_data['glossary_id'],
          'tmgmt_translator' => $translator->id(),
        ]
      );
      $deepl_ml_glossary->save();
      $glossary = $deepl_ml_glossary;
    }

    assert($glossary instanceof DeeplMultilingualGlossaryInterface);

    // Save dictionaries for glossary.
    assert(is_array($glossary_data['dictionaries']));
    foreach ($glossary_data['dictionaries'] as $dictionary_data) {
      assert(is_array($dictionary_data));
      assert(is_string($dictionary_data['source_lang']));
      assert(is_string($dictionary_data['target_lang']));
      assert(is_array($dictionary_data['entries']));
      // Ensure source_lang/ target_lang are available on site.
      $language_manager = \Drupal::languageManager();
      // Map chinese langcode.
      $source_lang = $dictionary_data['source_lang'] == 'zh' ? 'zh-hans' : $dictionary_data['source_lang'];
      $target_lang = $dictionary_data['target_lang'] == 'zh' ? 'zh-hans' : $dictionary_data['target_lang'];

      if ($language_manager->getLanguage($source_lang) && $language_manager->getLanguage($target_lang)) {
        $deepl_ml_glossary_dictionary_storage->create(
          [
            'label' => $dictionary_data['source_lang'] . ' -> ' . $dictionary_data['target_lang'],
            'glossary_id' => $glossary->id(),
            'source_lang' => strtoupper($dictionary_data['source_lang']),
            'target_lang' => strtoupper($dictionary_data['target_lang']),
            'entries' => $dictionary_data['entries'],
            'entries_format' => 'tsv',
            'entry_count' => count($dictionary_data['entries']),
          ]
        )->save();
      }
    }

    return $glossary;
  }

  /**
   * {@inheritDoc}
   */
  public static function cleanupObsoleteGlossaryEntities(string $translator, array $glossary_ids): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = \Drupal::service('entity_type.manager');
    // Delete all obsolete deepl_glossary entities.
    $deepl_glossary_storage = $entity_type_manager->getStorage('deepl_glossary');
    $glossary_entities = $deepl_glossary_storage->loadByProperties(['tmgmt_translator' => $translator]);
    /** @var \Drupal\tmgmt_deepl_glossary\DeeplGlossaryInterface $glossary_entity */
    foreach ($glossary_entities as $glossary_entity) {
      $glossary_entity->delete();
    }

    // Check for deepl_ml_glossaries and run cleanup.
    $deepl_ml_glossary_storage = $entity_type_manager->getStorage('deepl_ml_glossary');
    $ml_glossary_entities = $deepl_ml_glossary_storage->loadByProperties(['tmgmt_translator' => $translator]);
    foreach ($ml_glossary_entities as $ml_glossary_entity) {
      assert($ml_glossary_entity instanceof DeeplMultilingualGlossaryInterface);
      if (!in_array($ml_glossary_entity->getGlossaryId(), $glossary_ids)) {
        $ml_glossary_entity->delete();
      }
    }
  }

  /**
   * Clean up glossaries on deepL for free accounts and keep only one.
   *
   * @param \Drupal\tmgmt\TranslatorInterface $translator
   *   The translator.
   * @param string $glossary_id
   *   The glossary to keep while performing the cleanup.
   * @param bool $delete_obsolete_free_glossaries
   *   Whether delete obsolete free glossaries after merging or not.
   */
  protected static function processObsoleteGlossariesForFreeAccount(TranslatorInterface $translator, string $glossary_id, bool $delete_obsolete_free_glossaries): void {
    // Get glossary api service.
    /** @var \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiInterface $glossary_api */
    $glossary_api = \Drupal::service('tmgmt_deepl_glossary.ml.api');
    $glossary_api->setTranslator($translator);
    foreach ($glossary_api->getMultilingualGlossaries() as $glossary) {
      assert(is_array($glossary));
      assert(is_string($glossary['glossary_id']));
      if ($delete_obsolete_free_glossaries) {
        // Delete all glossaries except the one to keep.
        if ($glossary['glossary_id'] != $glossary_id) {
          $glossary_api->deleteMultilingualGlossary($glossary['glossary_id']);
        }
      }
      else {
        if ($glossary['glossary_id'] !== $glossary_id) {
          assert(is_string($glossary['name']));
          // Rename glossary entities to "[Deprecated] Glossary name".
          $name = str_contains($glossary['name'], '[Deprecated]') ? $glossary['name'] : '[Deprecated] ' . $glossary['name'];
          $glossary_api->editMultilingualGlossary($glossary['glossary_id'], $name, []);
        }
      }
    }
  }

}
