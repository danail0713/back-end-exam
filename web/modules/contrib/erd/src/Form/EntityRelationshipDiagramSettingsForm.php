<?php

namespace Drupal\erd\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntityRelationshipDiagramSettingsForm.
 *
 * @ingroup erd
 */
class EntityRelationshipDiagramSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'erd_settings';
  }

  /**
   * The configuration factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs an EntityRelationshipDiagramSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('erd.settings');
    foreach ($form_state->getValues() as $key => $value) {
      if (strpos($key, 'erd') !== FALSE) {
        $config->set(str_replace('erd_', '', $key), $value);
      }
      else {
        $config->set($key, $value);
      }
    }
    $config->save();
    $this->messenger()->addMessage($this->t('Configuration was saved.'));
  }

  /**
   * Defines the settings form for Search elevate entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['erd_settings']['#markup'] = $this->t('Settings form for Entity Relationship Diagrams. Manage configuration here.');

    $config = $this->config('erd.settings');

    $form['output_format'] = [
      '#type' => 'radios',
      '#title' => $this
        ->t('Poll status'),
      '#default_value' => $config->get('output_format') ?: 'svg',
      '#options' => [
        'png' => $this->t('PNG raster image'),
        'svg' => $this->t('SVG vector image'),
      ],
    ];

    $form['field_exclude'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Fields to exclude'),
      '#default_value' => $config->get('field_exclude'),
      '#description' => $this->t('If you wish to exclude specific fields from the diagram, add a comma separated list here. Note that these should all start with "field_".'),
    ];
    $form['property_include'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Properties to include'),
      '#default_value' => $config->get('property_include'),
      '#description' => $this->t('By default, all entity properties are included in the ERD. If instead you wish to include only a few select properties, add a comma separated list here. E.g. "title, body, created". Properties not listed will be excluded from the diagram.'),
    ];

    // @todo Add state API logic to hide other fields when this is selected.
    $form['entity_reference_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Only show entity reference fields'),
      '#default_value' => $config->get('entity_reference_only'),
      '#description' => $this->t('Check this if you only wish to see entity_reference fields between entitites. All other properties and fields will be hidden.'),
    ];

    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

}
