<?php

namespace Drupal\tmgmt_deepl_glossary;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\tmgmt\TranslatorInterface;

/**
 * Provides an interface defining a deepl_glossary entity.
 *
 * @deprecated in tmgmt_deepl:2.2.12 and is removed from tmgmt_deepl:2.2.15. Use DeeplMultilingualGlossaryInterface instead.
 * @see https://www.drupal.org/project/tmgmt_deepl/issues/3522010
 */
interface DeeplGlossaryInterface extends ContentEntityInterface {

  /**
   * Returns a labeled list of allowed translators.
   *
   * @return array
   *   A list of all allowed translators.
   */
  public static function getAllowedTranslators(): array;

  /**
   * Gets the glossary id.
   *
   * @return string|null
   *   Glossary id of the deepl_glossary.
   */
  public function getGlossaryId(): ?string;

  /**
   * Gets entries count.
   *
   * @return int|null
   *   Number of glossary entries.
   */
  public function getEntryCount(): ?int;

  /**
   * Get the translator of a glossary.
   *
   * @return \Drupal\tmgmt\TranslatorInterface|null
   *   The translator entity object.
   */
  public function getTranslator(): ?TranslatorInterface;

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
   * Get matching glossary for given source and target language.
   *
   * @param string $translator
   *   Machine name of the translator.
   * @param string $source_lang
   *   Glossary source language.
   * @param string $target_lang
   *   Glossary target language.
   *
   * @return array
   *   Array of matching glossaries with id/ name relation.
   */
  public static function getMatchingGlossaries(string $translator, string $source_lang, string $target_lang): array;

  /**
   * Returns a list of valid source/ target language combinations.
   *
   * @return array
   *   A list of valid source/ target language combinations.
   */
  public static function getValidSourceTargetLanguageCombinations(): array;

}
