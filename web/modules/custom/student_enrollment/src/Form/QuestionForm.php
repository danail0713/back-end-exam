<?php

declare(strict_types=1);

namespace Drupal\student_enrollment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\ObjectId;

/**
 * Form for submitting a question to an instructor about a theme.
 */
final class QuestionForm extends FormBase {

  public function getFormId(): string {
    return 'question_submission_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $theme_id = null, $instructor_id = null): array {

    $request = \Drupal::request();
    $theme_id = $request->query->get('theme_id');
    $instructor_id = $request->query->get('instructor_id');

    $form['theme_id'] = [
      '#type' => 'hidden',
      '#value' => $theme_id,
    ];

    $form['instructor_id'] = [
      '#type' => 'hidden',
      '#value' => $instructor_id,
    ];

    $form['question_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your Question here:'),
      '#required' => true,
    ];

    $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Send your question.'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
       // your validation logic here.
    }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $theme_id = $form_state->getValue('theme_id');
    $instructor_id = $form_state->getValue('instructor_id');
    $question_text = $form_state->getValue('question_text');
    $student_id = \Drupal::currentUser()->id();

    // Setup MongoDB connection (localhost on port 27017)
    $client = new MongoClient('mongodb://localhost:27017');
    $questions_collection = $client->getDatabase('test')->getCollection('questions');

    // Prepare new question
    $new_question = [
      'student_id' => (string)$student_id,
      'theme_id' => new ObjectId($theme_id),
      'question_text' => $question_text,
    ];

    // Check if instructor document exists
    $existing = $questions_collection->findOne(['instructor_id' => $instructor_id]);

    if ($existing) {
      // Update existing document
      $questions_collection->updateOne(
        ['instructor_id' => $instructor_id],
        ['$push' => ['questions_to_answer' => $new_question]]
      );
    } else {
      // Create new document
      $questions_collection->insertOne([
        'instructor_id' => $instructor_id,
        'questions_to_answer' => [$new_question],
      ]);
    }

    $this->messenger()->addStatus($this->t('Your question was sent successfully.'));
  }
}
