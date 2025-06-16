<?php

declare(strict_types=1);

namespace Drupal\themes_block\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\profile\Entity\ProfileInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;

/**
 * Provides a themes block.
 *
 * @Block(
 *   id = "themes_block",
 *   admin_label = @Translation("Themes"),
 *   category = @Translation("Custom"),
 * )
 */
final class ThemesBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_node = \Drupal::routeMatch()->getParameter('node');

    // Ensure the current page is a Course node.
    if (!($current_node instanceof Node) || $current_node->getType() != 'courses') {
      return [];
    }

    // Fetch themes from MongoDB.
    $themes = $this->getThemesFromMongoDB($current_node->id());
    $isUserEnrolled = $this->isUserEnrolled($current_node);
    $isUserCourseInstructor = $this->isCourseInstructor($current_node);
    $instructor_id = $this->getInstructorId();
    $homework_form = \Drupal::formBuilder()->getForm('Drupal\themes_block\Form\UploadHomeworkForm');
    return [
      '#theme' => 'themes',
      '#themes' => $themes,
      '#user_enrolled' => $isUserEnrolled,
      '#user_course_instructor' => $isUserCourseInstructor,
      '#instructor_id' => $instructor_id,
      '#homework_form' => $homework_form,
      '#cache' => ['max-age' => 0],
      '#attached' => [
        'library' => [
          'themes_block/themes_toggle',
        ],
      ],
    ];
  }

  private function getCurrentCourse() {
    $current_course_id = \Drupal::routeMatch()->getRawParameter('node');
    $current_course = Node::load($current_course_id);
    return $current_course;
  }

  private function getInstructorId() {
    $current_course = $this->getCurrentCourse();
    $instructor_id = $current_course->get('field_instructor')->target_id;
    $instructor_node = Node::load($instructor_id);
    $instructor_mobile_number = $instructor_node->get('field_phone')->value;
    $profiles = \Drupal::entityTypeManager()
      ->getStorage('profile')
      ->loadByProperties(['field_mob' => $instructor_mobile_number, 'type' => 'instructor']);
    $instructor_profile = reset($profiles);
    $instructor_id = "";
    if ($instructor_profile instanceof ProfileInterface) {
      $instructor_id = $instructor_profile->get('uid')->target_id;
    }
    return $instructor_id;
  }

  /**
   * Fetch themes data from MongoDB.
   */
  private function getThemesFromMongoDB($courseId) {
    $client = new Client('mongodb://localhost:27017');
    $collection = $client->getDatabase('test')->getCollection('themes');
    $documents = $collection->find(['course_id' => $courseId])->toArray();
    $themes = [];
    $index = 0;
    foreach ($documents as $document) {
      $theme_id = $document['_id'];
      $isUserSubmittedHomework = $this->isUserSubmittedHomework($theme_id);
      $user_homework_response = $this->fetchUserHomeworkResponse($theme_id);
      $theme = [
        'id' => (string)$theme_id,
        'title' => $document['title'] ?? '',
        'description' => $document['description'] ?? '',
        'resources' => [], // This is an array of file URLs
        'type' => $document['type'],
        'homework' => $document['homework'],
        'submitted_homework' =>  $isUserSubmittedHomework,
        'homework_response' => $user_homework_response ? $user_homework_response : [],
        'accessResources' => $index == 0 // Only first theme is accessible in the beggining
      ];
      foreach ($document['resources'] as $resource_url) {

        if(str_starts_with($resource_url, 'https://') || str_starts_with($resource_url, 'http://')) {
          $resource = ['name' => $resource_url, 'url' => $resource_url];
        }
        else {
          $resource_parts = explode('/', $resource_url);
          $resource_name = end($resource_parts);
          $resource = ['name' => $resource_name, 'url' => $resource_url];
        }
        $theme['resources'][] = $resource;
      }
      $themes[] = $theme;
      $index++;
    }

    for ($i = 0; $i < count($themes) - 1; $i++) {
      if ($themes[$i]['homework_response']) {
        $grade = $themes[$i]['homework_response']['grade'];
        if ($grade >= 4.50) {
          $themes[$i + 1]['accessResources'] = true;
        }
      }
    }

    return $themes;
  }

  /**
   * Check if the user is enrolled for the course.
   */
  private function isUserEnrolled(Node $course) {
    $user = \Drupal::currentUser();
    $query = \Drupal::database()->select('student_enrollments', 'se');
    $query->fields('se', ['user_id', 'course_id'])
      ->condition('user_id', $user->id())
      ->condition('course_id', $course->id());
    $enrolled = $query->execute()->fetchAssoc();
    return !empty($enrolled);
  }

  private function isCourseInstructor(Node $course) {
    $user = \Drupal::currentUser();
    $current_user_id = $user->id();
    $profiles = \Drupal::entityTypeManager()
      ->getStorage('profile')
      ->loadByProperties(['uid' => $current_user_id, 'type' => 'instructor']);
    $instructor_profile = reset($profiles);
    $profile_phone = "";
    if ($instructor_profile instanceof ProfileInterface) {
      $profile_phone = trim($instructor_profile->get('field_mob')->value);
    }
    $instructor_id = $course->get('field_instructor')->target_id;
    $instructor_entity = Node::load($instructor_id);
    $instructor_phone = trim($instructor_entity->get('field_phone')->value);
    return $profile_phone == $instructor_phone;
  }

  private function isUserSubmittedHomework(ObjectId $theme_id) {
    $student_id = \Drupal::currentUser()->id();
    $client = new Client('mongodb://localhost:27017');
    $collection = $client->getDatabase('test')->getCollection('homeworks');
    $theme_homeworks = $collection->findOne([
      'theme_id' => $theme_id,
      'homeworks_to_check' => [
        '$elemMatch' => ['student_id' => $student_id]
      ]
    ]);
    return $theme_homeworks ? true : false;
  }

  private function fetchUserHomeworkResponse(ObjectId $theme_id) {
    $student_id = \Drupal::currentUser()->id();
    $client = new Client('mongodb://localhost:27017');
    $collection = $client->getDatabase('test')->getCollection('homeworks_responses');
    $homework_response = $collection->findOne([
      'student_id' => $student_id,
      'responses' => [
        '$elemMatch' => ['theme_id' => $theme_id]
      ]
    ]);
    $student_info = [];
    if ($homework_response && $homework_response['responses']) {
      foreach ($homework_response['responses'] as $response) {
        if ($response['theme_id'] == $theme_id) {
          $student_info = ['grade' => $response['grade'], 'comment' => $response['comment']];
          break;
        }
      }
    }
    return $student_info;
  }
}
