<?php

declare(strict_types=1);

namespace Drupal\instructor_management\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\profile\Entity\ProfileInterface;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a Instructor management form.
 */
final class ViewHomeworksForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'instructor_management_view_homeworks';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $current_user = $this->currentUser();

    if (in_array('administrator', $current_user->getRoles())) {
      throw new AccessDeniedHttpException();
    }

    $instructor_id = $current_user->id();
    $mongo = new Client("mongodb://localhost:27017");
    $collection = $mongo->getDatabase('test')->getCollection('homeworks');
    $document = $collection->findOne(['instructor_id' => $instructor_id]);
    $themes_collection = $mongo->getDatabase('test')->getCollection('themes');
    $theme_name = $themes_collection->findOne(['_id' => $document['theme_id']])['title'];

    $form['homeworks'] = [
      '#type' => 'table',
      '#header' => ['First Name', 'Last Name', 'File', 'Grade', 'Comment'],
      '#caption' => $this->t("<h3>Homeworks to check for the theme $theme_name.</h3>"),
      '#empty' => $this->t('No homeworks to check.'),
      '#attributes' => [
        'class' => ['views-table', 'views-view-table'], // Drupal-style classes
      ],
    ];

    $form['instructor_id'] = [
      '#type' => 'hidden',
      '#value' => $instructor_id,
    ];

    if ($document) {
      // fetch theme_id of homeworks
      $theme_id = $document['theme_id'];
      $form['theme_id'] = [
        '#type' => 'hidden',
        '#value' => (string) $theme_id,
      ];
      if ($document['homeworks_to_check']) {
        foreach ($document['homeworks_to_check'] as $index => $homework) {
          $student_id = $homework['student_id'];
          $file_url = $homework['file_url'];
          $file_name = explode('/', $file_url)[5];
          $profiles  = \Drupal::entityTypeManager()
            ->getStorage('profile')
            ->loadByProperties(['uid' => $student_id, 'type' => 'student']);

          $student_profile = reset($profiles);
          if ($student_profile instanceof ProfileInterface) {
            $first_name = $student_profile->get('field_first_name')->value;
            $last_name = $student_profile->get('field_last_name')->value;
          }
          $form['homeworks'][$index]['student_name'] = [
            '#markup' => $first_name,
          ];
          $form['homeworks'][$index]['last_name'] = [
            '#markup' => $last_name,
          ];
          $form['homeworks'][$index]['file'] = [
            '#markup' => Link::fromTextAndUrl($file_name, Url::fromUri('base:' . $file_url))->toString(),
          ];
          $form['homeworks'][$index]['grade'] = [
            '#type' => 'number',
            '#size' => 4,
            '#min' => 2.00,
            '#max' => 6.00,
            '#step' => 0.50,
          ];
          $form['homeworks'][$index]['comment'] = [
            '#type' => 'textarea',
            '#size' => 20,
          ];

          $form['homeworks'][$index]['student_id'] = [
            '#type' => 'hidden',
            '#value' => $student_id,
          ];
        }
      }
    }

    $form['no_checking'] = [
      '#type' => 'checkbox',
      '#title' => t('Stop checking homeworks for the current theme.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save grades'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $values = $form_state->getValues();
    $mongo = new Client("mongodb://localhost:27017");
    $database = $mongo->getDatabase('test');
    $homeworks_collection = $database->getCollection('homeworks');
    $responses_collection = $database->getCollection('homeworks_responses');
    $instructor_id = $values['instructor_id'];
    $theme_id = $values['theme_id'];
    $stop_check_homeworks = $form_state->getValue('no_checking');

    foreach ($values['homeworks'] as $row) {
      $student_id = $row['student_id'];
      if ($row['grade'] && $row['comment'] && $stop_check_homeworks == 0) {
        $grade = (float) $row['grade'];
        $comment = $row['comment'];
        $previous_student_response = $responses_collection->findOne([
          'student_id' => $student_id,
          'responses' => [
            '$elemMatch' => ['theme_id' => new ObjectId($theme_id)]
          ]
        ]);
        if ($previous_student_response) {
          $responses_collection->updateOne(
            ['student_id' => $student_id],
            [
              '$pull' => [
                'responses' => ['theme_id' => new ObjectId($theme_id)]
              ]
            ]
          );
        }
        // 1. Upsert to homework_responses
        $responses_collection->updateOne(
          ['student_id' => $student_id],
          [
            '$push' => [
              'responses' => [
                'theme_id' => new ObjectId($theme_id),
                'grade' => $grade,
                'comment' => $comment,
              ]
            ]
          ],
          ['upsert' => true]
        );
      }
      if ($stop_check_homeworks == 1) {
        $collection = $database->getCollection('themes');
        $collection->updateOne(
          ['_id' => new ObjectId($theme_id)],
          ['$set' => ['homework' => 'no homeworks accepted']]
        );
        \Drupal::messenger()->addMessage('No more homeworks will be sent for this theme.');
      }
      // 2. Pull from homeworks_to_check array
      $homeworks_collection->updateOne(
        ['instructor_id' => $instructor_id],
        [
          '$pull' => [
            'homeworks_to_check' => [
              'student_id' => $student_id,
            ]
          ]
        ]
      );
    }
  }
}
