<?php

namespace Drupal\tmgmt_deepl_glossary;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\tmgmt\TranslatorInterface;

/**
 * Provides an interface defining a deepl_ml_glossary entity.
 */
interface DeeplMultilingualGlossaryInterface extends ContentEntityInterface {

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
   * Get the translator of a glossary.
   *
   * @return \Drupal\tmgmt\TranslatorInterface|null
   *   The translator entity object.
   */
  public function getTranslator(): ?TranslatorInterface;

}
