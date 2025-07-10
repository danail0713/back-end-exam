<?php

declare(strict_types=1);

namespace Drupal\admin_login\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserAuthInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a admin_login form.
 */
final class LogInAsAdminForm extends FormBase {

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
    return 'admin_login_log_in_as_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => true,
    ];
    $form['pass'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => true,
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    $pass = $form_state->getValue('pass');
    $uid = $this->userAuth->authenticate($name, $pass);
    if ($uid) {
      $user = User::load($uid);
      if ($user->hasRole('administrator')) {
        user_login_finalize($user);
        $form_state->setRedirect('entity.user.canonical', ['user' => $uid]);
      } else {
        $this->messenger()->addError($this->t('The user is not an administrator.'));
      }
    } else {
      $this->messenger()->addError($this->t('User with these credentials is not registered.'));
    }
  }
}
