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
   * Get tab amer entity by route name.
   *
   * @param string $route
   *   Route.
   *
   * @return mixed
   */
  public static function getByRoute(string $route);

}
