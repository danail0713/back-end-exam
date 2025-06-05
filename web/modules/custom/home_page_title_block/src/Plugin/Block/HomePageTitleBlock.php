<?php

namespace Drupal\home_page_title_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Entity\View;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "home_page_title_block_example",
 *   admin_label = @Translation("Home page title"),
 *   category = @Translation("Custom")
 * )
 */
class HomePageTitleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('<h1>Find the courses you are interested in the IT field.</h1>'),
    ];
    return $build;
  }
}
