<?php

declare(strict_types=1);

namespace Drupal\instructor_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserAuthInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Instructor management form.
 */
final class LogInAsInstructorForm extends FormBase {

  protected $userAuth;

  public function __construct(UserAuthInterface $user_auth) {
    $this->userAuth = $user_auth;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.auth')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'instructor_management_log_in_as_instructor';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => TRUE,
    ];
    $form['pass'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Log in'),
    ];
    return $form;
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    $pass = $form_state->getValue('pass');
    $uid = $this->userAuth->authenticate($name, $pass);
    if ($uid) {
      $user = User::load($uid);
      if ($user->hasRole('instructor')) {
        user_login_finalize($user);
        $form_state->setRedirect('entity.user.canonical', ['user' => $uid]);
      } else {
        $this->messenger()->addError($this->t('The user is not an instructor.'));
      }
    } else {
      $this->messenger()->addError($this->t('User with these credentials is not registered.'));
    }
  }
}
