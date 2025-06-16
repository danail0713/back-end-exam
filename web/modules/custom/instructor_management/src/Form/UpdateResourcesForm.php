<?php

declare(strict_types=1);

namespace Drupal\instructor_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\profile\Entity\ProfileInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class UpdateResourcesForm extends FormBase {

  public function getFormId(): string {
    return 'instructor_management_update_resources';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $current_user = \Drupal::currentUser();
    if (in_array('administrator', $current_user->getRoles())) {
      throw new AccessDeniedHttpException();
    }

    $courses = $this->getInstructorCourses();
    $selected_course = $form_state->getValue('course') ?? NULL;
    $form['course'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Course'),
      '#options' => $courses,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateThemes',
        'wrapper' => 'theme-select-wrapper',
      ]
    ];

    $form['theme_select_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'theme-select-wrapper'],
    ];

    // FIX: Always get the latest selected theme from user input if available
    $user_input = $form_state->getUserInput();
    $selected_theme = $user_input['theme'] ?? $form_state->getValue(['theme_select_wrapper', 'theme']) ?? NULL;

    $form['theme_select_wrapper']['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Theme'),
      '#options' => $this->getThemesByCourse($selected_course),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateResources',
        'wrapper' => 'resources-fieldset-wrapper',
      ]
    ];

    // List current resources for the selected theme
    $form['resources_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Current Resources'),
      '#prefix' => '<div id="resources-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#tree' => true,
    ];

    $resources = [];
    if ($selected_theme) {
      $resources = $this->getThemeResources($selected_theme);
    }

    foreach ($resources as $i => $resource) {
      // Determine original type
      $original_type = filter_var($resource, FILTER_VALIDATE_URL) ? 'url' : 'file';
      // Get current type from form state (after AJAX), fallback to original
      $current_type = $form_state->getValue(['resources_fieldset', $i, 'type']) ?? $original_type;

      $form['resources_fieldset'][$i]['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Resource type'),
        '#options' => ['file' => $this->t('File'), 'url' => $this->t('URL')],
        '#default_value' => $current_type,
        '#ajax' => [
          'callback' => '::updateResourceFields',
          'wrapper' => "resources-fieldset-wrapper",
          'event' => 'change',
        ],
      ];

      if ($current_type === 'file') {
        // If original is file, show its name; if original is url, show nothing
        $file_name = '';
        if ($original_type === 'file') {
          $parts = explode('/', $resource);
          $file_name = end($parts);
        }
        $form['resources_fieldset'][$i]['file'] = [
          '#type' => 'managed_file',
          '#title' => $this->t('Upload a resource file'),
          '#required' => ($original_type === 'url'), // require only if switching from url to file
          '#upload_location' => 'private://resources',
          '#upload_validators' => [
            'file_validate_extensions' => ['txt pdf doc docx ppt pptx mp4 avi mov'],
          ],
          '#default_value' => NULL, // do not prefill with old file
        ];
      } else {
        // If original is url, show it; if original is file, show nothing
        $form['resources_fieldset'][$i]['url'] = [
          '#type' => 'url',
          '#title' => $this->t('Resource URL'),
          '#default_value' => ($original_type === 'url') ? $resource : '',
          '#required' => ($original_type === 'file'), // require only if switching from file to url
        ];
      }
    }

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update resources'),
    ];

    return $form;
  }

  public function updateThemes(array &$form, FormStateInterface $form_state) {
    return $form['theme_select_wrapper'];
  }

  public function updateResources(array &$form, FormStateInterface $form_state) {
    return $form['resources_fieldset'];
  }

  public function updateResourceFields(array &$form, FormStateInterface $form_state) {
    return $form['resources_fieldset'];
  }

  private function getInstructorCourses(): array {
    $course_ids = \Drupal::entityQuery('node')
      ->condition('type', 'courses')
      ->accessCheck(true)
      ->execute();
    $current_user_id = \Drupal::currentUser()->id();
    $profiles = \Drupal::entityTypeManager()
      ->getStorage('profile')
      ->loadByProperties(['uid' => $current_user_id, 'type' => 'instructor']);
    $instructor_profile = reset($profiles);
    $profile_phone = '';
    if ($instructor_profile instanceof ProfileInterface) {
      $profile_phone = trim($instructor_profile->get('field_mob')->value);
    }
    $courses = Node::loadMultiple($course_ids);
    $instructor_courses = array_filter($courses, function ($course) use ($profile_phone) {
      $instructor_id = $course->get('field_instructor')->target_id;
      $instructor_entity = Node::load($instructor_id);
      $instructor_phone = trim($instructor_entity->get('field_phone')->value);
      return $profile_phone == $instructor_phone;
    });
    $courses_rendered = [];
    foreach ($instructor_courses as $course) {
      $courses_rendered[$course->id()] = $course->label();
    }
    return $courses_rendered;
  }

  private function getThemesByCourse($course_id): array {
    if (!$course_id) {
      return [];
    }
    $client = new Client('mongodb://localhost:27017');
    $collection = $client->getDatabase('test')->getCollection('themes');
    $themes = [];
    $cursor = $collection->find(['course_id' => $course_id], ['projection' => ['_id' => 1, 'title' => 1]])->toArray();
    foreach ($cursor as $theme) {
      $themes[(string)$theme['_id']] = $theme['title'];
    }
    return $themes;
  }

  private function getThemeResources($theme_id): array {
    $client = new Client('mongodb://localhost:27017');
    $collection = $client->getDatabase('test')->getCollection('themes');
    $theme = $collection->findOne(['_id' => new ObjectId($theme_id)]);
    if (isset($theme['resources'])) {
      // Safely convert BSONArray to PHP array
      return json_decode(json_encode($theme['resources']), true);
    }
    return [];
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Get theme ID from user input (more reliable with AJAX forms)
    $user_input = $form_state->getUserInput();
    $theme_id = $user_input['theme'] ?? $form_state->getValue(['theme_select_wrapper', 'theme']);

    $original_resources = $this->getThemeResources($theme_id);
    $submitted_resources = $form_state->getValue('resources_fieldset');
    $new_resources = [];

    foreach ($original_resources as $i => $resource) {
      $row = $submitted_resources[$i] ?? [];
      $selected_type = $row['type'] ?? (filter_var($resource, FILTER_VALIDATE_URL) ? 'url' : 'file');
      $original_type = filter_var($resource, FILTER_VALIDATE_URL) ? 'url' : 'file';

      if ($selected_type === 'url') {
        if (!empty($row['url'])) {
          $new_resources[] = $row['url'];
        } elseif ($original_type === 'url') {
          $new_resources[] = $resource;
        }
      } elseif ($selected_type === 'file') {
        if (!empty($row['file']) && !empty($row['file'][0])) {
          $file = File::load($row['file'][0]);
          if ($file) {
            $file->setPermanent();
            $file->save();
            $new_resources[] = $file->createFileUrl();
          }
        } else {
          if ($original_type === 'file') {
            $new_resources[] = $resource;
          }
        }
      }
    }

    // Update resources in MongoDB
    $client = new Client("mongodb://localhost:27017");
    $collection = $client->getDatabase('test')->getCollection('themes');
    $collection->updateOne(
      ['_id' => new ObjectId($theme_id)],
      ['$set' => ['resources' => $new_resources]]
    );

    \Drupal::messenger()->addStatus($this->t('Resources updated successfully.'));
  }
}
