<?php

declare(strict_types=1);

namespace Drupal\student_enrollment\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a view all responses to questions block.
 *
 * @Block(
 *   id = "student_enrollment_view_all_responses_to_questions",
 *   admin_label = @Translation("View all responses to questions"),
 *   category = @Translation("Custom"),
 * )
 */
final class ViewAllResponsesToQuestionsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    // Link for viewing all responses to questions.
    $url = Url::fromRoute('student_enrollment.question_responses');
    $link = Link::fromTextAndUrl($this->t('View all responses to questions'), $url)->toRenderable();
    $link['#attributes'] = ['class' => ['button', 'button--secondary']];

    // Add both links to the container
    $build['content'] = [
      '#type' => 'container',
       'link' => $link,
    ];

    return $build;
  }

}
