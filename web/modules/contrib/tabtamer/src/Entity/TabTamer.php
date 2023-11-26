<?php

namespace Drupal\tabtamer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\tabtamer\TabTamerInterface;

/**
 * Defines the tab tamer entity type.
 *
 * @ConfigEntityType(
 *   id = "tab_tamer",
 *   label = @Translation("Tab tamer"),
 *   label_collection = @Translation("Tab tamers"),
 *   label_singular = @Translation("tab tamer"),
 *   label_plural = @Translation("tab tamers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count tab tamer",
 *     plural = "@count tab tamers",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\tabtamer\TabTamerListBuilder",
 *     "form" = {
 *       "add" = "Drupal\tabtamer\Form\TabTamerForm",
 *       "edit" = "Drupal\tabtamer\Form\TabTamerForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "tab_tamer",
 *   admin_permission = "administer tab_tamer",
 *   links = {
 *     "collection" = "/admin/structure/tab-tamer",
 *     "add-form" = "/admin/structure/tab-tamer/add",
 *     "edit-form" = "/admin/structure/tab-tamer/{tab_tamer}",
 *     "delete-form" = "/admin/structure/tab-tamer/{tab_tamer}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "tabs" = "tabs"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "tabs"
 *   }
 * )
 */
class TabTamer extends ConfigEntityBase implements TabTamerInterface {

  /**
   * The tab tamer ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The tab tamer label.
   *
   * @var string
   */
  protected $label;

  /**
   * The tab tamer status.
   *
   * @var bool
   */
  protected $status;

  /**
   * Tabs.
   *
   * @var array
   */
  protected $tabs = [];

  /**
   * {@inheritdoc}
   */
  public function getTabs() {
    return !empty($this->get('tabs')) ? $this->get('tabs') : [];
  }

  /**
   * {@inheritdoc}
   */
  public static function getByRoute(string $route) {
    $tabTamer = \Drupal::entityTypeManager()->getStorage('tab_tamer')->loadByProperties(['label' => $route]);
    return reset($tabTamer);
  }

}
