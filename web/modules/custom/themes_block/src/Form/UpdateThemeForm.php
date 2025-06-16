<?php

declare(strict_types=1);

namespace Drupal\themes_block\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

/**
 * Provides a Themes block form.
 */
final class UpdateThemeForm extends FormBase {

  public function getFormId(): string {
    return 'themes_block_update_theme_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Always rebuild on AJAX.
    if ($form_state->getTriggeringElement()['#ajax'] ?? false) {
      $form_state->setRebuild(true);
    }

    // Step 1: Select Course
    $form['course_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Course'),
      '#options' => $this->getAvailableCourses(),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateThemesList',
        'wrapper' => 'themes-list-wrapper',
        'event' => 'change',
      ],
      '#default_value' => $form_state->getValue('course_select') ?? '',
      '#empty_option' => $this->t('- Select -'),
    ];

    // Step 2: Select Theme
    $themes_options = [];
    $user_input = $form_state->getUserInput();
    $selected_course = $user_input['course_select'] ?? $form_state->getValue('course_select');
    if ($selected_course) {
      $themes_options = $this->getThemesForCourse($selected_course);
    }

    $form['theme_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Theme'),
      '#options' => $themes_options,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateThemeFields',
        'wrapper' => 'theme-fields-wrapper',
        'event' => 'change',
      ],
      '#prefix' => '<div id="themes-list-wrapper">',
      '#suffix' => '</div>',
      '#default_value' => $user_input['theme_select'] ?? $form_state->getValue('theme_select') ?? '',
      '#empty_option' => $this->t('- Select -'),
      '#disabled' => empty($themes_options),
    ];

    // Step 3: Theme fields
    $form['theme_fields_wrapper'] = [
      '#type' => 'container',
      '#prefix' => '<div id="theme-fields-wrapper">',
      '#suffix' => '</div>',
    ];

    $selected_theme = $user_input['theme_select'] ?? $form_state->getValue('theme_select');
    $theme_data = [];
    if ($selected_theme) {
      $theme_data = $this->getThemeData($selected_theme);
    }

    // Show/hide fields based on theme selection
    $show_fields = !empty($selected_theme);

    if ($show_fields) {
      $form['theme_fields_wrapper']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#required' => TRUE,
        '#default_value' => $theme_data['title'] ?? '',
      ];

      $form['theme_fields_wrapper']['description'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Description'),
        '#required' => TRUE,
        '#default_value' => $theme_data['description'] ?? '',
        '#format' => 'basic_html',
        '#allowed_formats' => ['basic_html'],
        '#after_build' => [[get_class($this), 'hideTextFormatHelpText']],
      ];

      $form['theme_fields_wrapper']['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Type'),
        '#options' => [
          'lection' => $this->t('Lection'),
          'exercise' => $this->t('Exercise'),
        ],
        '#required' => true,
        '#default_value' => $theme_data['type'] ?? '',
      ];

      $form['theme_fields_wrapper']['theme_id'] = [
        '#type' => 'hidden',
        '#value' => $selected_theme,
      ];

      // Submit button is only visible when a theme is selected
      $form['theme_fields_wrapper']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Update theme'),
      ];
    } else {
      // Message when no theme is selected
      $form['theme_fields_wrapper']['no_theme_message'] = [
        '#type' => 'markup',
        '#markup' => '<div class="no-theme-message">' . $this->t('Please select a theme to edit.') . '</div>',
      ];
    }

    return $form;
  }

  // AJAX callback to update the themes list when a course is selected.
  public function updateThemesList(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['theme_select'];
  }

  // AJAX callback to update the theme fields when a theme is selected.
  public function updateThemeFields(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    // Return the whole wrapper so all fields and the button are rebuilt.
    return $form['theme_fields_wrapper'];
  }

  // Get available courses from Drupal nodes.
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

  // Get themes for a given course from MongoDB.
  private function getThemesForCourse($course_id) {
    $options = [];
    try {
      $client = new Client("mongodb://localhost:27017");
      $collection = $client->getDatabase('test')->getCollection('themes');
      $themes = $collection->find(['course_id' => $course_id]);
      foreach ($themes as $theme) {
        $options[(string)$theme['_id']] = $theme['title'];
      }
    } catch (\Exception $e) {
      // Optionally log error.
    }
    return $options;
  }

  // Get theme data by theme_id from MongoDB.
  private function getThemeData($theme_id) {
    try {
      $client = new Client("mongodb://localhost:27017");
      $collection = $client->getDatabase('test')->getCollection('themes');
      $theme = $collection->findOne(['_id' => new ObjectId($theme_id)]);
      if ($theme) {
        return [
          'title' => $theme['title'] ?? '',
          'description' => $theme['description'] ?? '',
          'type' => $theme['type'] ?? '', // If missing, will be empty
        ];
      }
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('MongoDB Error: @message', ['@message' => $e->getMessage()]));
    }
    return [];
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

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Prevent submission if no theme is selected.
    if (empty($form_state->getValue('theme_select'))) {
      $form_state->setErrorByName('theme_select', $this->t('Please select a theme.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Get values directly from form_state
    $values = $form_state->getValues();
    $theme_id = $values['theme_id'];
    $title = $values['title'];
    $description = $values['description']['value'];
    $type = $values['type'];
    $course_id = $values['course_select'];
    $theme_title = $title;

    try {
      $client = new Client("mongodb://localhost:27017");
      $collection = $client->getDatabase('test')->getCollection('themes');

      // First check if the document exists and if it has a type field
      $existingDoc = $collection->findOne(['_id' => new ObjectId($theme_id)]);
      $typeFieldExists = isset($existingDoc['type']);

      // Update the document
      $updateResult = $collection->updateOne(
        ['_id' => new ObjectId($theme_id)],
        ['$set' => [
          'title' => $title,
          'description' => $description,
          'type' => $type,
          'course_id' => $course_id,
        ]]
      );
      if ($updateResult->getModifiedCount() > 0) {
        $this->messenger()->addMessage($this->t(
          'The theme "@theme" has been updated.',
          ['@theme' => $theme_title]
        ));
      } else {
        $this->messenger()->addWarning($this->t('No changes were made to the theme.'));
      }
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('MongoDB Error: @message', ['@message' => $e->getMessage()]));
    }
  }
}
