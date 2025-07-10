<?php

namespace Drupal\tmgmt_deepl_glossary\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\tmgmt\TranslatorInterface;
use Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryDictionaryInterface;
use Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the DeepL multilingual glossary entity.
 *
 * @ContentEntityType(
 *   id = "deepl_ml_glossary",
 *   label = @Translation("DeepL glossary"),
 *   label_singular = @Translation("DeepL glossary"),
 *   label_plural = @Translation("DeepL glossaries"),
 *   handlers = {
 *     "access" = "Drupal\tmgmt_deepl_glossary\AccessControlHandler",
 *     "list_builder" = "Drupal\tmgmt_deepl_glossary\Controller\DeeplMultilingualGlossaryListBuilder",
 *     "views_data" = "Drupal\tmgmt_deepl_glossary\Entity\ViewsData\DeeplMultilingualGlossaryViewsData",
 *     "form" = {
 *       "default" = "Drupal\tmgmt_deepl_glossary\Form\DeeplMultilingualGlossaryForm",
 *       "add" = "Drupal\tmgmt_deepl_glossary\Form\DeeplMultilingualGlossaryForm",
 *       "edit" = "Drupal\tmgmt_deepl_glossary\Form\DeeplMultilingualGlossaryForm",
 *       "delete" = "Drupal\tmgmt_deepl_glossary\Form\DeeplMultilingualGlossaryDeleteForm",
 *     },
 *     "route_provider" = {
 *        "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "tmgmt_deepl_ml_glossary",
 *   translatable = FALSE,
 *   admin_permission = "administer deepl_glossary entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "add-form" = "/admin/tmgmt/deepl_glossaries/add",
 *     "edit-form" = "/admin/tmgmt/deepl_glossaries/manage/{deepl_ml_glossary}/edit",
 *     "delete-form" = "/admin/tmgmt/deepl_glossaries/manage/{deepl_ml_glossary}/delete",
 *     "collection" = "/admin/tmgmt/deepl_glossaries",
 *   }
 * )
 */
class DeeplMultilingualGlossary extends ContentEntityBase implements DeeplMultilingualGlossaryInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    // The machine name of the translator.
    $fields['tmgmt_translator'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Translator'))
      ->setDescription(t('The tmgmt translator.'))
      ->setSetting('allowed_values_function', static::class . '::getAllowedTranslators')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'options_select',
      ])
      ->setDisplayConfigurable('form', TRUE);

    // Name associated with the glossary.
    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the glossary.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string',
      ])
      ->setDisplayConfigurable('form', TRUE);

    // The user id of the current user.
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The author of the glossary entry.'))
      ->setSetting('target_type', 'user')
      ->setReadOnly(TRUE);

    // A unique ID assigned to the glossary (values is retrieved by DeepL API)
    $fields['glossary_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Glossary Id'))
      ->setDescription(t('The glossary id.'));

    // The time that the entity was created.
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    // The time that the entity was changed.
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last changed.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getGlossaryId(): ?string {
    /** @var string $glossary_id */
    $glossary_id = $this->get('glossary_id')->value ?? NULL;
    return $glossary_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getTranslator(): ?TranslatorInterface {
    $tmgmt_translator_storage = $this->entityTypeManager()->getStorage('tmgmt_translator');
    $translator = $this->get('tmgmt_translator')->value;
    /** @var \Drupal\tmgmt\TranslatorInterface $translator */
    $translator = $tmgmt_translator_storage->load($translator);
    return $translator;
  }

  /**
   * {@inheritDoc}
   */
  public static function getAllowedTranslators(): array {
    $tmgmt_translator_storage = \Drupal::entityTypeManager()->getStorage('tmgmt_translator');
    $deepl_translators = $tmgmt_translator_storage->loadByProperties([
      'plugin' => [
        'deepl_pro',
        'deepl_free',
      ],
    ]);

    return array_map(function ($deepl_translator) {
      return $deepl_translator->label();
    }, $deepl_translators);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);

    // Set uid to current user.
    $this->set('uid', self::getDefaultEntityOwner());
  }

  /**
   * {@inheritdoc}
   */
  public function delete(): void {
    parent::delete();
    // Delete all corresponding glossary dictionaries.
    $storage = $this->entityTypeManager()->getStorage('deepl_ml_glossary_dictionary');
    $dictionaries = $storage->loadByProperties(['glossary_id' => $this->id()]);
    foreach ($dictionaries as $dictionary) {
      $dictionary->delete();
    }
  }

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
  public static function getMatchingGlossaries(string $translator, string $source_lang, string $target_lang): array {
    // Get all glossaries for translator.
    $glossary_ids = \Drupal::entityQuery('deepl_ml_glossary')
      ->condition('tmgmt_translator', $translator)
      ->accessCheck(FALSE)
      ->execute();
    if (empty($glossary_ids)) {
      return [];
    }
    // Fix language mapping.
    $source_lang = DeeplMultilingualGlossaryDictionary::fixLanguageMappings($source_lang);
    $target_lang = DeeplMultilingualGlossaryDictionary::fixLanguageMappings($target_lang);

    // Get matching dictionaries.
    $dictionary_entity_ids = \Drupal::entityQuery('deepl_ml_glossary_dictionary')
      ->condition('source_lang', $source_lang)
      ->condition('target_lang', $target_lang)
      ->condition('glossary_id', $glossary_ids, 'IN')
      ->accessCheck(FALSE)
      ->execute();

    // If no dictionary entries match the criteria, return an empty array.
    if (empty($dictionary_entity_ids) || !is_array($dictionary_entity_ids)) {
      return [];
    }
    $dictionary_storage = \Drupal::entityTypeManager()->getStorage('deepl_ml_glossary_dictionary');
    $dictionary_entities = $dictionary_storage->loadMultiple($dictionary_entity_ids);
    $matching_glossaries = [];
    foreach ($dictionary_entities as $dictionary_entity) {
      assert($dictionary_entity instanceof DeeplMultilingualGlossaryDictionaryInterface);
      $glossary = $dictionary_entity->get('glossary_id')->entity;
      assert($glossary instanceof DeeplMultilingualGlossaryInterface);
      $matching_glossaries[$glossary->id()] = $glossary->label();
    }
    return $matching_glossaries;
  }

}
