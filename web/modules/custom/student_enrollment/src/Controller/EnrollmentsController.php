<?php

namespace Drupal\student_enrollment\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

class EnrollmentsController extends ControllerBase {
  public function build() {
    $enrollments = \Drupal::database()
      ->select('student_enrollments', 'se')
      ->fields('se', ['user_id', 'course_id', 'created'])
      ->execute()
      ->fetchAll();
    $enrollments_for_render = [];

    foreach ($enrollments as $enrollment) {
      $student_name = User::load($enrollment->user_id)->getAccountName();
      $course_name = Node::load($enrollment->course_id)->label();
      $enrollment_time = $enrollment->created;
      $enrollment_for_render = [
        'student_name' => $student_name,
        'course_name' => $course_name,
        'enrollment_time' => $enrollment_time
      ];
      $enrollments_for_render[] = $enrollment_for_render;
    }

    $build = [
      '#theme' => 'enrollmentsTable',
      '#enrollments' => $enrollments_for_render
    ];
    return $build;
  }
}
