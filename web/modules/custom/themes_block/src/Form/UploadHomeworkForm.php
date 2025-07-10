<?php

declare(strict_types=1);

namespace Drupal\themes_block\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;
use Drupal\profile\Entity\ProfileInterface;
use MongoDB\Client;

/**
 * Provides a Themes block form.
 */
final class UploadHomeworkForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'themes_block_upload_homework';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['homework_file'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload a homework'),
      '#description' => $this->t('Please upload a .zip, .rar, or .tar file.'),
      '#required' => true,
    ];

    $form['actions'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send the homework'),
      '#attributes' => [
        'class' => ['homework-submit-btn'],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $validators = ['file_validate_extensions' => ['zip rar tar']];

    $file = file_save_upload('homework_file', $validators, 'private://homeworks', 0);
    if (!$file) {
      $form_state->setErrorByName('homework_file');
    } else {
      // Temporarily store the file in form_state to use it later in submitForm().
      $form_state->setValue('homework_file_id', $file->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /** @var FileInterface $file */
    $file_id = $form_state->getValue('homework_file_id');
    $file = File::load($file_id);
    // Save the file permanently.
    $file->setPermanent();
    $file->save();

    $file_url = $file->createFileUrl();
    $student_id = \Drupal::currentUser()->id(); // current user id. The user is a student.

    // These should come from context, route params, or form input.
    $instructor_id = $this->getInstructorId();
    $theme_id = $this->getThemeId();
    // Connect to MongoDB
    $mongo_client = new Client('mongodb://localhost:27017');
    $collection = $mongo_client->getDatabase('test')->getCollection('homeworks');
    // Homework item with theme ID
    $homework = [
      'student_id' => $student_id,
      'file_url' => $file_url,
    ];
    // Save to MongoDB
    $collection->updateOne(
      ['instructor_id' => $instructor_id],
      [
        '$set' => ['theme_id' => $theme_id],
        '$push' => ['homeworks_to_check' => $homework]
      ],
      ['upsert' => true]
    );

    \Drupal::messenger()->addMessage($this->t('Homework sent successfully'));
  }

  private function getCurrentCourse() {
    $current_course_id = \Drupal::routeMatch()->getRawParameter('node');
    $current_course = Node::load($current_course_id);
    return $current_course;
  }

  private function getInstructorId() {
    $current_course = $this->getCurrentCourse();
    $instructor_id = $current_course->get('field_instructor')->target_id;
    $instructor_node = Node::load($instructor_id);
    $instructor_mobile_number = $instructor_node->get('field_phone')->value;
    $profiles = \Drupal::entityTypeManager()
      ->getStorage('profile')
      ->loadByProperties(['field_mob' => $instructor_mobile_number, 'type' => 'instructor']);
      $instructor_profile = reset($profiles);
      $instructor_id = "";
      if ($instructor_profile instanceof ProfileInterface) {
        $instructor_id = $instructor_profile->get('uid')->target_id;
      }
    return $instructor_id;
  }

  private function getThemeId() {
    $mongo_client = new Client('mongodb://localhost:27017');
    $current_language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $db = $mongo_client->getDatabase('test');
    $themes_collection = ($current_language == 'en') ? $db->getCollection('themes') : $db->getCollection('themes_bg');
    $course_id = $this->getCurrentCourse()->id();
    $theme = $themes_collection->findOne([
      'course_id' => $course_id,
      'homework' => [
            '$not' => [
                '$in' => ['', 'no homeworks accepted']
            ]
        ]
    ]);
    return ($current_language == 'en') ? $theme['_id'] : $theme['original_theme_id'];
  }
}
