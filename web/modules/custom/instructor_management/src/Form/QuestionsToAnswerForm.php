<?php

declare(strict_types=1);

namespace Drupal\instructor_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use Drupal\profile\Entity\ProfileInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a form for instructors to answer student questions.
 */
final class QuestionsToAnswerForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'instructor_management_answer_student_questions';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $current_user = $this->currentUser();

    if (in_array('administrator', $current_user->getRoles())) {
      throw new AccessDeniedHttpException();
    }
    $current_user = $this->currentUser();
    $instructor_id = $current_user->id();

    $mongo = new Client("mongodb://localhost:27017");
    $database = $mongo->getDatabase('test');
    $questions_collection = $database->getCollection('questions');
    $themes_collection = $database->getCollection('themes');

    $document = $questions_collection->findOne(['instructor_id' => $instructor_id]);

    $form['instructor_id'] = [
      '#type' => 'hidden',
      '#value' => $instructor_id,
    ];

    $form['questions'] = [
      '#type' => 'table',
      '#header' => [$this->t('Student'), $this->t('Theme'), $this->t('Question'), $this->t('Response')],
      '#empty' => $this->t('No questions from students.'),
      '#attributes' => [
        'class' => ['views-table', 'views-view-table'], // Drupal-style classes
      ],
    ];

    if ($document && isset($document['questions_to_answer'])) {
      foreach ($document['questions_to_answer'] as $index => $question_data) {
        $student_id = $question_data['student_id'];
        $theme_id = (string) $question_data['theme_id'];
        $question_text = $question_data['question_text'];

        // Load student name
        $profiles = \Drupal::entityTypeManager()
          ->getStorage('profile')
          ->loadByProperties(['uid' => $student_id, 'type' => 'student']);
        $student_profile = reset($profiles);
        $student_name = ($student_profile instanceof ProfileInterface) ?
          $student_profile->get('field_first_name')->value . ' ' .
          $student_profile->get('field_last_name')->value : 'Unknown';

        // Load theme title
        $theme = $themes_collection->findOne(['_id' => new ObjectId($theme_id)]);
        $theme_title = $theme ? $theme['title'] : 'Unknown Theme';

        $form['questions'][$index]['student'] = [
          '#markup' => $student_name,
        ];
        $form['questions'][$index]['theme'] = [
          '#markup' => $theme_title,
        ];
        $form['questions'][$index]['question_text'] = [
          '#markup' => $question_text,
        ];
        $form['questions'][$index]['response'] = [
          '#type' => 'textarea',
          '#rows' => 3,
        ];

        // Hidden fields for tracking
        $form['questions'][$index]['student_id'] = [
          '#type' => 'hidden',
          '#value' => $student_id,
        ];
        $form['questions'][$index]['theme_id'] = [
          '#type' => 'hidden',
          '#value' => (string)$theme_id,
        ];
        $form['questions'][$index]['original_question'] = [
          '#type' => 'hidden',
          '#value' => $question_text,
        ];
      }
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Answers'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();
    $mongo = new Client("mongodb://localhost:27017");
    $database = $mongo->getDatabase('test');
    $responses_collection = $database->getCollection('questions_responses');
    $questions_collection = $database->getCollection('questions');

    foreach ($values['questions'] as $question) {
      $student_id = $question['student_id'];
      $theme_id = $question['theme_id'];
      $question_text = $question['original_question'];
      $response_text = $question['response'];

      if (!empty($response_text)) {
        // Запиши отговора
        $responses_collection->updateOne(
          ['student_id' => $student_id],
          [
            '$push' => [
              'questions_responses' => [
                'question_text' => $question_text,
                'theme_id' => new ObjectId($theme_id),
                'response' => $response_text,
              ],
            ],
          ],
          ['upsert' => true]
        );

        // Изтрий отворения въпрос
        $questions_collection->updateOne(
          ['instructor_id' => $values['instructor_id']],
          ['$pull' => ['questions_to_answer' => [
            'student_id' => $student_id,
            'theme_id' => new ObjectId($theme_id),
            'question_text' => $question_text
          ]]]
        );
      }
    }

    $this->messenger()->addMessage($this->t('Answers have been successfully submitted.'));
  }
}
