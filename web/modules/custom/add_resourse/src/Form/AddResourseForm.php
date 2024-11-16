<?php

namespace Drupal\add_resourse\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\node\Entity\Node;

/**
 * Provides a Add resourse form.
 */
class AddResourseForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_resourse_add_resourse';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add Title field.
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
    ];

    // Add Description field.
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
    ];

    // Add File Upload field.
    $form['file_upload'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload file'),
      '#upload_validators' => [
        'FileExtension' => [
           'extensions' => 'pdf doc docx avi mp3 mp4 mov',
          ],
        ],
      '#required' => TRUE,
      '#description' => $this->t('Upload a file for the resource.'),
    ];

    // Add Select List for Available Courses.
    $course_options = $this->getAvailableCourses();
    $form['course_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Course'),
      '#options' => $course_options,
      '#required' => TRUE,
    ];

    // Add Submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $title = $form_state->getValue('title');
    $description = $form_state->getValue('description');
    $file_upload = $form_state->getValue('file_upload');
    $selected_course_id = $form_state->getValue('course_select');
    // Create a new Resource entity.
    $resource = Node::create(['type' => 'resourse']);
    $resource->set('title', $title);
    $resource->set('field_title', $title);
    $resource->set('field_info', $description);
    $resource->set('field_file_upload', $file_upload);
    $resource->enforceIsNew();
    $resource->save();


    // Save the file upload if present.
    if (!empty($file_upload)) {
      $file = File::load($file_upload[0]);
      $file->setPermanent();
      $file->save();
      $resource->field_file_upload[] = [
        'target_id' => $file->id(),
      ];
    }

    $resource->save();

    // Associate the new resource with the Course.
    $course = Node::load($selected_course_id);
    $course->field_resourses[] = ['target_id' => $resource->id()];
    $course->save();

    // Redirect or perform additional actions after saving.
    // For example:
    $form_state->setRedirect('entity.node.canonical', ['node' => $selected_course_id]);
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
}
