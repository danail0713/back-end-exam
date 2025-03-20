<?php

declare(strict_types=1);

namespace Drupal\admin_profile_view\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Returns responses for admin_profile_view routes.
 */
final class AdminProfileViewController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function __invoke(): array {
    $current_user = $this->currentUser();
    $roles = $current_user->getRoles();
    // check if the current user is an administrator.
    if (in_array('administrator', $roles)) {
      $admin_username = $current_user->getAccountName();
      // get the default route for editing the admin profile
      $edit_url = Url::fromRoute('entity.user.edit_form', ['user' => 1])->toString();
      $build['content'] = [
        '#type' => 'markup',
        '#cache' => ['max-age' => 0],
        '#markup' => '<h3>'.$admin_username.'</h3><a href="' . $edit_url . '">Edit</a>',
      ];
      return $build;
    } else {
      return [];
    }
  }
}
