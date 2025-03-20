<?php

namespace Drupal\tabtamer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a tab tamer entity type.
 */
interface TabTamerInterface extends ConfigEntityInterface {

  /**
   * Get tabs.
   *
   * @return array
   *   Tabs.
   */
  public function getTabs();

  /**
   * Get tab tamer entity by route name.
   *
   * @param string $route
   *   Route.
   *
   * @return mixed
   * @return static|null
   *   The tab tamer entity or NULL if not found.
   */
  public static function getByRoute(string $route);

}
