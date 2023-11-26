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
    // Here you use parameter that you are getting from route it can be course_id/nid/id what you are getting from the route
   // To get all parameter can use this $route_match->getParameters()
   // print in the logger
    return ['course_id' => \Drupal::routeMatch()->getRawParameter('node')];
  }

}
