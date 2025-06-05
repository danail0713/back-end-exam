<?php

declare(strict_types=1);

namespace Drupal\course_cleaner\EventSubscriber;

use Drupal;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;

/**
 * @todo Add description for this subscriber.
 */
final class OnCourseDeleteSubscriber implements EventSubscriberInterface {

  protected $messenger;
  protected $state;

  public function __construct(MessengerInterface $messenger, StateInterface $state) {
    $this->messenger = $messenger;
    $this->state = $state;
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 0];
    return $events;
  }

  public function onRequest(RequestEvent $event) {
    // Check if message exists.
    $deleted_course_name = $this->state->get('course_cleaner_message');
    if ($deleted_course_name) {
      $current_user = \Drupal::currentUser();
      // Check if user is admin and visiting admin page.
      $is_admin = in_array('administrator', $current_user->getRoles());
      $current_path = Url::fromRoute('<current>')->toString();
      $is_admin_path = str_starts_with($current_path, '/admin');
      if ($is_admin && $is_admin_path) {
        $this->messenger->addMessage(t("The course $deleted_course_name is without themes and has been deleted today."));
        $this->state->delete('course_cleaner_message');
      }
    }
  }

}
