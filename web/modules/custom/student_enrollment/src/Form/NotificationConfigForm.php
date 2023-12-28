<?php

namespace Drupal\student_enrollment\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotificationConfigForm extends FormBase {

  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  public function getFormId() {
    return 'student_enrollment_notification_config';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email_recipients'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email Recipients'),
      '#description' => $this->t('Enter email addresses to receive enrollment notifications, separated by commas.'),
      '#default_value' => $this->getConfig()->get('email_recipients') ?: '',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Save configuration',
    ];
    return $form;
  }

  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('config.factory')
    );
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->getConfig()->set('email_recipients', $form_state->getValue('email_recipients'))->save();
  }

  private function getConfig() {
    return $this->configFactory->getEditable('student_enrollment.settings');
  }
}
