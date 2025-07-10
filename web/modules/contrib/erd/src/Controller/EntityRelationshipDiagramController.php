<?php

/**
 * @file
 * Contains \Drupal\erd\Controller\EntityRelationshipDiagramController.
 */

namespace Drupal\erd\Controller;

use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;

/**
 * Contains the primary entity relationship diagram for this module.
 */
class EntityRelationshipDiagramController extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The configuration factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  protected $state;

  /**
   * Constructs a new EntityRelationshipDiagram.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory holding resource settings.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory, StateInterface $state) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('module_handler'),
      $container->get('config.factory'),
      $container->get('state')
    );
  }

  public function getMainDiagram() {
    $entity_definitions = $this->entityTypeManager->getDefinitions();
    $entities = [];
    $links = [];

    $config = $this->configFactory->get('erd.settings');
    $exclude = array_map('trim', explode(',', $config->get('field_exclude') ?? ''));
    $admin_include = $config->get('property_include') ?? '';
    if ($admin_include) {
      $admin_include = array_map('trim', explode(',', $admin_include));
    }
    $entity_reference_fields_only = $config->get('entity_reference_only');

    foreach ($entity_definitions as $definition_id => $definition) {
      $entities[] = [
        'identifier' => $definition_id,
        'id' => $definition_id,
        'type' => 'type',
        'type_label' => $this->t('Entity Type'),
        'label' => $definition->getLabel(),
      ];

      $bundles = $this->entityTypeBundleInfo->getBundleInfo($definition_id);
      foreach ($bundles as $bundle_id => $bundle_label) {
        $bundle_identifier = $definition_id . ':' . $bundle_id;
        $bundle = [
          'identifier' => $bundle_identifier,
          'id' => $bundle_id,
          'type' => 'bundle',
          'type_label' => $this->t('Entity Bundle'),
          'label' => $bundle_label['label'],
          'entity_type_label' => $definition->getLabel(),
        ];

        if ($bundle_type = $definition->getBundleEntityType()) {
          $links[] = [
            'label' => $this->t('Is a bundle of'),
            'from' => $bundle_identifier,
            'targets' => [$bundle_type, $bundle_type . ':' . $bundle_type],
          ];
        }

        if ($definition->entityClassImplements(FieldableEntityInterface::class)) {
          $bundle['fields'] = [];
          $fields = $this->entityFieldManager->getFieldDefinitions($definition_id, $bundle_id);

          foreach ($fields as $field) {
            $field_storage_definition = $field->getFieldStorageDefinition();
            $field_name = $field_storage_definition->getName();

            // Check if field/property should be skipped.
            if (in_array($field_name, $exclude)) {
              continue;
            }
            if (!empty($admin_include) && !in_array($field_name, $admin_include) && strpos($field_name, 'field_') !== 0) {
              continue;
            }
            if ($entity_reference_fields_only && $field->getType() !== 'entity_reference') {
              continue;
            }

            $field_settings = $field->getItemDefinition()->getSettings();
            $cardinality_num = $field_storage_definition->getCardinality();
            $is_required = $field->getItemDefinition()->isRequired();
            $start_num = $is_required ? 1 : 0;
            if ($cardinality_num == -1) {
              $cardinality_num = 'many';
            }
            $cardinality = '[' . $start_num . '..' . $cardinality_num . ']';
            $is_reference = in_array('Drupal\Core\Field\EntityReferenceFieldItemListInterface', class_implements($field->getClass()));

            $bundle['fields'][$field_name] = [
              'id' => $field_name,
              'label' => $field->getLabel(),
            ];
            if ($is_reference && $field_name !== $definition->getKey('bundle')) {
              $link = [
                'label' => $this->t('Reference from field "@field_name"', [
                  '@field_name' => $field_name
                ]),
                'cardinality' => $cardinality,
                'from' => $bundle_identifier,
                'from_selector' => '.attribute-background-' . $field_name,
                'targets' => [$field_settings['target_type']],
              ];

              if (isset($field_settings['handler_settings']['target_bundles']) && !empty($field_settings['handler_settings']['target_bundles'])) {
                foreach ($field_settings['handler_settings']['target_bundles'] as $target_bundle) {
                  $link['targets'][] = $field_settings['target_type'] . ':' . $target_bundle;
                }
              }
              else {
                foreach (array_keys($this->entityTypeBundleInfo->getBundleInfo($field_settings['target_type'])) as $target_bundle) {
                  $link['targets'][] = $field_settings['target_type'] . ':' . $target_bundle;
                }
              }

              $links[] = $link;
            }
            if ($field->getType() === 'comment') {
              $link = [
                'label' => $this->t('Comments from field "@field_name"', [
                  '@field_name' => $field_name
                ]),
                'cardinality' => $cardinality,
                'from' => $bundle_identifier,
                'from_selector' => '.attribute-background-' . $field_name,
                'targets' => ['comment'],
              ];

              if (isset($field_settings['comment_type']) && !empty($field_settings['comment_type'])) {
                $link['targets'][] = 'comment:' . $field_settings['comment_type'];
              }

              $links[] = $link;
            }
          }
        }
        else if ($definition instanceof ConfigEntityTypeInterface && $properties = $definition->getPropertiesToExport()) {
          $bundle['fields'] = [];
          foreach ($properties as $property) {
            if (!in_array($property, ['_core', 'third_party_settings', 'dependencies', 'status'])) {
              $bundle['fields'][$property] = [
                'id' => $property,
                'label' => $property,
              ];
            }
          }
        }

        $entities[] = $bundle;
      }
    }
    $this->moduleHandler->alter('erd_entities', $entities);
    $settings = [
      'output_format' => $config->get('output_format'),
    ];

    return [
      '#markup' =>
        '<div class="erd-actions">' .
          '<i title="Add Entity Type or Bundle" class="erd-search">' .
          '  <input type="text" placeholder="Search for entities..."/>' .
          '</i>' .
          '<i title="Add editable label" class="erd-label"></i>' .
        '<i title="Change link styles" class="erd-line-style"></i>' .
        '<i title="Toggle machine names" class="erd-machine-name"></i>' .
        '<i title="Save to image" class="erd-save"></i>' .
        '<i title="Zoom in" class="erd-zoom"></i>' .
        '<i title="Zoom out" class="erd-unzoom"></i>' .
        '</div>' .
        '<div class="erd-container"></div>',
      '#allowed_tags' => ['input', 'div', 'i'],
      '#attached' => [
        'library' => ['erd/main'],
        'drupalSettings' => [
          'erd' => [
            'entities' => $entities,
            'links' => $links,
            'settings' => $settings,
            'graphState' => $this->state->get('erd.graph'),
          ],
        ],
      ],
    ];
  }

  /**
   * AJAX callback to save diagram state.
   */
  public function saveDiagram(Request $request) {
    $this->state->set('erd.graph', $request->getContent());

    // @todo Return an empty JSON success response rather than a render array.
    // May require a fix for https://www.drupal.org/i/3106945.
    return [];
  }

}
