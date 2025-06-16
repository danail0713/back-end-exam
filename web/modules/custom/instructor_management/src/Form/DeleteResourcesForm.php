<?php

declare(strict_types=1);

namespace Drupal\instructor_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\profile\Entity\ProfileInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class DeleteResourcesForm extends FormBase {

  public function getFormId(): string {
    return 'instructor_management_delete_resources';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $current_user = \Drupal::currentUser();
    if (in_array('administrator', $current_user->getRoles())) {
      throw new AccessDeniedHttpException();
    }

    $courses = $this->getInstructorCourses();
    $selected_course = $form_state->getValue('course') ?? null;
    $form['course'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Course'),
      '#options' => $courses,
      '#required' => true,
      '#ajax' => [
        'callback' => '::updateThemes',
        'wrapper' => 'theme-select-wrapper',
      ]
    ];

    $form['theme_select_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'theme-select-wrapper'],
    ];

    // Get the latest selected theme from user input if available
    $user_input = $form_state->getUserInput();
    $selected_theme = $user_input['theme'] ?? $form_state->getValue(['theme_select_wrapper', 'theme']) ?? null;

    $form['theme_select_wrapper']['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Theme'),
      '#options' => $this->getThemesByCourse($selected_course),
      '#required' => true,
      '#ajax' => [
        'callback' => '::updateResources',
        'wrapper' => 'resources-wrapper',
      ]
    ];

    // Wrapper for both resources and submit button
    $form['resources_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'resources-wrapper'],
    ];

    $resources = [];
    if ($selected_theme) {
      $resources = $this->getThemeResources($selected_theme);
    }

    if (!empty($resources)) {
      $form['resources_wrapper']['resources_fieldset'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Resources to Delete'),
        '#tree' => true,
      ];

      $form['resources_wrapper']['resources_fieldset']['info'] = [
        '#markup' => '<p><strong>' . $this->t('Select the resources you want to delete:') . '</strong></p>',
      ];

      foreach ($resources as $i => $resource) {
        // Determine resource type and display name
        $is_url = filter_var($resource, FILTER_VALIDATE_URL);
        $display_name = '';
        $resource_type = '';

        if ($is_url) {
          $display_name = $resource;
          $resource_type = $this->t('URL');
        } else {
          // Extract filename from file path
          $parts = explode('/', $resource);
          $display_name = end($parts);
          $resource_type = $this->t('File');
        }

        $form['resources_wrapper']['resources_fieldset'][$i] = [
          '#type' => 'checkbox',
          '#title' => $this->t('@type: @name', [
            '@type' => $resource_type,
            '@name' => $display_name
          ]),
          '#default_value' => false,
        ];
      }

      // Submit button inside the wrapper so it appears with resources
      $form['resources_wrapper']['actions'] = [
        '#type' => 'actions',
      ];

      $form['resources_wrapper']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete Selected Resources'),
        '#button_type' => 'danger',
      ];
    } elseif ($selected_theme) {
      $form['resources_wrapper']['no_resources'] = [
        '#markup' => '<p>' . $this->t('No resources found for this theme.') . '</p>',
      ];
    }

    return $form;
  }

  public function updateThemes(array &$form, FormStateInterface $form_state) {
    return $form['theme_select_wrapper'];
  }

  public function updateResources(array &$form, FormStateInterface $form_state) {
    return $form['resources_wrapper'];
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $user_input = $form_state->getUserInput();
    $theme_id = $user_input['theme'] ?? $form_state->getValue(['theme_select_wrapper', 'theme']);

    if (!$theme_id) {
      $form_state->setErrorByName('theme', $this->t('Please select a theme.'));
      return;
    }

    $resources_to_delete = $form_state->getValue('resources_fieldset');
    $has_selection = false;

    if (is_array($resources_to_delete)) {
      foreach ($resources_to_delete as $key => $value) {
        if ($key !== 'info' && $value == 1) {
          $has_selection = true;
          break;
        }
      }
    }

    if (!$has_selection) {
      $form_state->setErrorByName('resources_fieldset', $this->t('Please select at least one resource to delete.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Get theme ID from user input (more reliable with AJAX forms)
    $user_input = $form_state->getUserInput();
    $theme_id = $user_input['theme'] ?? $form_state->getValue(['theme_select_wrapper', 'theme']);

    if (!$theme_id) {
      \Drupal::messenger()->addError($this->t('No theme selected.'));
      return;
    }

    $original_resources = $this->getThemeResources($theme_id);
    $resources_to_delete = $form_state->getValue('resources_fieldset');
    $new_resources = [];
    $deleted_count = 0;
    $file_deletion_errors = [];

    // Check if any resources were selected for deletion
    $has_selection = false;
    if (is_array($resources_to_delete)) {
      foreach ($resources_to_delete as $key => $value) {
        if ($key !== 'info' && $value == 1) {
          $has_selection = true;
          break;
        }
      }
    }

    if (!$has_selection) {
      \Drupal::messenger()->addError($this->t('Please select at least one resource to delete.'));
      return;
    }

    // Keep only resources that were not selected for deletion
    foreach ($original_resources as $i => $resource) {
      if (empty($resources_to_delete[$i]) || $resources_to_delete[$i] != 1) {
        // Resource not selected for deletion, keep it
        $new_resources[] = $resource;
      } else {
        // Resource selected for deletion
        $deleted_count++;

        // Delete physical file if it's not a URL
        if (!filter_var($resource, FILTER_VALIDATE_URL)) {
          // Check if resource path starts with private://resources
          if (strpos($resource, 'private://resources/') === 0) {
            $file_system = \Drupal::service('file_system');
            $real_path = $file_system->realpath($resource);

            if ($real_path && file_exists($real_path)) {
              if (!unlink($real_path)) {
                $file_deletion_errors[] = basename($resource);
              }
            }
          } else {
            // Handle other file paths if needed
            $file_system = \Drupal::service('file_system');
            $full_path = 'private://resources/' . basename($resource);
            $real_path = $file_system->realpath($full_path);

            if ($real_path && file_exists($real_path)) {
              if (!unlink($real_path)) {
                $file_deletion_errors[] = basename($resource);
              }
            }
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

    // Show appropriate messages
    if ($deleted_count > 0) {
      if (empty($file_deletion_errors)) {
        \Drupal::messenger()->addStatus($this->t('Successfully deleted @count resource(s) and their files.', ['@count' => $deleted_count]));
      } else {
        \Drupal::messenger()->addStatus($this->t('Successfully deleted @count resource(s) from database.', ['@count' => $deleted_count]));
        \Drupal::messenger()->addWarning($this->t('Could not delete the following files: @files', ['@files' => implode(', ', $file_deletion_errors)]));
      }
    } else {
      \Drupal::messenger()->addWarning($this->t('No resources were deleted.'));
    }
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
}
