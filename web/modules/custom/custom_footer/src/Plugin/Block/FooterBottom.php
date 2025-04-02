<?php

declare(strict_types=1);

namespace Drupal\custom_footer\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a footer bottom content block.
 *
 * @Block(
 *   id = "custom_footer_bottom",
 *   admin_label = @Translation("Footer bottom content"),
 *   category = @Translation("Custom"),
 * )
 */
final class FooterBottom extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $year = date('Y'); // Get current year dynamically
    return [
      '#markup' => $this->t("© $year. All rights reserved."),
      '#cache' => ['max-age' => 0], // Ensure it updates yearly as it is not cached.
    ];
  }
}
