<?php

declare(strict_types=1);

namespace Drupal\student_enrollment\Controller;

use Drupal\Core\Controller\ControllerBase;
use MongoDB\Client;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Displays student questions and instructor responses.
 */
final class QuestionsResponsesController extends ControllerBase {

  /**
   * Displays all answered questions for the current student.
   */
  public function __invoke(): array {

    $current_user = $this->currentUser();
    if (in_array('administrator', $current_user->getRoles())) {
      throw new AccessDeniedHttpException();
    }
    $student_id = $current_user->id();
    $mongo = new Client("mongodb://localhost:27017");
    $database = $mongo->getDatabase('test');
    $responses_collection = $database->getCollection('questions_responses');
    $themes_collection = $database->getCollection('themes');

    $document = $responses_collection->findOne(['student_id' => $student_id]);

    $header = [
      $this->t('Theme'),
      $this->t('Your Question'),
      $this->t('Instructor Response'),
    ];

    $rows = [];

    if ($document && !empty($document['questions_responses'])) {
      foreach ($document['questions_responses'] as $entry) {
        $theme_id = (string) $entry['theme_id'];
        $theme = $themes_collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($theme_id)]);
        $theme_name = $theme['title'] ?? $this->t('Unknown');

        $question = $entry['question_text'] ?? '';
        $response = $entry['response'] ?? '';

        $rows[] = [
          'data' => [
            ['data' => $theme_name],
            ['data' => $question],
            ['data' => $response],
          ],
        ];
      }
    }

    $build = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#caption' => $this->t('<h2>Responses to your questions</h2>'),
      '#empty' => $this->t('You don’t have any answered questions yet.'),
      '#attributes' => [
        'class' => ['views-table', 'views-view-table'], // Drupal-style classes
      ],
    ];

    return $build;
  }
}
