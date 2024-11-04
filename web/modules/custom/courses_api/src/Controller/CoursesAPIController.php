<?php

namespace Drupal\courses_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Returns responses for Courses api routes.
 */
class CoursesAPIController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $courses = $this->getCoursesData();
    header('Content-Type: application/json');
    echo json_encode($courses, JSON_PRETTY_PRINT);
    exit;
  }

  /**
   * Method to fetch course data in the desired format.
   *
   * @return array
   *   Array containing course data in the required JSON format.
   */
  private function getCoursesData() {
    $courses = [];
    // Sample data for demonstration purposes.
    // Replace this with your logic to fetch actual course data.
    $coursesFromDatabase = $this->fetchCoursesFromDatabase();
    foreach ($coursesFromDatabase as $course) {
      // fetch instructor entity
      $instructor_id = $course->get('field_instructor')->target_id;
      $instructor = Node::load($instructor_id);
      // fetch subject term
      $subject_id = $course->get('field_subject')->target_id;
      $subject = Term::load($subject_id);
      // fetch level term
      $level_id = $course->get('field_level')->target_id;
      $level = Term::load($level_id);
      // fetch department term
      $department_id = $course->get('field_department')->target_id;
      $department = Term::load($department_id);
      $courses[] = [
        'courseName' => $course->getTitle(),
        'description' => $course->get('field_description')->value, // Assuming 'body' field holds description.
        'startDate' => strtotime($course->get('field_start_date')->value),
        'endDate' => strtotime($course->get('field_end_date')->value),
        'instructor' => [
          'name' => $instructor->get('field_name')->value,
          'bio' => $instructor->get('field_bio')->value,
          'contactInfo' => $instructor->get('field_email')->value,
        ],
        'subject' => $subject->getName(),
        'level' => $level->getName(),
        'department' => $department->getName(),
        'resources' => $this->getCourseResourses($course), // Fetch resources for each course.
      ];
    }
    return $courses;
  }

  /**
   * Method to fetch resources for a course.
   *
   * @param \Drupal\node\Entity\Node $course
   *   The course node.
   *
   * @return array
   *   Array containing resources for the course.
   */
  private function getCourseResourses($course) {
    $resourses = [];
    // Get the referenced resources for the course.
    $referencedResourses = $course->get('field_resourses');
    foreach ($referencedResourses as $resourse) {
      $resourse_id = $resourse->target_id;
      $resourse = Node::load($resourse_id);
      $file_id = $resourse->get('field_file_upload')->target_id;
      $file = File::load($file_id);
      $file_url = $file->createFileUrl();
      $domain = \Drupal::request()->getHost();
      $resourses[] = [
        'title' => $resourse->get('field_title')->value,
        'description' => $resourse->get('field_info')->value,
        'url' => $domain.$file_url,
      ];
    }
    return $resourses;
  }

  /**
   * Method to fetch course data from the database.
   * Replace this with your actual logic to fetch course data.
   *
   * @return array
   *   Array of course entities.
   */
  private function fetchCoursesFromDatabase() {
    // Sample logic. Replace this with your actual database query to fetch courses.
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'courses')
      ->accessCheck(true);
    $course_ids = $query->execute();
    $courses = Node::loadMultiple($course_ids);
    return $courses;
  }
}
