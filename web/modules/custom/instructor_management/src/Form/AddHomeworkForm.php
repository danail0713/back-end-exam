<?php

declare(strict_types=1);

namespace Drupal\instructor_management\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\profile\Entity\ProfileInterface;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a form to add homework to a theme.
 */
final class AddHomeworkForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'instructor_management_add_homework';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Prevent administrators from using the form.
    $current_user = \Drupal::currentUser();
    if (in_array('administrator', $current_user->getRoles())) {
      throw new AccessDeniedHttpException();
    }

    $courses = $this->getInstructorCourses();
    $selected_course = $form_state->getValue('course') ?? $form_state->getUserInput()['course'] ?? NULL;

    // Course selection.
    $form['course'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Course'),
      '#options' => $courses,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateThemes',
        'wrapper' => 'theme-select-wrapper',
      ],
    ];

    // Theme selection wrapper.
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

    // Homework text area.
    $form['homework'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Homework Details'),
      '#required' => true,
    ];

    // Submit button.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Homework'),
    ];

    return $form;
  }

  /**
   * Ajax callback to update theme list based on selected course.
   */
  public function updateThemes(array &$form, FormStateInterface $form_state) {
    return $form['theme_select_wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $homework = trim($form_state->getValue('homework'));
    if (mb_strlen($homework) < 10) {
      $form_state->setErrorByName('homework', $this->t('Homework must be at least 10 characters long.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
      $theme_id =  $form_state->getValue('theme');
      $homework_text = $form_state->getValue('homework');
      $client = new Client("mongodb://localhost:27017");
      $collection = $client->getDatabase('test')->getCollection('themes');

      $collection->updateOne(
        ['_id' => new ObjectId($theme_id)],
        ['$set' => ['homework' => $homework_text]]
      );

      $this->messenger()->addStatus($this->t('Homework added successfully.'));
  }

  /**
   * Get instructor's courses.
   */
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
      return $profile_name === $full_name;
    });

    $courses_rendered = [];
    foreach ($instructor_courses as $course) {
      $courses_rendered[$course->id()] = $course->label();
    }
    return $courses_rendered;
  }

  /**
   * Get themes by course from MongoDB.
   */
  private function getThemesByCourse($course_id): array {
    if (!$course_id) {
      return [];
    }

    $client = new Client('mongodb://localhost:27017');
    $collection = $client->getDatabase('test')->getCollection('themes');
    $themes = [];
    $cursor = $collection->find(['course_id' => $course_id], ['projection' => ['_id' => 1, 'title' => 1]])->toArray();

    foreach ($cursor as $theme) {
      $themes[(string) $theme['_id']] = $theme['title'];
    }
    return $themes;
  }
}
