<?php

declare(strict_types=1);

namespace Drupal\themes_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Element\Url;
use Drupal\Core\Url as CoreUrl;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

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

    // Check if the current page is a Course node.
    if ($current_node instanceof Node && $current_node->getType() === 'courses') {
      $themes_field = $current_node->get('field_themes');
      $themes = [];
      if ($themes_field) {
        foreach ($themes_field as $theme_ref) {
          $paragraph = Paragraph::load($theme_ref->target_id);
          $theme_data = [
            'title' => $paragraph->get('field_title')->value,
            'description' => $paragraph->get('field_description')->value,
            'resources' => [],
          ];
          $resources = $paragraph->get('field_resources');
          if ($resources) {
            foreach ($resources as $resource_ref) {
              $fileParagraph = Paragraph::load($resource_ref->target_id);
              $file_id = $fileParagraph->get('field_file')->target_id;
              $file = File::load($file_id);
              $file_name = $file->getFilename();
              $file_url = $file->createFileUrl();
              $theme_data['resources'][] = ['fileName' => $file_name, 'fileUrl' => $file_url];
            }
          }
          $themes[] = $theme_data;
        }
      }
      $isUserEnrolled = $this->isUserEnrolled($current_node);
      return [
        '#theme' => 'themes',
        '#themes' => $themes,
        '#user_enrolled' => $isUserEnrolled,
        '#cache' => ['max-age' => 0],
        '#attached' => [
          'library' => [
            'themes_block/themes_toggle',
          ],
        ],
      ];
    }
    // Return a default message if not on the course page.
    return [];
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
}
