<?php

namespace Drupal\tabtamer\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tab tamer form.
 *
 * @property \Drupal\tabtamer\TabTamerInterface $entity
 */
class TabTamerForm extends EntityForm {

  /**
   * Local task manager.
   *
   * @var \Drupal\Core\Menu\LocalTaskManagerInterface
   */
  protected $localTaskManager;

  /**
   * RequestStack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * TabTamerForm constructor.
   *
   * @param \Drupal\Core\Menu\LocalTaskManagerInterface $localTaskManager
   *   Local task manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   RequestStack.
   */
  public function __construct(LocalTaskManagerInterface $localTaskManager, RequestStack $requestStack) {
    $this->localTaskManager = $localTaskManager;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.menu.local_task'), $container->get('request_stack'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);
    $route = $this->requestStack->getCurrentRequest()->query->get('route');
    $isEntity = !empty($this->entity->id());

    if (is_null($route)) {
      $route = $form_state->getValue('label');

      if (is_null($route) && $isEntity) {
        $route = $this->entity->label();
      }
    }

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Route name'),
      '#maxlength' => 255,
      '#default_value' => $route,
      '#description' => $this->t('Label for the tab tamer.'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::tabTamerAjaxCallback',
        'event' => 'change',
        'wrapper' => 'tab-tamer-tabs-wrapper',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Checking route...'),
        ],
      ],
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $isEntity ? $this->entity->id() : str_replace('.', '_', $route ?? ''),
      '#machine_name' => [
        'exists' => '\Drupal\tabtamer\Entity\TabTamer::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];

    $group_class = 'group-order-weight';
    $tabs = $this->entity->getTabs();

    $getTabs = $this->localTaskManager->getLocalTasksForRoute($route);
    $getTabs = reset($getTabs);

    if ($getTabs) {
      /** @var \Drupal\Core\Menu\LocalTaskDefault $val */
      foreach ($getTabs as $val) {
        $options = $val->getPluginDefinition();
        // Load a new tabs for current route.
        if (array_search($val->getPluginId(), array_column($tabs, 'label')) === FALSE) {
          $tabs[] = [
            'weight' => $options['weight'],
            'label' => $val->getPluginId(),
            'link' => $options['title'],
            'access' => TRUE,
          ];
        }
      }
    }

    // Build table.
    $form['tabs'] = [
      '#prefix' => '<div id="tab-tamer-tabs-wrapper">',
      '#suffix' => '</div>',
      '#type' => 'table',
      '#caption' => $this->t('Tabs'),
      '#header' => [
        $this->t('Route'),
        $this->t('Link title'),
        $this->t('Display'),
        $this->t('Weight'),
      ],
      '#empty' => $this->t('No items.'),
      '#tableselect' => FALSE,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class,
        ],
      ],
    ];

    // Build rows.
    foreach ($tabs as $key => $tab) {
      $form['tabs'][$key]['#attributes']['class'][] = 'draggable';
      $form['tabs'][$key]['#weight'] = $tab['weight'];

      // Label col.
      $form['tabs'][$key]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Route'),
        '#default_value' => $tab['label'],
        '#required' => TRUE,
        '#disabled' => TRUE,
      ];

      // Link title.
      $form['tabs'][$key]['link'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Link title'),
        '#default_value' => $tab['link'],
        '#required' => TRUE,
      ];

      // ID col.
      $form['tabs'][$key]['access'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Display'),
        '#default_value' => $tab['access'],
      ];

      // Weight col.
      $form['tabs'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $key]),
        '#title_display' => 'invisible',
        '#default_value' => $tab['weight'],
        '#attributes' => ['class' => [$group_class]],
      ];
    }

    return $form;
  }

  /**
   * Ajax callback.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   Form state.
   *
   * @return mixed
   *   Rendered array.
   */
  public function tabTamerAjaxCallback(array &$form, FormStateInterface $formState) {
    return $form['tabs'];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new tab tamer %label.', $message_args)
      : $this->t('Updated tab tamer %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
