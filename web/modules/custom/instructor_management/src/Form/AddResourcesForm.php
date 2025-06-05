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

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'instructor_management_add_resources';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Prevents administrators from enrolling for a course. They are redirected to 403 page(access denied).
    $current_user = \Drupal::currentUser();
    if (in_array('administrator', $current_user->getRoles())) {
      throw new AccessDeniedHttpException(); /* throws new exception for denied access to the current page.
      The 403 page is displaying.*/
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

    // Theme select (from MongoDB)
    $form['theme_select_wrapper']['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Theme'),
      '#options' => $this->getThemesByCourse($selected_course),
      '#required' => TRUE,
    ];

    // Dynamic file upload section
    $file_count = $form_state->get('file_count') ?? 1;

    $form['files_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Resources'),
      '#prefix' => '<div id="file-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#tree' => true,
    ];

    for ($i = 0; $i < $file_count; $i++) {
      $form['files_fieldset']['file_' . $i] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Upload a resourse file'),
        '#required' => true,
        '#upload_location' => 'private://resources',
        '#upload_validators' => [
          'file_validate_extensions' => ['pdf doc docx ppt pptx mp4 avi mov'],
        ],
        '#default_value' => $form_state->getValue(['files_fieldset', 'file_' . $i]),
      ];
    }

    $form['files_fieldset']['add_file'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add a resource'),
      '#submit' => ['::addOneFile'],
      '#ajax' => [
        'callback' => '::updateFileFields',
        'wrapper' => 'file-fieldset-wrapper',
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save changes'),
    ];

    return $form;
  }

  public function addOneFile(array &$form, FormStateInterface $form_state): void {
    $count = $form_state->get('file_count') ?? 1;
    $form_state->set('file_count', $count + 1);
    $form_state->setRebuild(true);
  }

  public function updateFileFields(array &$form, FormStateInterface $form_state): array {
    return $form['files_fieldset'];
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
    $profile_name = '';
    if ($instructor_profile instanceof ProfileInterface) {
      $first_name = trim($instructor_profile->get('field_first_name')->value);
      $last_name = trim($instructor_profile->get('field_last_name')->value);
      $profile_name = "$first_name $last_name";
    }
    $courses = Node::loadMultiple($course_ids);
    $instructor_courses = array_filter($courses, function ($course) use ($profile_name) {
      $instructor_id = $course->get('field_instructor')->target_id;
      $instructor_entity = Node::load($instructor_id);
      $first_name = trim($instructor_entity->get('field_first_name')->value);
      $last_name = trim($instructor_entity->get('field_last_name')->value);
      $full_name = "$first_name $last_name";
      return $profile_name == $full_name;
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
    $collection = $client->getDatabase('test')->getCollection('themes'); // Replace with your actual database and collection

    $themes = [];
    $cursor = $collection->find(['course_id' => $course_id], ['projection' => ['_id' => 1, 'title' => 1]])->toArray();

    foreach ($cursor as $theme) {
      $themes[(string)$theme['_id']] = $theme['title'];
    }
    return $themes;
  }

  /**
   * AJAX callback to update the themes based on the selected course.
   */
  public function updateThemes(array &$form, FormStateInterface $form_state) {
    return $form['theme_select_wrapper'];
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if (mb_strlen($form_state->getValue('message')) < 10) {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('Message should be at least 10 characters.'),
    //     );
    //   }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $theme_id = $form_state->getValue('theme');
    $resources = [];
    foreach ($form_state->getValues()['files_fieldset'] as $key => $fids) {
      if (strpos($key, 'file_') === 0 && !empty($fids[0])) {
        $file = File::load($fids[0]);
        if ($file) {
          $file->setPermanent(); // Mark as permanent so Drupal doesn't delete it later
          $file->save();
          $resources[] = $file->createFileUrl();
        }
      }
    }
    if (!empty($resources)) {
      $client = new Client("mongodb://localhost:27017");
      $collection = $client->getDatabase('test')->getCollection('themes');
      $collection->updateOne(
        ['_id' => new ObjectId($theme_id)],
        ['$push' => ['resources' => ['$each' => $resources]]]
      );
      \Drupal::messenger()->addStatus($this->t('Resources added successfully.'));
    }
  }
}
