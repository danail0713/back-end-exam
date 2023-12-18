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

      // Build the block content.
      $content = [
        '#theme' => 'related-resourses',
        '#resourses' => $related_resources,
      ];
      return $content;
    } else {
      return [];
    }
  }

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
   * Get related resources for the course.
   *
   * @param \Drupal\node\NodeInterface $course
   *   The course node.
   *
   * @return array
   *   An array of related resource titles and links.
   */
  private function getRelatedResources(NodeInterface $course) {
    $relatedResourses = [];
    // Get referenced resources from the course node.
    $resourceReferences = $course->get('field_resourses');
    foreach ($resourceReferences as $resourseReference) {
      $resourse = Node::load($resourseReference->target_id);
      $resourceTitle = $resourse->get('field_title')->value;
      $resourceDescription = $resourse->get('field_info')->value;

      // Get the file attached to the resource, if any.
      $fileField = $resourse->get('field_file_upload');
      $fileUrl = '';
      $fileName = '';
      if ($fileField) {
        $fileId = $fileField->target_id;
        $file = File::load($fileId);
        if ($file) {
          $fileName = $file->getFilename();
          $fileUrl = $file->createFileUrl();
        }
      }

      // Add resource details to the array.
      $relatedResourses[] = [
        'title' => $resourceTitle,
        'description' => $resourceDescription,
        'file' => ['name' => $fileName, 'url' => $fileUrl]
      ];
    }
    return $relatedResourses;
  }
}
