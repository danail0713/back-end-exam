<?php

namespace Drupal\student_enrollment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Returns a rendered table of all enrollments made by students for administrators to track them.
 */
class EnrollmentsController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Query to fetch the student enrollments.
    $enrollments = \Drupal::database()
      ->select('student_enrollments', 'se')
      ->fields('se', ['user_id', 'course_id', 'created'])
      ->execute()
      ->fetchAll();

    // preparing the enrollments for render
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
      '#cache' => ['max-age' => 0],
      '#enrollments' => $enrollments_for_render
    ];
    return $build;
  }
}
