<?php

declare(strict_types=1);

namespace Drupal\student_enrollment\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a my enrolled courses block.
 *
 * @Block(
 *   id = "student_enrollment_my_enrolled_courses",
 *   admin_label = @Translation("My enrolled courses"),
 *   category = @Translation("Custom"),
 * )
 */
final class MyEnrolledCoursesBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $current_user = \Drupal::currentUser();
    // Query to fetch the ids of the enrolled courses for the current user.
    $query = \Drupal::database()
      ->select('student_enrollments', 'en')
      ->fields('en', ['course_id'])
      ->condition('user_id', $current_user->id());
    $enrolled_course_ids = $query->execute()->fetchCol();

    // Load enrolled courses based on the IDs.
    $enrolled_courses = Node::loadMultiple($enrolled_course_ids);
    $build = [
      '#theme' => 'enrolled-courses-dashboard',
      '#courses' => $enrolled_courses,
      '#cache' => ['max-age' => 0]
    ];
    return $build;
  }
}
