<?php

namespace Drupal\tmgmt_deepl_glossary;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface for a deepl_ml_glossary_dictionary entity.
 */
interface DeeplMultilingualGlossaryDictionaryInterface extends ContentEntityInterface {

  /**
   * Gets the glossary entity.
   *
   * @return \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryInterface|null
   *   Glossary entity of the dictionary.
   */
  public function getGlossary(): ?DeeplMultilingualGlossaryInterface;

  /**
   * Gets entries count.
   *
   * @return int|null
   *   Number of glossary entries.
   */
  public function getEntryCount(): ?int;

  /**
   * Gets the target language.
   *
   * @return string
   *   Glossary target language.
   */
  public function getTargetLanguage(): string;

  /**
   * Gets the source language.
   *
   * @return string
   *   Glossary source language.
   */
  public function getSourceLanguage(): string;

  /**
   * Gets the entries of the glossary in tsv format with linebreaks.
   *
   * @return string
   *   Glossary entries.
   */
  public function getEntriesString(): string;

  /**
   * Returns a list of valid source/ target language combinations.
   *
   * @return array
   *   A list of valid source/ target language combinations.
   */
  public static function getValidSourceTargetLanguageCombinations(): array;

  /**
   * Fix language mappings.
   *
   * @param string $lang_code
   *   The language code.
   *
   * @return string
   *   The mapped langcode.
   */
  public static function fixLanguageMappings(string $lang_code): string;

}
