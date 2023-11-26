<?php

namespace Drupal\student_enrollment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a Student Enrollment form.
 */
class StudentEnrollmentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_enrollment_student_enrollment';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $course_id = NULL) {
    // Build the enrollment form elements here.
    // Add a dropdown for selecting courses.
    $course_name = Node::load($course_id)->label();
    $form['course'] = [
      '#type' => 'label',
      '#title' => $this->t('Enroll for the ' . $course_name . ' course'),
    ];
    $form['course_id'] = [
      '#type' => 'hidden',
      '#value' => $course_id
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enroll'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Implement validation logic if needed.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Implement submission logic to record the enrollment in the database.
    $uid = \Drupal::currentUser()->id();
    $course_id = $form_state->getValue('course_id');
    // Check if the user is already enrolled in the selected course.
    if (!$this->isUserEnrolled($uid, $course_id)) {
      // Record the enrollment.
      $this->recordEnrollment($uid, $course_id);
      $this->messenger()->addMessage($this->t('Enrollment successful.'));
    } else {
      $this->messenger()->addMessage($this->t('You are already enrolled in this course.'), 'warning');
    }

    // Redirect the user back to the dashboard.
    //$form_state->setRedirect('custom_student_module.dashboard');
  }

  /**
   * Check if a user is already enrolled in a course.
   *
   * @param int $uid
   *   The user ID.
   * @param int $course_id
   *   The course ID.
   *
   * @return bool
   *   TRUE if the user is already enrolled, FALSE otherwise.
   */
  private function isUserEnrolled($uid, $course_id) {
    // Implement logic to check if the user is already enrolled in the course.
    // This is a placeholder; replace it with your actual implementation.

    $query = \Drupal::database()->select('student_enrollments', 'e');
    $query->fields('e');
    $query->condition('user_id', $uid);
    $query->condition('course_id', $course_id);
    $result = $query->execute();

    return !empty($result->fetchAssoc());
  }

  /**
   * Record a user's enrollment in a course.
   *
   * @param int $uid
   *   The user ID.
   * @param int $course_id
   *   The course ID.
   */
  private function recordEnrollment($uid, $course_id) {
    // Implement logic to record the user's enrollment in the database.
    // This is a placeholder; replace it with your actual implementation.
    \Drupal::database()->insert('student_enrollments')
      ->fields([
        'user_id' => $uid,
        'course_id' => $course_id,
        'created' => date('Y/M/d H:i:s',\Drupal::time()->getRequestTime())
      ])
      ->execute();
  }
}
