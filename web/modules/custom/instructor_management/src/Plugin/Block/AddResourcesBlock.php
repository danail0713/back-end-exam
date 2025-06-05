<?php

declare(strict_types=1);

namespace Drupal\instructor_management\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides an add resources block.
 *
 * @Block(
 *   id = "instructor_management_add_resources",
 *   admin_label = @Translation("Add resources button"),
 *   category = @Translation("Custom"),
 * )
 */
final class AddResourcesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    // First link
    $url1 = Url::fromRoute('instructor_management.add_resources');
    $link1 = Link::fromTextAndUrl($this->t('Add resources'), $url1)->toRenderable();
    $link1['#attributes'] = ['class' => ['button', 'button--secondary']];

    // Second link
    $url2 = Url::fromRoute('instructor_management.view_homeworks');
    $link2 = Link::fromTextAndUrl($this->t('View all homeworks'), $url2)->toRenderable();
    $link2['#attributes'] = ['class' => ['button', 'button--secondary']];

    // Add both links to the container
    $build['content'] = [
      '#type' => 'container',
      'link1' => $link1,
      'link2' => $link2,
    ];

    return $build;
  }
}
