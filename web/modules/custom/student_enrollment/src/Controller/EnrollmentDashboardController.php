<?php

namespace Drupal\student_enrollment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Returns a rendered list of all courses that the current user has enrolled.
 */
class EnrollmentDashboardController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $current_user = \Drupal::currentUser();
    // Query to fetch the ids of the enrolled courses for the current user.
    $query = \Drupal::database()
    ->select('student_enrollments','en')
    ->fields('en',['course_id'])
    ->condition('user_id', $current_user->id());
    $enrolled_course_ids = $query->execute()->fetchCol();

    // Load enrolled courses based on the IDs.
    $enrolled_courses = Node::loadMultiple($enrolled_course_ids);

    // Display enrolled courses in the dashboard by passing them to the enrolled-courses-dashboard twig file.
    $build = [
      '#theme' => 'enrolled-courses-dashboard',
      '#cache' => ['max-age' => 0],
      '#courses' => $enrolled_courses
    ];
    return $build;
  }
}
