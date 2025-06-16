<?php

declare(strict_types=1);

namespace Drupal\instructor_management\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\profile\Entity\ProfileInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a Instructor management form.
 */
final class AddResourcesForm extends FormBase {

  public function getFormId(): string {
    return 'instructor_management_add_resources';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $current_user = \Drupal::currentUser();
    if (in_array('administrator', $current_user->getRoles())) {
      throw new AccessDeniedHttpException();
    }

    // Course select (from node titles)
    $courses = $this->getInstructorCourses();
    $selected_course = $form_state->getValue('course') ?? $form_state->getUserInput()['course'] ?? NULL;
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

    // Theme select field
    $form['theme_select_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'theme-select-wrapper'],
    ];

    $form['theme_select_wrapper']['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Theme'),
      '#options' => $this->getThemesByCourse($selected_course),
      '#required' => TRUE,
    ];

    // Dynamic resources section
    $resource_count = $form_state->get('resource_count') ?? 1;

    $form['resources_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Resources'),
      '#prefix' => '<div id="resource-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#tree' => true,
    ];

    for ($i = 0; $i < $resource_count; $i++) {
      $resource_type = $form_state->getValue(['resources_fieldset', $i, 'type']) ?? 'file';

      $form['resources_fieldset'][$i]['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Resource type'),
        '#options' => [
          'file' => $this->t('File'),
          'url' => $this->t('URL'),
        ],
        '#default_value' => $resource_type,
        '#ajax' => [
          'callback' => '::updateResourceFields',
          'wrapper' => "resource-fieldset-wrapper",
          'event' => 'change',
        ],
      ];

      if ($resource_type === 'file') {
        $form['resources_fieldset'][$i]['file'] = [
          '#type' => 'managed_file',
          '#title' => $this->t('Upload a resource file'),
          '#upload_location' => 'private://resources',
          '#upload_validators' => [
            'file_validate_extensions' => ['txt pdf doc docx ppt pptx mp4 avi mov'],
          ],
          '#default_value' => $form_state->getValue(['resources_fieldset', $i, 'file']),
        ];
      } elseif ($resource_type === 'url') {
        $form['resources_fieldset'][$i]['url'] = [
          '#type' => 'url',
          '#title' => $this->t('Resource URL'),
          '#default_value' => $form_state->getValue(['resources_fieldset', $i, 'url']),
        ];
      }
    }

    $form['resources_fieldset']['add_resource'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add a resource'),
      '#submit' => ['::addOneResource'],
      '#ajax' => [
        'callback' => '::updateResourceFields',
        'wrapper' => 'resource-fieldset-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save changes'),
    ];

    return $form;
  }

  public function addOneResource(array &$form, FormStateInterface $form_state): void {
    $count = $form_state->get('resource_count') ?? 1;
    $form_state->set('resource_count', $count + 1);
    $form_state->setRebuild(true);
  }

  public function updateResourceFields(array &$form, FormStateInterface $form_state): array {
    return $form['resources_fieldset'];
  }

  public function updateThemes(array &$form, FormStateInterface $form_state) {
    return $form['theme_select_wrapper'];
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

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Only validate if this is the main form submission
    $triggering_element = $form_state->getTriggeringElement();
    if ($triggering_element && $triggering_element['#value'] == $this->t('Add a resource')) {
      return;
    }

    // Validate that at least one resource is provided
    $resources_data = $form_state->getValue('resources_fieldset');
    $has_valid_resource = false;

    if (is_array($resources_data)) {
      foreach ($resources_data as $key => $resource) {
        if (!is_array($resource) || $key === 'add_resource') {
          continue;
        }

        if (isset($resource['type'])) {
          if ($resource['type'] == 'file' && !empty($resource['file'])) {
            $has_valid_resource = true;
            break;
          } elseif ($resource['type'] == 'url' && !empty($resource['url'])) {
            $has_valid_resource = true;
            break;
          }
        }
      }
    }

    if (!$has_valid_resource) {
      $form_state->setErrorByName('resources_fieldset', $this->t('Please provide at least one resource.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $theme_id =  $form_state->getValue('theme');
    $resources = [];

    $resources_data = $form_state->getValue('resources_fieldset');

    if (is_array($resources_data)) {
      foreach ($resources_data as $key => $resource) {
        // Skip non-numeric keys (like 'add_resource' button)
        if (!is_numeric($key) || !is_array($resource)) {
          continue;
        }

        // Process file resources
        if (isset($resource['type']) && $resource['type'] == 'file') {
          if (isset($resource['file']) && !empty($resource['file'])) {
            $file_id = is_array($resource['file']) ? $resource['file'][0] : $resource['file'];

            if ($file_id) {
              $file = File::load($file_id);
              if ($file) {
                $file->setPermanent();
                $file->save();
                $resources[] = $file->createFileUrl();
              }
            }
          }
        }
        // Process URL resources
        elseif (isset($resource['type']) && $resource['type'] == 'url') {
          if (isset($resource['url']) && !empty($resource['url'])) {
            $resources[] = $resource['url'];
          }
        }
      }
    }

    if (!empty($resources) && !empty($theme_id)) {
      try {
        $client = new Client("mongodb://localhost:27017");
        $collection = $client->getDatabase('test')->getCollection('themes');

        $result = $collection->updateOne(
          ['_id' => new ObjectId($theme_id)],
          ['$push' => ['resources' => ['$each' => $resources]]]
        );

        if ($result->getModifiedCount() > 0) {
          \Drupal::messenger()->addStatus($this->t('Resources added successfully.'));
        } else {
          \Drupal::messenger()->addWarning($this->t('No resources were added. Please check if the theme exists.'));
        }
      } catch (\Exception $e) {
        \Drupal::messenger()->addError($this->t('Error adding resources: @error', ['@error' => $e->getMessage()]));
      }
    } else {
      if (empty($theme_id)) {
        \Drupal::messenger()->addError($this->t('Please select a theme.'));
      } else {
        \Drupal::messenger()->addWarning($this->t('Please provide at least one resource.'));
      }
    }
  }
}
