<?php

namespace Drupal\tmgmt_deepl_glossary\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\tmgmt\TranslatorInterface;
use Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator;
use Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiInterface;
use Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryDictionaryInterface;
use Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryInterface;
use Drupal\tmgmt_deepl_glossary\Entity\DeeplMultilingualGlossaryDictionary;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for deepl_ml_glossary_dictionary edit forms.
 *
 * @ingroup tmgmt_deepl_glossary
 */
class DeeplMultilingualGlossaryDictionaryForm extends ContentEntityForm {

  /**
   * The DeepL glossary API service.
   *
   * @var \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiInterface
   */
  protected DeeplMultilingualGlossaryApiInterface $glossaryApi;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $account;

  /**
   * Constructs a DeeplGlossaryForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiInterface $glossary_api
   *   The DeepL glossary API service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, DeeplMultilingualGlossaryApiInterface $glossary_api, AccountInterface $account) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);

    $this->glossaryApi = $glossary_api;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('tmgmt_deepl_glossary.ml.api'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    // Get current dictionary entity.
    $dictionary = $this->entity;
    assert($dictionary instanceof DeeplMultilingualGlossaryDictionaryInterface);

    // Handling of new dictionary entities.
    $deepl_ml_glossary_id = $this->getRouteMatch()->getParameter('deepl_ml_glossary');
    if ($dictionary->isNew() && $deepl_ml_glossary_id !== NULL) {
      // Load available languages based on translator.
      // We need to set the glossary_id manually to proceed.
      $dictionary->set('glossary_id', $deepl_ml_glossary_id);
    }

    // Retrieve glossary and translator for available languages.
    $glossary = $dictionary->getGlossary();
    assert($glossary instanceof DeeplMultilingualGlossaryInterface);
    $translator = $glossary->getTranslator();
    assert($translator instanceof TranslatorInterface);

    // Available languages handling.
    $language_mappings = $translator->getRemoteLanguagesMappings();
    $source_languages = DeeplTranslator::getSupportedRemoteSourceLanguages();
    $available_languages = [];
    foreach ($language_mappings as $language_mapping) {
      assert(is_string($language_mapping));
      $language_mapping = DeeplMultilingualGlossaryDictionary::fixLanguageMappings($language_mapping);
      if (isset($source_languages[$language_mapping])) {
        $available_languages[$language_mapping] = $source_languages[$language_mapping];
      }
    }
    asort($available_languages);

    // Source language.
    assert(is_array($form['source_lang']));
    assert(is_array($form['source_lang']['widget']));
    $form['source_lang']['widget']['#attributes'] = !$dictionary->isNew() ? ['disabled' => 'disabled'] : [];
    $form['source_lang']['widget']['#empty_option'] = $this->t('- Select source language -');
    $form['source_lang']['widget']['#options'] = $available_languages;

    // Target language.
    assert(is_array($form['target_lang']));
    assert(is_array($form['target_lang']['widget']));
    $form['target_lang']['widget']['#attributes'] = !$dictionary->isNew() ? ['disabled' => 'disabled'] : [];
    $form['target_lang']['widget']['#empty_option'] = $this->t('- Select target language -');
    $form['target_lang']['widget']['#options'] = $available_languages;

    // Entries search form.
    if ($form['entries'] && is_array($form['entries'])) {
      // Add wrapper class to entries field for easier targeting.
      $form['entries']['#attributes']['class'][] = 'entries-multivalue-wrapper';

      // Add entries search.
      assert(is_numeric($form['entries']['#weight']));
      $form['entries_search'] = [
        '#type' => 'container',
        '#weight' => $form['entries']['#weight'] - 1,
        '#attributes' => [
          'class' => ['entries-search-container'],
        ],
      ];

      // Add search input field.
      $form['entries_search']['search_input'] = [
        '#type' => 'textfield',
        '#placeholder' => $this->t('Type at least 3 characters to search...'),
        '#attributes' => [
          'class' => ['entries-search-input'],
          'autocomplete' => 'off',
        ],
        '#size' => 50,
      ];
      // Add reset button.
      $form['entries_search']['reset_button'] = [
        '#type' => 'button',
        '#value' => $this->t('Reset'),
        '#attributes' => [
          'class' => ['entries-reset-button', 'button'],
          'type' => 'button',
        ],
      ];

      // Add css/ js library.
      $form['#attached']['library'][] = 'tmgmt_deepl_glossary/tmgmt_deepl_glossary.entries_search';

    }

    // Cancel link.
    assert(is_array($form['actions']));
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => Url::fromRoute('entity.deepl_ml_glossary.edit_form', ['deepl_ml_glossary' => $glossary->id()]),
      '#weight' => 8,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): ContentEntityInterface {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryDictionaryInterface $entity */
    $entity = $this->buildEntity($form, $form_state);

    // Validate matching source, target language.
    $this->validateSourceTargetLanguage($form, $form_state);

    // Validate unique entries.
    $this->validateUniqueEntries($form, $form_state);

    // Validate unique dictionary within glossary.
    if ($entity->isNew()) {
      $this->validateUniqueDictionary($form, $form_state, $entity);
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    // Get dictionary.
    $dictionary = $this->entity;
    assert($dictionary instanceof DeeplMultilingualGlossaryDictionaryInterface);
    $status = $dictionary->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created dictionary %label.', ['%label' => $dictionary->label()]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Updated dictionary %label DeepL glossary.', ['%label' => $dictionary->label()]));
    }

    // Add redirect to glossary form.
    $glossary = $dictionary->getGlossary();
    assert($glossary instanceof DeeplMultilingualGlossaryInterface);
    $form_state->setRedirect('entity.deepl_ml_glossary.edit_form', ['deepl_ml_glossary' => $glossary->id()]);

    // Save DeepL glossary dictionary to DeepL API.
    $this->saveDeeplGlossaryDictionary($dictionary, $status);

    return $status;
  }

  /**
   * Save DeepL glossary dictionary to DeepL API.
   *
   * @param \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryDictionaryInterface $dictionary
   *   The DeepL glossary dictionary entity object.
   * @param int $status
   *   The save status (indicator for new or existing entities)
   */
  protected function saveDeeplGlossaryDictionary(DeeplMultilingualGlossaryDictionaryInterface $dictionary, int $status): void {
    // Retrieve glossary and translator for available languages.
    $glossary = $dictionary->getGlossary();
    assert($glossary instanceof DeeplMultilingualGlossaryInterface);
    $translator = $glossary->getTranslator();
    assert($translator instanceof TranslatorInterface);

    $glossary_api = $this->glossaryApi;
    $glossary_api->setTranslator($translator);

    // Save glossary dictionary to DeepL API.
    $glossary_id = $glossary->getGlossaryId();
    assert(is_string($glossary_id));
    $result = $glossary_api->createMultilingualGlossaryDictionary($glossary_id, $dictionary->getSourceLanguage(), $dictionary->getTargetLanguage(), $dictionary->getEntriesString());
    if (isset($result['entry_count'])) {
      // Save result count to dictionary.
      $dictionary->set('entry_count', $result['entry_count']);
      $dictionary->save();
    }

  }

  /**
   * Validate valid source/ target language pair.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function validateSourceTargetLanguage(array &$form, FormStateInterface $form_state): void {
    $user_input = $form_state->getValues();
    assert(is_array($user_input['source_lang']));
    assert(is_array($user_input['source_lang'][0]));
    $source_lang = $user_input['source_lang'][0]['value'] ?? '';
    assert(is_array($user_input['target_lang']));
    assert(is_array($user_input['target_lang'][0]));
    $target_lang = $user_input['target_lang'][0]['value'] ?? '';

    // Define valid language pairs.
    $valid_language_pairs = DeeplMultilingualGlossaryDictionary::getValidSourceTargetLanguageCombinations();

    // Get valid match for source/ target language..
    $match = FALSE;
    foreach ($valid_language_pairs as $valid_language_pair) {
      assert(is_array($valid_language_pair));
      if (isset($valid_language_pair[$source_lang]) && ($valid_language_pair[$source_lang] == $target_lang)) {
        $match = TRUE;
      }
    }

    // If we don't find a valid math, set error to fields.
    if (!$match) {
      $message = $this->t('Select a valid source/ target language.');
      $form_state->setErrorByName('source_lang', $message);
      $form_state->setErrorByName('target_lang', $message);
    }
  }

  /**
   * Validate unique entries.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  protected function validateUniqueEntries(array &$form, FormStateInterface $form_state): void {
    $user_input = $form_state->getUserInput();
    $entries = $user_input['entries'] ?? [];
    $subjects = [];
    assert(is_array($entries));
    foreach ($entries as $entry) {
      assert(is_array($entry));
      if (isset($entry['subject']) && $entry['subject'] !== '') {
        $subjects[] = $entry['subject'];
      }
    }

    // Duplicate check.
    $unique_subjects = array_unique($subjects);
    $duplicates = array_diff_assoc($subjects, $unique_subjects);
    if (count($duplicates) > 0) {
      foreach (array_keys($duplicates) as $key) {
        $form_state->setErrorByName('entries][' . $key . '][subject', $this->t('Please check your dictionary entries, the "Source text" should be unique.'));
      }
    }
  }

  /**
   * Validate unique dictionary for source/ target within glossary.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryDictionaryInterface $dictionary
   *   The entity object.
   */
  protected function validateUniqueDictionary(array &$form, FormStateInterface $form_state, DeeplMultilingualGlossaryDictionaryInterface $dictionary): void {
    // Check available dictionaries for the glossary.
    $glossary = $dictionary->getGlossary();
    assert($glossary instanceof DeeplMultilingualGlossaryInterface);
    $translator = $glossary->getTranslator();
    assert($translator instanceof TranslatorInterface);

    $glossary_api = $this->glossaryApi;
    $glossary_api->setTranslator($translator);
    $glossary_id = $glossary->getGlossaryId();
    assert(is_string($glossary_id));

    if ($glossary_api->hasMultilingualGlossaryDictionary($glossary_id, $dictionary->getSourceLanguage(), $dictionary->getTargetLanguage())) {
      $message = $this->t('You cannot add more than one glossary for the selected source/ target language combination.');
      $form_state->setErrorByName('source_lang', $message);
      $form_state->setErrorByName('target_lang', $message);
    }
  }

}
