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
      '#required' => true,
    ];

    $form['description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
      '#required' => true,
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
      '#required' => true,
    ];

    // Add Language selection field
    $form['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#options' => [
        'en' => $this->t('English'),
        'bg' => $this->t('Bulgarian'),
      ],
      '#required' => true,
      '#default_value' => 'en',
      '#ajax' => [
        'callback' => '::languageChangeCallback',
        'wrapper' => 'dynamic-fields-wrapper',
        'effect' => 'fade',
      ],
    ];

    // Add Select List for Available Courses first (needed for original theme filtering)
    $form['course_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Course'),
      '#options' => $this->getAvailableCourses(),
      '#required' => true,
      '#ajax' => [
        'callback' => '::courseChangeCallback',
        'wrapper' => 'dynamic-fields-wrapper',
        'effect' => 'fade',
      ],
    ];

    // Wrapper for dynamic fields (original theme and homework)
    $form['dynamic_fields_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'dynamic-fields-wrapper'],
    ];

    $language = $form_state->getValue('language', 'en');
    $course_id = $form_state->getValue('course_select');
    $original_theme_id = $form_state->getValue('original_theme_id');

    if ($language === 'bg') {
      $form['dynamic_fields_wrapper']['original_theme_id'] = [
        '#type' => 'select',
        '#title' => $this->t('Original English Theme'),
        '#options' => $this->getEnglishThemes($course_id),
        '#required' => TRUE,
        '#empty_option' => $this->t('- Select English theme -'),
        '#description' => $this->t('Select the English theme that this Bulgarian theme translates. Resources will be copied from the original theme.'),
        '#ajax' => [
          'callback' => '::originalThemeChangeCallback',
          'wrapper' => 'homework-wrapper',
          'effect' => 'fade',
        ],
      ];
    }


    // Add description for Bulgarian homework field
    if ($language === 'bg') {
      $form['dynamic_fields_wrapper']['homework_wrapper']['homework']['#description'] = $this->t('This field will be prefilled with the homework from the selected English theme. You can translate it to Bulgarian.');
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add theme'),
    ];

    return $form;
  }

  /**
   * AJAX callback for language change.
   */
  public function languageChangeCallback(array &$form, FormStateInterface $form_state) {
    return $form['dynamic_fields_wrapper'];
  }

  /**
   * AJAX callback for course change.
   */
  public function courseChangeCallback(array &$form, FormStateInterface $form_state) {
    return $form['dynamic_fields_wrapper'];
  }

  /**
   * AJAX callback for original theme change.
   */
  public function originalThemeChangeCallback(array &$form, FormStateInterface $form_state) {
    return $form['dynamic_fields_wrapper']['homework_wrapper'];
  }

  /**
   * Function to retrieve available courses.
   */
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
   * Get English themes for selection, optionally filtered by course.
   */
  private function getEnglishThemes($course_id = null) {
    try {
      $client = new Client("mongodb://localhost:27017");
      $collection = $client->getDatabase('test')->getCollection('themes');

      // Build query
      $query = [];
      if ($course_id) {
        $query['course_id'] = $course_id;
      }

      $documents = $collection->find($query)->toArray();

      $options = [];
      foreach ($documents as $document) {
        $course_info = $course_id ? '' : ' (Course ID: ' . $document['course_id'] . ')';
        $options[(string)$document['_id']] = $document['title'] . $course_info;
      }
      return $options;
    } catch (\Exception $e) {
      \Drupal::logger('themes_block')->error('Error fetching English themes: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }

  /**
   * Get original theme data including resources.
   */
  private function getOriginalThemeData($original_theme_id) {
    try {
      $client = new Client("mongodb://localhost:27017");
      $collection = $client->getDatabase('test')->getCollection('themes');

      $original_theme = $collection->findOne(['_id' => new ObjectId($original_theme_id)]);
      return $original_theme;
    } catch (\Exception $e) {
      \Drupal::logger('themes_block')->error('Error fetching original theme: @message', ['@message' => $e->getMessage()]);
      return null;
    }
  }

  /**
   * Hide text format help text.
   */
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
    $language = $form_state->getValue('language');
    $original_theme_id = $form_state->getValue('original_theme_id');

    // Validate that Bulgarian themes have an original theme selected
    if ($language === 'bg' && empty($original_theme_id)) {
      $form_state->setErrorByName('original_theme_id', $this->t('Please select an original English theme for the Bulgarian translation.'));
    }

    // Check if Bulgarian theme already exists for this original theme
    if ($language === 'bg' && $original_theme_id) {
      try {
        $client = new Client("mongodb://localhost:27017");
        $collection = $client->getDatabase('test')->getCollection('themes_bg');

        $existing_theme = $collection->findOne(['original_theme_id' => new ObjectId($original_theme_id)]);
        if ($existing_theme) {
          $form_state->setErrorByName('original_theme_id', $this->t('A Bulgarian translation already exists for this theme: "@title"', ['@title' => $existing_theme['title']]));
        }
      } catch (\Exception $e) {
        // Log error but don't block form submission
        \Drupal::logger('themes_block')->error('Error checking for existing Bulgarian theme: @message', ['@message' => $e->getMessage()]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Get form values.
    $title = $form_state->getValue('title');
    $description = $form_state->getValue('description')['value'];
    $type = $form_state->getValue('type');
    $language = $form_state->getValue('language');
    $course_id = $form_state->getValue('course_select');
    $original_theme_id = $form_state->getValue('original_theme_id');

    // Get course title for success message
    $course_options = $this->getAvailableCourses();
    $selected_course = $course_options[$course_id] ?? 'Unknown Course';

    // Connect to MongoDB.
    try {
      $client = new Client("mongodb://localhost:27017");

      // Choose collection based on language
      $collection_name = ($language === 'bg') ? 'themes_bg' : 'themes';
      $collection = $client->getDatabase('test')->getCollection($collection_name);

      // Prepare the document.
      $theme_data = [
        'title' => $title,
        'description' => $description,
        'type' => $type,
        'course_id' => $course_id,
        'resources' => [], // Default empty array
        'homework' => '',
      ];

      // For Bulgarian themes, get resources from original English theme
      if ($language === 'bg' && $original_theme_id) {
        $original_theme = $this->getOriginalThemeData($original_theme_id);
        if ($original_theme) {
          // Copy resources from original theme
          $theme_data['resources'] = $original_theme['resources'] ?? [];
          $theme_data['original_theme_id'] = new ObjectId($original_theme_id);
        } else {
          $this->messenger()->addWarning($this->t('Could not fetch resources from original theme. Theme created with empty resources.'));
        }
      }

      // For English themes, resources start empty (can be added later via separate form/interface)
      if ($language === 'en') {
        $theme_data['resources'] = [];
      }

      // Insert into MongoDB.
      $insertResult = $collection->insertOne($theme_data);

      // Show success message.
      if ($insertResult->getInsertedCount() > 0) {
        $language_name = $language === 'bg' ? 'Bulgarian' : 'English';
        $message = $this->t(
          'The theme "@title" has been added to the course "@course" in @language.',
          [
            '@title' => $title,
            '@course' => $selected_course,
            '@language' => $language_name
          ]
        );

        if ($language === 'bg' && $original_theme_id) {
          // Get original theme title for additional info
          try {
            $original_theme = $this->getOriginalThemeData($original_theme_id);
            if ($original_theme) {
              $resources_count = count($original_theme['resources'] ?? []);
              $message .= ' ' . $this->t('(Translation of: "@original_title", @count resources copied)', [
                '@original_title' => $original_theme['title'],
                '@count' => $resources_count
              ]);
            }
          } catch (\Exception $e) {
            // Ignore error in fetching original theme title
          }
        }
        $this->messenger()->addMessage($message);
      } else {
        $this->messenger()->addError($this->t('Failed to save the theme.'));
      }
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('MongoDB Error: @message', ['@message' => $e->getMessage()]));
    }
  }
}
