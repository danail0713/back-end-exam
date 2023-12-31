<?php

namespace Drupal\student_enrollment\Plugin\Menu\LocalAction;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Menu\LocalActionDefault;

/**
 * Modifies the local action to set value of dynamic parameter course_id.
 */
class EnrollCourseAddLocalAction extends LocalActionDefault {

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    // make the course_id route parameter to be equal to the current node id.
    return ['course_id' => \Drupal::routeMatch()->getRawParameter('node')];
  }

}
