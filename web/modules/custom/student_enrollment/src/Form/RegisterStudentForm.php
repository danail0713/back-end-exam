<?php

namespace Drupal\student_enrollment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url as CoreUrl;
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

  public function buildForm(array $form, FormStateInterface $form_state) {
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
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => true,
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
    $first_name = $form_state->getValue('first_name');
    $last_name = $form_state->getValue('last_name');

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
    // get the submitted fields from the form
    $first_name = $form_state->getValue('first_name');
    $last_name = $form_state->getValue('last_name');
    $email = $form_state->getValue('email');
    $password = $form_state->getValue('password');
    $phone = $form_state->getValue('phone');

    // Check if email or phone already exist.
    $existing_user = user_load_by_mail($email);
    $existing_profile = \Drupal::entityTypeManager()
      ->getStorage('profile')
      ->loadByProperties(['field_mobile_number' => $phone]);

    if ($existing_user || !empty($existing_profile)) {
      if ($existing_user) {
        $username = $existing_user->getAccountName();
        $this->messenger()->addMessage($this->t('Account with this email already exists.'));
      }
      if (!empty($existing_profile)) {
        $this->messenger()->addMessage($this->t('Account with this phone number already exists.'));
      }
      return;
    }
    $user = User::create();
    //set the username to be a combination between first and last name
    $username = $first_name . $last_name;
    // Set user fields.
    $user
      ->setUsername($username)
      ->setEmail($email)
      ->setPassword($password)
      ->addRole('student')
      ->activate()
      ->save();
    // Create a new student profile.
    $profile = Profile::create([
      'type' => 'student',
      'uid' => $user->id(),
      'field_first_name' => $first_name,
      'field_last_name' => $last_name,
      'field_mobile_number' => $phone
    ]);
    $profile->save();

    // Redirect the user to a confirmation page or any other appropriate destination.
    $url = CoreUrl::fromRoute('<front>');
    $form_state->setRedirectUrl($url);
  }
}
