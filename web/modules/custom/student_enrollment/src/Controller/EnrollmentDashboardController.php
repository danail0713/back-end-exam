<?php

namespace Drupal\student_enrollment\Controller;

use Drupal\Core\Controller\ControllerBase;
/**
 * Returns responses for Student Enrollment routes.
 */
class EnrollmentDashboardController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $current_user = \Drupal::currentUser();
    // Query to fetch enrolled course IDs for the current user.
    // Replace 'student_enrollments' with your actual table/entity storing enrollment data.
    $query = \Drupal::database()
    ->select('student_enrollments','en')
    ->fields('en',['course_id'])
    ->condition('user_id', $current_user->id());
    $enrolled_course_ids = $query->execute()->fetchCol();

    // Load enrolled courses based on the IDs.
    $enrolled_courses = $this->entityTypeManager()->getStorage('node')->loadMultiple($enrolled_course_ids);

    // Display enrolled courses in the dashboard.
    $build = [
      '#theme' => 'enrolled-courses-dashboard',
      '#courses' => $enrolled_courses
    ];

    return $build;
  }
}
