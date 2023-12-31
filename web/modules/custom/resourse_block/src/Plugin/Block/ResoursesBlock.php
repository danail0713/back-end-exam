<?php

namespace Drupal\resourse_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Provides a resourses block block.
 *
 * @Block(
 *   id = "resourse_block_resourses_block",
 *   admin_label = @Translation(""),
 *   category = @Translation("Custom")
 * )
 */
class ResoursesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current node (course).
    $course = \Drupal::routeMatch()->getParameter('node');
    // Check if the current page is a node and of type 'courses' and whether the user is enrolled for that course to see its resourses.'
    if ($course instanceof NodeInterface && $course->getType() == 'courses' && $this->isUserEnrolled($course)) {
      $related_resources = $this->getRelatedResources($course);
      // Render the resourses.
      $content = [
        '#theme' => 'related-resourses',
        '#resourses' => $related_resources,
      ];
    } else {
      $content = [
        '#type' => 'markup',
        '#markup' => $this->t("Resourses for this course are not available, because you aren't enrolled for it.")
      ];
    }
    return $content;
  }

  // check if the user is enrolled for the course
  private function isUserEnrolled(NodeInterface $course) {
    $user = \Drupal::currentUser();
    $query = \Drupal::database()->select('student_enrollments', 'se');
    $query->fields('se', ['user_id', 'course_id'])
      ->condition('user_id', $user->id())
      ->condition('course_id', $course->id());
    $enrolled = $query->execute()->fetchAssoc();
    return !empty($enrolled);
  }


  /**
   * Get related resourses for the course.
   *
   * @param \Drupal\node\NodeInterface $course
   *   The course node.
   *
   * @return array
   *   An array of related resourse details
   */
  private function getRelatedResources(NodeInterface $course) {
    $related_resourses = [];
    // Get referenced resourses from the course.
    $resourceReferences = $course->get('field_resourses');
    foreach ($resourceReferences as $resourseReference) {
      $resourse = Node::load($resourseReference->target_id);
      $resource_title = $resourse->get('field_title')->value;
      $resource_description = $resourse->get('field_info')->value;

      // Get the file attached to the resourse.
      $fileField = $resourse->get('field_file_upload');
      $fileId = $fileField->target_id;
      $file = File::load($fileId);
      $file_name = $file->getFilename();
      $file_url = $file->createFileUrl();

      // Add resource details to the array.
      $related_resourses[] = [
        'title' => $resource_title,
        'description' => $resource_description,
        'file' => ['name' => $file_name, 'url' => $file_url]
      ];
    }
    return $related_resourses;
  }
}
