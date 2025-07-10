<?php

namespace Drupal\tmgmt_deepl_glossary\EventSubscriber;

use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Route subscriber to show warning messages on specific routes.
 */
class DeeplMultilingualGlossaryRouteSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * Constructs a new DeeplMlGlossaryRouteSubscriber object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(MessengerInterface $messenger, StateInterface $state) {
    $this->messenger = $messenger;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Subscribe to the request event with a priority that ensures it runs
    // after routing but before the main content is rendered.
    $events[KernelEvents::RESPONSE][] = ['onResponse', 100];
    return $events;
  }

  /**
   * Responds to the response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response event.
   */
  public function onResponse(ResponseEvent $event): void {
    // Only process the master request.
    if (!$event->isMainRequest()) {
      return;
    }

    // Get route name from request attributes.
    $request = $event->getRequest();
    if ($request->attributes->get('_route') === 'entity.deepl_ml_glossary.collection') {
      // Check if fetch after module update was already done.
      $fetch_complete = $this->state->get('tmgmt_deepl_glossary.fetch_after_update', FALSE);
      if (!$fetch_complete) {
        $this->showWarningMessage();
      }
    }
  }

  /**
   * Shows the warning message.
   */
  protected function showWarningMessage(): void {
    $fetch_url = Url::fromRoute('tmgmt_deepl_glossary.fetch_form');
    $fetch_link = Link::fromTextAndUrl($this->t('Fetch DeepL glossaries form'), $fetch_url);

    $message = $this->t('Warning: DeepL glossaries need to be fetched before proceeding. Please visit the @fetch_link to complete the update before creating or managing glossaries.', [
      '@fetch_link' => $fetch_link->toString(),
    ]);

    $this->messenger->addWarning($message);
  }

}
