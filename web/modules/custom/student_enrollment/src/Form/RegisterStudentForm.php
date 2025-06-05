<?php

namespace Drupal\student_enrollment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\profile\Entity\Profile;
use Drupal\user\Entity\User;

/**
 * Provides a Student enrollment form.
 */
class RegisterStudentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_enrollment_register_student';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => true,
    ];

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#required' => true,
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => true,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => true,
    ];

    $form['password'] = [
      '#type' => 'password_confirm',
      '#required' => true,
      '#title' => null
  ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Mobile number'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $username = $form_state->getValue('username');
    $first_name = $form_state->getValue('first_name');
    $last_name = $form_state->getValue('last_name');
    $email = $form_state->getValue('email');
    $phone = $form_state->getValue('phone');
    // Check if email or phone already exist
    $existing_user_email = user_load_by_mail($email);
    $existing_username = user_load_by_name($username);
    $existing_profile = \Drupal::entityTypeManager()
      ->getStorage('profile')
      ->loadByProperties(['field_phone' => $phone, 'type' => 'student']);
    // Validate username uniqueness
    if ($existing_username) {
      $form_state->setErrorByName('username', $this->t('This username is already taken.'));
    }
    if ($existing_user_email || !empty($existing_profile)) {
      if ($existing_user_email) {
        $form_state->setErrorByName('email', $this->t('An account with this email already exists.'));
      }
      if (!empty($existing_profile)) {
        $form_state->setErrorByName('phone', $this->t('An account with this phone number already exists.'));
      }
    }
    // Validate first and last name
    if (!preg_match('/^[A-Za-z]{2,}$/', $first_name)) {
      $form_state->setErrorByName('first_name', $this->t('First name must be at least 2 letters and contain only letters.'));
    }

    if (!preg_match('/^[A-Za-z]{2,}$/', $last_name)) {
      $form_state->setErrorByName('last_name', $this->t('Last name must be at least 2 letters and contain only letters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $username = $form_state->getValue('username');
    $first_name = $form_state->getValue('first_name');
    $last_name = $form_state->getValue('last_name');
    $email = $form_state->getValue('email');
    $password = $form_state->getValue('password');
    $phone = $form_state->getValue('phone');

    // Create the user
    $user = User::create([
      'name' => $username,
      'mail' => $email,
      'pass' => $password,
      'status' => 1,
      'roles' => ['student'],
    ]);
    $user->save();

    // Create a new student profile
    $profile = Profile::create([
      'type' => 'student',
      'uid' => $user->id(),
      'field_first_name' => $first_name,
      'field_last_name' => $last_name,
      'field_phone' => $phone,
    ]);
    $profile->save();

    // Display success message instead of redirecting
    $this->messenger()->addMessage($this->t('The user has been successfully registered.'));
  }
}
