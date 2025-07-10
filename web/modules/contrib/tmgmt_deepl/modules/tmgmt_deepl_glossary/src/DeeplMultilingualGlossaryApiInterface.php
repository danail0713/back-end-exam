<?php

namespace Drupal\tmgmt_deepl_glossary;

use Drupal\tmgmt\TranslatorInterface;

/**
 * Provides an interface defining DeepL glossary API service.
 */
interface DeeplMultilingualGlossaryApiInterface {

  /**
   * Build glossary fetch batch for available deepl translators.
   *
   * @param bool $delete_obsolete_free_glossaries
   *   Whether delete obsolete free glossaries after merging or not.
   */
  public function buildGlossariesFetchBatch(bool $delete_obsolete_free_glossaries = FALSE): void;

  /**
   * Creates a new multilingual glossary.
   *
   * @param string $name
   *   The name to be associated with the glossary.
   * @param array $dictionaries
   *   An array of dictionary definitions.
   *
   * @return array
   *   An associative array containing the results after creating the glossary.
   */
  public function createMultilingualGlossary(string $name, array $dictionaries): array;

  /**
   * Edit glossary metadata.
   *
   * @param string $glossary_id
   *   The unique ID assigned to the glossary.
   * @param string $name
   *   Name to be associated with the glossary.
   * @param array $dictionaries
   *   An array of dictionary definitions.
   *
   * @return array
   *   Array with results after creating a glossary.
   */
  public function editMultilingualGlossary(string $glossary_id, string $name, array $dictionaries = []): array;

  /**
   * Delete glossary for a given glossary id.
   *
   * @param string $glossary_id
   *   The unique ID assigned to the glossary.
   */
  public function deleteMultilingualGlossary(string $glossary_id): void;

  /**
   * Delete an existing glossary dictionary.
   *
   * @param string $glossary_id
   *   The unique ID assigned to the glossary.
   * @param string $source_lang
   *   The language in which the source texts in the glossary are specified.
   * @param string $target_lang
   *   The language in which the target texts in the glossary are specified.
   */
  public function deleteMultilingualGlossaryDictionary(string $glossary_id, string $source_lang, string $target_lang): void;

  /**
   * Make API requests.
   *
   * @param string $url
   *   The url for the request.
   * @param string $method
   *   HTTP method of the API request (can be GET or POST).
   * @param array $query_params
   *   Query params to be passed into the request.
   * @param array $headers
   *   Additional headers for request.
   *
   * @return array|null
   *   Array with results of the request.
   */
  public function doRequest(string $url, string $method, array $query_params, array $headers): ?array;

  /**
   * Get all glossaries for active translator.
   *
   * @return array
   *   Array of available glossaries.
   */
  public function getMultilingualGlossaries(): array;

  /**
   * Get entries for a given glossary id.
   *
   * @param string $glossary_id
   *   The unique ID assigned to the glossary.
   * @param string $source_lang
   *   The language in which the source texts in the glossary are specified.
   * @param string $target_lang
   *   The language in which the target texts in the glossary are specified.
   *
   * @return array
   *   Array of glossary entries.
   */
  public function getMultilingualGlossaryEntries(string $glossary_id, string $source_lang, string $target_lang): array;

  /**
   * Get metadata for a given glossary id.
   *
   * @param string $glossary_id
   *   The unique ID assigned to the glossary.
   *
   * @return array
   *   Array of glossary metadata (name, dictionaries).
   */
  public function getMultilingualGlossaryMetadata(string $glossary_id): array;

  /**
   * Check if a dictionary exists in a given glossary.
   *
   * @param string $glossary_id
   *   The unique ID assigned to the glossary.
   * @param string $source_lang
   *   The language in which the source texts in the glossary are specified.
   * @param string $target_lang
   *   The language in which the target texts in the glossary are specified.
   *
   * @return bool
   *   Whether an existing glossary dictionary exists.
   */
  public function hasMultilingualGlossaryDictionary(string $glossary_id, string $source_lang, string $target_lang): bool;

  /**
   * Set translator for all glossary API calls.
   *
   * @param \Drupal\tmgmt\TranslatorInterface $translator
   *   The translator entity.
   */
  public function setTranslator(TranslatorInterface $translator): void;

  /**
   * Replace or create a dictionary in the glossary with the specified entries.
   *
   * @param string $glossary_id
   *   The unique ID assigned to the glossary.
   * @param string $source_lang
   *   The language in which the source texts in the glossary are specified.
   * @param string $target_lang
   *   The language in which the target texts in the glossary are specified.
   * @param string $entries
   *   The entries of the dictionary.
   * @param string $entries_format
   *   The entries format of the dictionary.
   *
   * @return array
   *   Array with results after updating the glossary.
   */
  public function createMultilingualGlossaryDictionary(string $glossary_id, string $source_lang, string $target_lang, string $entries, string $entries_format = 'tsv'): array;

}
