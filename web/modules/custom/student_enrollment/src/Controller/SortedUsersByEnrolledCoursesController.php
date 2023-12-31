<?php

namespace Drupal\student_enrollment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;

class SortedUsersByEnrolledCoursesController extends ControllerBase {

  public function build() {
    $enrolled_users = $this->fetchEnrolledUsers();
    $all_users = $this->fetchAllUsers();
    foreach ($all_users as $user) {
      if (!array_key_exists($user, $enrolled_users)) {
        $enrolled_users[$user] = '0';
      }
    }

    $users_for_render = [];
    foreach ($enrolled_users as $user_id => $courses_count) {
      $names = User::load($user_id)->getAccountName();
      $user = [
        'names' => $names,
        'courses_count' => $courses_count,
      ];
      $users_for_render[] = $user;
    }

    $build = [
      '#theme' => 'sorted-users-by-enrolled-courses',
      '#users' => $users_for_render
    ];
    return $build;
  }

  /**
   * Function to fetch all users that enrolled for courses sorted by the number
   * of courses they enrolled. It returns an associative array with keys user ids
   * and values the count of courses each user has enrolled.
   *
   */
  private function fetchEnrolledUsers() {
    $query = \Drupal::database()
      ->select('student_enrollments', 'se')
      ->fields('se', ['user_id']);
    $query->addExpression('count(*)', 'count_courses');
    $query->groupBy('user_id');
    $query->orderBy('count_courses', 'DESC');
    $most_enrolled_courses = $query->execute()->fetchAllKeyed();
    return $most_enrolled_courses;
  }

  /**
   * Function to fetch all users with role "student". It returns an array with user ids.
   */
  private function fetchAllUsers() {
    $users = \Drupal::entityQuery('user')
      ->condition('roles', 'student')
      ->accessCheck(true)
      ->execute();
    return $users;
  }
}
