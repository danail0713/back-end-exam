<?php

declare(strict_types=1);

namespace Drupal\themes_block\Form;
use MongoDB\BSON\ObjectId;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use MongoDB\Client;
use MongoDB\Database;

/**
 * Provides a form to delete themes from a course.
 */
final class DeleteThemeForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'themes_block_delete_theme';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Get available courses
    $courses = $this->getAvailableCourses();
    $selected_course = $form_state->getValue('course_select');

    // Course select field
    $form['course_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Course'),
      '#options' => $courses,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateThemes',
        'wrapper' => 'theme-select-wrapper',
      ],
    ];

    // Theme select field
    $form['theme_select_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'theme-select-wrapper'],
    ];

    $form['theme_select_wrapper']['theme_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Theme(s) to Delete'),
      '#options' => $this->getThemesByCourse($selected_course),
      '#multiple' => TRUE,
      '#required' => TRUE,
    ];

    // Submit button
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete Selected Themes'),
    ];

    return $form;
  }

  /**
   * AJAX callback to update the themes based on the selected course.
   */
  public function updateThemes(array &$form, FormStateInterface $form_state) {
    return $form['theme_select_wrapper'];
  }

  /**
   * Retrieves available courses from MongoDB.
   */
  // Function to retrieve available courses.
  private function getAvailableCourses() {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'courses')
      ->condition('status', 1)
      ->accessCheck(true);

    $course_ids = $query->execute();
    $courses = Node::loadMultiple($course_ids);
    $options = [];
    foreach ($courses as $course) {
      $options[$course->id()] = $course->getTitle();
    }
    return $options;
  }

  /**
   * Retrieves themes related to a specific course from MongoDB.
   */
  private function getThemesByCourse($course_id): array {
    if (!$course_id) {
      return [];
    }
    $client = new Client('mongodb://localhost:27017');
    $collection = $client->getDatabase('test')->getCollection('themes'); // Replace with your actual database and collection

    $themes = [];
    $cursor = $collection->find(['course_id' => $course_id], ['projection' => ['_id' => 1, 'title' => 1]])->toArray();

    foreach ($cursor as $theme) {
      $themes[(string)$theme['_id']] = $theme['title'];
    }
    return $themes;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $selected_themes = $form_state->getValue('theme_select');
    if (!empty($selected_themes)) {
      try {
        $client = new Client('mongodb://localhost:27017');
        $collection = $client->getDatabase('test')->getCollection('themes');
        // Convert selected theme IDs to ObjectId
        $objectIds = array_map(fn($id) => new ObjectId($id), $selected_themes);
        $deleteResult = $collection->deleteMany(['_id' => ['$in' => array_values($objectIds)]]);

        if ($deleteResult->getDeletedCount() > 0) {
          $this->messenger()->addStatus($this->t('Selected themes have been deleted.'));
        } else {
          $this->messenger()->addWarning($this->t('No themes were deleted.'));
        }
      } catch (\Exception $e) {
        $this->messenger()->addError($this->t('An error occurred: @message', ['@message' => $e->getMessage()]));
      }
    } else {
      $this->messenger()->addError($this->t('Please select at least one theme.'));
    }
  }
}
