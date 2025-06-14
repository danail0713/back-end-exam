<?php

declare(strict_types=1);

namespace Drupal\instructor_management\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\profile\Entity\ProfileInterface;

/**
 * Provides a courses i teach block.
 *
 * @Block(
 *   id = "instructor_management_courses_i_teach",
 *   admin_label = @Translation("Courses I teach"),
 *   category = @Translation("Custom"),
 * )
 */
final class InstructorCoursesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $instructor_courses = $this->fetchInstructorCourses();
    $build = [
      '#theme' => 'courses',
      '#instructor_courses' => $instructor_courses,
      '#cache' => ['max-age' => 0]
    ];
    return $build;
  }

  private function fetchInstructorCourses() {
    $course_ids = \Drupal::entityQuery('node')
      ->condition('type', 'courses')
      ->accessCheck(true)
      ->execute();
    $current_user_id = \Drupal::currentUser()->id();
    $profiles = \Drupal::entityTypeManager()
      ->getStorage('profile')
      ->loadByProperties(['uid' => $current_user_id, 'type' => 'instructor']);
    $instructor_profile = reset($profiles);
    $profile_name = '';
    if ($instructor_profile instanceof ProfileInterface) {
      $first_name = trim($instructor_profile->get('field_first_name')->value);
      $last_name = trim($instructor_profile->get('field_last_name')->value);
      $profile_name = "$first_name $last_name";
    }
    $courses = Node::loadMultiple($course_ids);
    $instructor_courses = array_filter($courses, function ($course) use ($profile_name) {
      $instructor_id = $course->get('field_instructor')->target_id;
      $instructor_entity = Node::load($instructor_id);
      $first_name = trim($instructor_entity->get('field_first_name')->value);
      $last_name = trim($instructor_entity->get('field_last_name')->value);
      $full_name = "$first_name $last_name";
      return $profile_name == $full_name;
    });
    return $instructor_courses;
  }
}
