<?php

namespace Drupal\tmgmt_deepl_glossary\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for fetching glossaries from the DeepL API.
 *
 * @ingroup tmgmt_deepl_glossary
 */
class DeeplMultilingualGlossaryFetchForm extends ConfirmFormBase {

  /**
   * The DeepL glossary API service.
   *
   * @var \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiInterface
   */
  protected DeeplMultilingualGlossaryApiInterface $glossaryApi;

  /**
   * Constructs a DeeplMultilingualGlossaryFetchForm object.
   *
   * @param \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiInterface $glossary_api
   *   The DeepL glossary API service.
   */
  public function __construct(DeeplMultilingualGlossaryApiInterface $glossary_api) {
    $this->glossaryApi = $glossary_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('tmgmt_deepl_glossary.ml.api'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'tmgmt_deepl_ml_glossary_fetch_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText(): TranslatableMarkup {
    return $this->t('Fetch DeepL glossaries');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): TranslatableMarkup {
    return $this->t('This action will fetch all DeepL glossaries via the DeepL API.');
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Do you want to fetch the latest DeepL glossaries via the DeepL API?');
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl(): Url {
    return new Url('entity.deepl_ml_glossary.collection');
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    // Add checkbox for deleting obsolete glossaries (Free Accounts).
    $form['delete_obsolete_free_glossaries'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete obsolete glossaries for account type "DeepL API Free"'),
      '#description' => $this->t('For account type "DeepL API Free" only 1 glossary with multiple dictionaries is allowed. Selecting the option will automatically delete obsolete glossaries via the DeepL API after merging existing glossaries into a new glossary called "Merged Glossary". Otherwise existing glossaries will be renamed to "[Deprecated] Glossary name" and can be deleted manually on <a href="https://www.deepl.com/en/glossary" target="_blank">https://www.deepl.com/en/glossary.</a>'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Build sync batch.
    $delete_obsolete_free_glossaries = (bool) $form_state->getValue('delete_obsolete_free_glossaries');
    $this->glossaryApi->buildGlossariesFetchBatch($delete_obsolete_free_glossaries);

    // Redirect to glossary overview.
    $form_state->setRedirect('entity.deepl_ml_glossary.collection');
  }

}
