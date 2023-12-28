<?php

namespace Drupal\student_enrollment\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Service description.
 */
class NotificationService {

  protected $mailManager;
  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory, MailManagerInterface $mailManager) {
    $this->configFactory = $configFactory;
    $this->mailManager = $mailManager;
  }

  public function sendEnrollmentNotification($userId, $courseId) {
    $config = $this->configFactory->getEditable('student_enrollment.settings');
    $recipients = $config->get('email_recipients');
    $params = [
      'subject' => 'Course Enrollment Notification',
      'body' => [
        'message' => "User with id $userId successfully enrolled for the course with id $courseId."
      ]
    ];
    $emails = explode(',', $recipients);
    foreach ($emails as $email) {
      $this->mailManager->mail('student_enrollment', 'notify', trim($email), 'en-US', $params);
    }
  }
}
