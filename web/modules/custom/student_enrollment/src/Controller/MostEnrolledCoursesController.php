<?php

namespace Drupal\student_enrollment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

class MostEnrolledCoursesController extends ControllerBase {

  public function build() {
    $most_enrolled_courses = $this->fetchCourses();
    $courses_for_render = [];
    foreach ($most_enrolled_courses as $course_id => $course_count) {
      $course_name = Node::load($course_id)->label();
      $course_for_render = [
        'name' => $course_name,
        'count_enrolled' => $course_count
      ];
      $courses_for_render[] = $course_for_render;
    }

    $build = [
      '#theme' => 'most-enrolled-courses',
      '#courses' => $courses_for_render,
    ];
    return $build;
  }

  private function fetchCourses() {
    $query = \Drupal::database()
      ->select('student_enrollments', 'se')
      ->fields('se', ['course_id']);
    $query->addExpression('count(*)', 'count_enrolled');
    $query->groupBy('course_id');
    $query->having('count(*) >= :matches', [':matches' => 2]);
    $query->orderBy('count_enrolled', 'DESC');
    $most_enrolled_courses = $query->execute()->fetchAllKeyed();
    return $most_enrolled_courses;
  }
}
