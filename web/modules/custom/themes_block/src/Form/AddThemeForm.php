<?php

declare(strict_types=1);

namespace Drupal\themes_block\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

/**
 * Provides a Themes form.
 */
final class AddThemeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'themes_block_add_theme';
  }

  /**
   * Build the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
      '#required' => TRUE,
      '#default_value' => '',
      '#allowed_formats' => ['basic_html'],
      '#after_build' => [[get_class($this), 'hideTextFormatHelpText'],],
    ];

    // Add Type selection field
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => [
        'lection' => $this->t('Lection'),
        'exercise' => $this->t('Exercise'),
      ],
      '#required' => TRUE,
    ];

    // Add Select List for Available Courses.
    $form['course_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Course'),
      '#options' => $this->getAvailableCourses(),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add theme'),
    ];
    return $form;
  }

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

  public static function hideTextFormatHelpText(array $element) {
    if (isset($element['format']['help'])) {
      $element['format']['help']['#access'] = FALSE;
    }
    if (isset($element['format']['guidelines'])) {
      $element['format']['guidelines']['#access'] = FALSE;
    }
    if (isset($element['format']['#attributes']['class'])) {
      unset($element['format']['#attributes']['class']);
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Get form values.
    $title = $form_state->getValue('title');
    $description = $form_state->getValue('description')['value'];
    $type = $form_state->getValue('type');
    $course_id = $form_state->getValue('course_select');
    $selected_course = $form['course_select']['#options'][$course_id];

    // Connect to MongoDB.
    try {
      $client = new Client("mongodb://localhost:27017");
      $collection = $client->getDatabase('test')->getCollection('themes');

      // Prepare the document.
      $theme_data = [
        'title' => $title,
        'description' => $description,
        'type' => $type,
        'course_id' => $course_id,
        'resources' => [],
        'homework' => "",
      ];

      // Insert into MongoDB.
      $insertResult = $collection->insertOne($theme_data);

      // Show success message.
      if ($insertResult->getInsertedCount() > 0) {
        $this->messenger()->addMessage($this->t(
          'The theme has been added to the course @course.',
          ['@course' => $selected_course]
        ));
      } else {
        $this->messenger()->addError($this->t('Failed to save the theme.'));
      }
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('MongoDB Error: @message', ['@message' => $e->getMessage()]));
    }
  }
}
