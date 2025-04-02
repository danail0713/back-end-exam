<?php
namespace Drupal\student_enrollment\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\Entity\Node;
/**
 * Service that sends emails to configured recipients when a student successfully enroll for a course.
 */
class NotificationService {

  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * A function to send email notififications to a list of recipients via Mailgun
   * external service. The service has limitations related to recipients - they must be
   * authorised to get emails from the domain of the service. Only two emails that are authorised
   * will receive notifications - danails1307@abv.bg and danail1307@gmail.com. These emails come from
   * my configuration form.
   */
  public function sendEnrollmentNotification($user_id, $course_id) {
    $config = $this->configFactory->getEditable('student_enrollment.settings');
    $recipients = $config->get('email_recipients');
    $emails = explode(',', $recipients);
    $user_name = \Drupal::currentUser()->getAccountName();
    $course_name = Node::load($course_id)->label();
    foreach ($emails as $email) {
      // send to emails
    }
  }
}
