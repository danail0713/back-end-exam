<?php

namespace Drupal\tmgmt_deepl_glossary\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryDictionaryInterface;
use Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler for related deepl_ml_glossary_dictionary entities.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("deepl_ml_glossary_related_dictionaries")
 */
class DeeplMultilingualGlossaryRelatedDictionaries extends FieldPluginBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerFactory;

  /**
   * Constructs a new DeeplMultilingualGlossaryRelatedDictionaries object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // @phpstan-ignore-next-line
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy(): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query(): void {
    // This field doesn't need to add anything to the query.
    // We'll load the related entities in the render method.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions(): array {
    $options = parent::defineOptions();

    $options['link_to_entity'] = ['default' => TRUE];

    return $options;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-ignore-next-line
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);
    assert(is_array($form));

    $form['link_to_entity'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link to dictionary entity'),
      '#description' => $this->t('Make dictionary names link to their respective entity pages.'),
      '#default_value' => $this->options['link_to_entity'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $glossary = $this->getEntity($values);
    assert($glossary instanceof DeeplMultilingualGlossaryInterface);

    // Load related dictionary entities.
    $dictionaries = $this->getRelatedDictionaries($glossary);

    if (empty($dictionaries)) {
      return '';
    }

    $items = [];
    foreach ($dictionaries as $dictionary) {
      assert($dictionary instanceof DeeplMultilingualGlossaryDictionaryInterface);
      $name = $dictionary->label();

      if ($this->options['link_to_entity']) {
        $url = $dictionary->toUrl('edit-form', ['deepl_ml_glossary_dictionary' => $dictionary->id()]);
        $items[] = [
          '#type' => 'link',
          '#title' => $name,
          '#url' => $url,
        ];
      }
      else {
        $items[] = [
          '#markup' => $name,
        ];
      }
    }
    // @phpstan-ignore-next-line
    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#list_type' => 'ul',
    ];
  }

  /**
   * Get related dictionary entities for a glossary.
   *
   * @param \Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryInterface $glossary
   *   The glossary entity.
   *
   * @return array
   *   Array of related dictionary entities.
   */
  protected function getRelatedDictionaries(DeeplMultilingualGlossaryInterface $glossary): array {
    try {
      $dictionary_storage = $this->entityTypeManager->getStorage('deepl_ml_glossary_dictionary');
      // Get all dictionaries related to glossary.
      $query = $dictionary_storage->getQuery()
        ->condition('glossary_id', $glossary->id())
        ->accessCheck()
        ->sort('label', 'ASC');
      $dictionary_ids = $query->execute();
      if (empty($dictionary_ids)) {
        return [];
      }
      return $dictionary_storage->loadMultiple($dictionary_ids);
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('tmgmt_deepl_glossary')->error('Error loading related dictionaries: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }

}
