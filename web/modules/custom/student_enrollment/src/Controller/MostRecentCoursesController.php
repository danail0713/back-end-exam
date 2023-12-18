<?php

namespace Drupal\student_enrollment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

class MostRecentCoursesController extends ControllerBase {

  public function build() {
    $courses = $this->fetchCourses();
    $build = [
      '#theme' => 'most-recent-courses',
      '#courses' => $courses
    ];
    return $build;
  }

  private function fetchCourses() {
    $course_ids = \Drupal::entityQuery('node')
      ->condition('type', 'courses')
      ->sort('created', 'DESC')
      ->range(0, 5)
      ->accessCheck(true)
      ->execute();
    $courses = Node::loadMultiple($course_ids);
    return $courses;
  }
}
