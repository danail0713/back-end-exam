<?php
// phpcs:ignoreFile
// cSpell:disable

/**
 * @file
 * A database agnostic dump for testing purposes.
 *
 * This file was generated by the Drupal 9.5.0-dev db-tools.php script.
 */

use Drupal\Core\Database\Database;
use Symfony\Component\Routing\Route;

$connection = Database::getConnection();
// Ensure any tables with a serial column with a value of 0 are created as
// expected.
if ($connection->databaseType() === 'mysql') {
  $sql_mode = $connection->query("SELECT @@sql_mode;")->fetchField();
  $connection->query("SET sql_mode = '$sql_mode,NO_AUTO_VALUE_ON_ZERO'");
}

$connection->insert('config')
  ->fields(array(
    'collection',
    'name',
    'data',
  ))
  ->values(array(
    'collection' => '',
    'name' => 'automatic_updates.settings',
    'data' => 'a:3:{s:5:"_core";a:1:{s:19:"default_config_hash";s:43:"Sk3UhyTChNgXr-7AcaHHCN02VWo2yLzfJTjTU9M-wrc";}s:4:"cron";s:8:"security";s:24:"allow_core_minor_updates";b:0;}',
  ))
  ->values(array(
    'collection' => '',
    'name' => 'package_manager.settings',
    'data' => 'a:3:{s:5:"_core";a:1:{s:19:"default_config_hash";s:43:"oxC6eB3wWdoiIKTKUmbHsXp_qSmtBDRFWRfwuGDP_K0";}s:11:"file_syncer";s:3:"php";s:11:"executables";a:2:{s:8:"composer";N;s:5:"rsync";N;}}',
  ))
  ->execute();
$extensions = $connection->select('config', 'c')
  ->fields('c', ['data'])
  ->condition('name', 'core.extension')
  ->execute()
  ->fetchField();
$extensions = unserialize($extensions);
$extensions['module']['automatic_updates'] = 0;
$extensions['module']['package_manager'] = 0;
// Install the mysql module manually because
// system_post_update_enable_provider_database_driver() makes reliable update
// path testing impossible.
// @see https://www.drupal.org/project/automatic_updates/issues/3314137#comment-14772840
$extensions['module']['mysql'] = 0;
$connection->update('config')
  ->fields([
    'data' => serialize($extensions),
  ])
  ->condition('name', 'core.extension')
  ->execute();

// Add system_post_update_enable_provider_database_driver() as an existing
// update.
// @see https://www.drupal.org/project/automatic_updates/issues/3314137#comment-14772840
$existing_updates = $connection->select('key_value')
  ->fields('key_value', ['value'])
  ->condition('collection', 'post_update')
  ->condition('name', 'existing_updates')
  ->execute()
  ->fetchField();
$existing_updates = unserialize($existing_updates);
$existing_updates = array_merge(
  $existing_updates,
  ['system_post_update_enable_provider_database_driver'],
);
$connection->update('key_value')
  ->fields(['value' => serialize($existing_updates)])
  ->condition('collection', 'post_update')
  ->condition('name', 'existing_updates')
  ->execute();

$connection->insert('key_value')
  ->fields(array(
    'collection',
    'name',
    'value',
  ))
  ->values(array(
    'collection' => 'system.schema',
    'name' => 'automatic_updates',
    'value' => 'i:8000;',
  ))
  ->values(array(
    'collection' => 'system.schema',
    'name' => 'package_manager',
    'value' => 'i:8000;',
  ))
  ->execute();

// Ensure that these expirable values are available in tests which import this
// dump file.
$expire = time() + 3600;
$connection->insert('key_value_expire')
  ->fields(array(
    'collection',
    'name',
    'value',
    'expire',
  ))
  ->values(array(
    'collection' => 'automatic_updates',
    'name' => 'readiness_check_timestamp',
    'value' => 'i:1666285089;',
    'expire' => $expire,
  ))
  ->values(array(
    'collection' => 'automatic_updates',
    'name' => 'readiness_validation_last_run',
    'value' => 'a:2:{i:0;O:39:"Drupal\package_manager\ValidationResult":3:{s:7:"summary";N;s:8:"messages";a:1:{i:0;s:93:"The active directory at "/Users/phen/Sites/drupal" contains symlinks, which is not supported.";}s:8:"severity";i:2;}i:1;O:39:"Drupal\package_manager\ValidationResult":3:{s:7:"summary";O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:6:"string";s:55:"Updating from Drupal @installed_version is not allowed.";s:9:"arguments";a:1:{s:18:"@installed_version";s:9:"9.5.0-dev";}s:7:"options";a:0:{}}s:8:"messages";a:1:{i:0;O:48:"Drupal\Core\StringTranslation\TranslatableMarkup":3:{s:6:"string";s:171:"Drupal cannot be automatically updated from the installed version, @installed_version, because automatic updates from a dev version to any other version are not supported.";s:9:"arguments";a:1:{s:18:"@installed_version";s:9:"9.5.0-dev";}s:7:"options";a:0:{}}}s:8:"severity";i:2;}}',
    'expire' => $expire,
  ))
  ->execute();

$connection->insert('router')
  ->fields(array(
    'name',
    'path',
    'pattern_outline',
    'fit',
    'route',
    'number_parts',
  ))
  ->values(array(
    'name' => 'automatic_updates.confirmation_page',
    'path' => '/admin/automatic-update-ready/{stage_id}',
    'pattern_outline' => '/admin/automatic-update-ready/%',
    'fit' => '6',
    'route' => 'O:31:"Symfony\Component\Routing\Route":9:{s:4:"path";s:40:"/admin/automatic-update-ready/{stage_id}";s:4:"host";s:0:"";s:8:"defaults";a:2:{s:5:"_form";s:42:"\Drupal\automatic_updates\Form\UpdateReady";s:6:"_title";s:15:"Ready to update";}s:12:"requirements";a:1:{s:11:"_permission";s:27:"administer software updates";}s:7:"options";a:6:{s:14:"compiler_class";s:33:"Drupal\Core\Routing\RouteCompiler";s:19:"_maintenance_access";b:1;s:37:"_automatic_updates_readiness_messages";s:4:"skip";s:4:"utf8";b:1;s:12:"_admin_route";b:1;s:14:"_access_checks";a:1:{i:0;s:23:"access_check.permission";}}s:7:"schemes";a:0:{}s:7:"methods";a:2:{i:0;s:3:"GET";i:1;s:4:"POST";}s:9:"condition";s:0:"";s:8:"compiled";O:33:"Drupal\Core\Routing\CompiledRoute":11:{s:4:"vars";a:1:{i:0;s:8:"stage_id";}s:11:"path_prefix";s:0:"";s:10:"path_regex";s:59:"#^/admin/automatic\-update\-ready/(?P<stage_id>[^/]++)$#sDu";s:11:"path_tokens";a:2:{i:0;a:5:{i:0;s:8:"variable";i:1;s:1:"/";i:2;s:6:"[^/]++";i:3;s:8:"stage_id";i:4;b:1;}i:1;a:2:{i:0;s:4:"text";i:1;s:29:"/admin/automatic-update-ready";}}s:9:"path_vars";a:1:{i:0;s:8:"stage_id";}s:10:"host_regex";N;s:11:"host_tokens";a:0:{}s:9:"host_vars";a:0:{}s:3:"fit";i:6;s:14:"patternOutline";s:31:"/admin/automatic-update-ready/%";s:8:"numParts";i:3;}}',
    'number_parts' => '3',
  ))
  ->values(array(
    'name' => 'automatic_updates.cron.post_apply',
    'path' => '/automatic-update/cron/post-apply/{stage_id}/{installed_version}/{target_version}/{key}',
    'pattern_outline' => '/automatic-update/cron/post-apply/%/%/%/%',
    'fit' => '112',
    'route' => 'O:31:"Symfony\Component\Routing\Route":9:{s:4:"path";s:87:"/automatic-update/cron/post-apply/{stage_id}/{installed_version}/{target_version}/{key}";s:4:"host";s:0:"";s:8:"defaults";a:1:{s:11:"_controller";s:46:"automatic_updates.cron_updater:handlePostApply";}s:12:"requirements";a:1:{s:19:"_access_system_cron";s:4:"TRUE";}s:7:"options";a:3:{s:14:"compiler_class";s:33:"Drupal\Core\Routing\RouteCompiler";s:4:"utf8";b:1;s:14:"_access_checks";a:1:{i:0;s:17:"access_check.cron";}}s:7:"schemes";a:0:{}s:7:"methods";a:2:{i:0;s:3:"GET";i:1;s:4:"POST";}s:9:"condition";s:0:"";s:8:"compiled";O:33:"Drupal\Core\Routing\CompiledRoute":11:{s:4:"vars";a:4:{i:0;s:8:"stage_id";i:1;s:17:"installed_version";i:2;s:14:"target_version";i:3;s:3:"key";}s:11:"path_prefix";s:0:"";s:10:"path_regex";s:136:"#^/automatic\-update/cron/post\-apply/(?P<stage_id>[^/]++)/(?P<installed_version>[^/]++)/(?P<target_version>[^/]++)/(?P<key>[^/]++)$#sDu";s:11:"path_tokens";a:5:{i:0;a:5:{i:0;s:8:"variable";i:1;s:1:"/";i:2;s:6:"[^/]++";i:3;s:3:"key";i:4;b:1;}i:1;a:5:{i:0;s:8:"variable";i:1;s:1:"/";i:2;s:6:"[^/]++";i:3;s:14:"target_version";i:4;b:1;}i:2;a:5:{i:0;s:8:"variable";i:1;s:1:"/";i:2;s:6:"[^/]++";i:3;s:17:"installed_version";i:4;b:1;}i:3;a:5:{i:0;s:8:"variable";i:1;s:1:"/";i:2;s:6:"[^/]++";i:3;s:8:"stage_id";i:4;b:1;}i:4;a:2:{i:0;s:4:"text";i:1;s:33:"/automatic-update/cron/post-apply";}}s:9:"path_vars";a:4:{i:0;s:8:"stage_id";i:1;s:17:"installed_version";i:2;s:14:"target_version";i:3;s:3:"key";}s:10:"host_regex";N;s:11:"host_tokens";a:0:{}s:9:"host_vars";a:0:{}s:3:"fit";i:112;s:14:"patternOutline";s:41:"/automatic-update/cron/post-apply/%/%/%/%";s:8:"numParts";i:7;}}',
    'number_parts' => '7',
  ))
  ->values(array(
    'name' => 'automatic_updates.finish',
    'path' => '/automatic-update/finish',
    'pattern_outline' => '/automatic-update/finish',
    'fit' => '3',
    'route' => 'O:31:"Symfony\Component\Routing\Route":9:{s:4:"path";s:24:"/automatic-update/finish";s:4:"host";s:0:"";s:8:"defaults";a:1:{s:11:"_controller";s:63:"\Drupal\automatic_updates\Controller\UpdateController::onFinish";}s:12:"requirements";a:1:{s:11:"_permission";s:27:"administer software updates";}s:7:"options";a:5:{s:14:"compiler_class";s:33:"Drupal\Core\Routing\RouteCompiler";s:19:"_maintenance_access";b:1;s:37:"_automatic_updates_readiness_messages";s:4:"skip";s:4:"utf8";b:1;s:14:"_access_checks";a:1:{i:0;s:23:"access_check.permission";}}s:7:"schemes";a:0:{}s:7:"methods";a:2:{i:0;s:3:"GET";i:1;s:4:"POST";}s:9:"condition";s:0:"";s:8:"compiled";O:33:"Drupal\Core\Routing\CompiledRoute":11:{s:4:"vars";a:0:{}s:11:"path_prefix";s:0:"";s:10:"path_regex";s:32:"#^/automatic\-update/finish$#sDu";s:11:"path_tokens";a:1:{i:0;a:2:{i:0;s:4:"text";i:1;s:24:"/automatic-update/finish";}}s:9:"path_vars";a:0:{}s:10:"host_regex";N;s:11:"host_tokens";a:0:{}s:9:"host_vars";a:0:{}s:3:"fit";i:3;s:14:"patternOutline";s:24:"/automatic-update/finish";s:8:"numParts";i:2;}}',
    'number_parts' => '2',
  ))
  ->values(array(
    'name' => 'automatic_updates.module_update',
    'path' => '/admin/modules/automatic-update',
    'pattern_outline' => '/admin/modules/automatic-update',
    'fit' => '7',
    'route' => 'O:31:"Symfony\Component\Routing\Route":9:{s:4:"path";s:31:"/admin/modules/automatic-update";s:4:"host";s:0:"";s:8:"defaults";a:1:{s:11:"_controller";s:78:"\Drupal\automatic_updates\Controller\UpdateController::redirectDeprecatedRoute";}s:12:"requirements";a:1:{s:7:"_access";s:4:"TRUE";}s:7:"options";a:4:{s:14:"compiler_class";s:33:"Drupal\Core\Routing\RouteCompiler";s:4:"utf8";b:1;s:12:"_admin_route";b:1;s:14:"_access_checks";a:1:{i:0;s:20:"access_check.default";}}s:7:"schemes";a:0:{}s:7:"methods";a:2:{i:0;s:3:"GET";i:1;s:4:"POST";}s:9:"condition";s:0:"";s:8:"compiled";O:33:"Drupal\Core\Routing\CompiledRoute":11:{s:4:"vars";a:0:{}s:11:"path_prefix";s:0:"";s:10:"path_regex";s:39:"#^/admin/modules/automatic\-update$#sDu";s:11:"path_tokens";a:1:{i:0;a:2:{i:0;s:4:"text";i:1;s:31:"/admin/modules/automatic-update";}}s:9:"path_vars";a:0:{}s:10:"host_regex";N;s:11:"host_tokens";a:0:{}s:9:"host_vars";a:0:{}s:3:"fit";i:7;s:14:"patternOutline";s:31:"/admin/modules/automatic-update";s:8:"numParts";i:3;}}',
    'number_parts' => '3',
  ))
  ->values(array(
    'name' => 'automatic_updates.report_update',
    'path' => '/admin/reports/updates/automatic-update',
    'pattern_outline' => '/admin/reports/updates/automatic-update',
    'fit' => '15',
    'route' => 'O:31:"Symfony\Component\Routing\Route":9:{s:4:"path";s:39:"/admin/reports/updates/automatic-update";s:4:"host";s:0:"";s:8:"defaults";a:1:{s:11:"_controller";s:78:"\Drupal\automatic_updates\Controller\UpdateController::redirectDeprecatedRoute";}s:12:"requirements";a:1:{s:7:"_access";s:4:"TRUE";}s:7:"options";a:4:{s:14:"compiler_class";s:33:"Drupal\Core\Routing\RouteCompiler";s:4:"utf8";b:1;s:12:"_admin_route";b:1;s:14:"_access_checks";a:1:{i:0;s:20:"access_check.default";}}s:7:"schemes";a:0:{}s:7:"methods";a:2:{i:0;s:3:"GET";i:1;s:4:"POST";}s:9:"condition";s:0:"";s:8:"compiled";O:33:"Drupal\Core\Routing\CompiledRoute":11:{s:4:"vars";a:0:{}s:11:"path_prefix";s:0:"";s:10:"path_regex";s:47:"#^/admin/reports/updates/automatic\-update$#sDu";s:11:"path_tokens";a:1:{i:0;a:2:{i:0;s:4:"text";i:1;s:39:"/admin/reports/updates/automatic-update";}}s:9:"path_vars";a:0:{}s:10:"host_regex";N;s:11:"host_tokens";a:0:{}s:9:"host_vars";a:0:{}s:3:"fit";i:15;s:14:"patternOutline";s:39:"/admin/reports/updates/automatic-update";s:8:"numParts";i:4;}}',
    'number_parts' => '4',
  ))
  ->values(array(
    'name' => 'automatic_updates.theme_update',
    'path' => '/admin/theme/automatic-update',
    'pattern_outline' => '/admin/theme/automatic-update',
    'fit' => '7',
    'route' => 'O:31:"Symfony\Component\Routing\Route":9:{s:4:"path";s:29:"/admin/theme/automatic-update";s:4:"host";s:0:"";s:8:"defaults";a:1:{s:11:"_controller";s:78:"\Drupal\automatic_updates\Controller\UpdateController::redirectDeprecatedRoute";}s:12:"requirements";a:1:{s:7:"_access";s:4:"TRUE";}s:7:"options";a:4:{s:14:"compiler_class";s:33:"Drupal\Core\Routing\RouteCompiler";s:4:"utf8";b:1;s:12:"_admin_route";b:1;s:14:"_access_checks";a:1:{i:0;s:20:"access_check.default";}}s:7:"schemes";a:0:{}s:7:"methods";a:2:{i:0;s:3:"GET";i:1;s:4:"POST";}s:9:"condition";s:0:"";s:8:"compiled";O:33:"Drupal\Core\Routing\CompiledRoute":11:{s:4:"vars";a:0:{}s:11:"path_prefix";s:0:"";s:10:"path_regex";s:37:"#^/admin/theme/automatic\-update$#sDu";s:11:"path_tokens";a:1:{i:0;a:2:{i:0;s:4:"text";i:1;s:29:"/admin/theme/automatic-update";}}s:9:"path_vars";a:0:{}s:10:"host_regex";N;s:11:"host_tokens";a:0:{}s:9:"host_vars";a:0:{}s:3:"fit";i:7;s:14:"patternOutline";s:29:"/admin/theme/automatic-update";s:8:"numParts";i:3;}}',
    'number_parts' => '3',
  ))
  ->values(array(
    'name' => 'automatic_updates.update_readiness',
    'path' => '/admin/automatic_updates/readiness',
    'pattern_outline' => '/admin/automatic_updates/readiness',
    'fit' => '7',
    'route' => 'O:31:"Symfony\Component\Routing\Route":9:{s:4:"path";s:34:"/admin/automatic_updates/readiness";s:4:"host";s:0:"";s:8:"defaults";a:2:{s:11:"_controller";s:68:"\Drupal\automatic_updates\Controller\ReadinessCheckerController::run";s:6:"_title";s:25:"Update readiness checking";}s:12:"requirements";a:1:{s:11:"_permission";s:27:"administer software updates";}s:7:"options";a:6:{s:14:"compiler_class";s:33:"Drupal\Core\Routing\RouteCompiler";s:19:"_maintenance_access";b:1;s:37:"_automatic_updates_readiness_messages";s:4:"skip";s:4:"utf8";b:1;s:12:"_admin_route";b:1;s:14:"_access_checks";a:1:{i:0;s:23:"access_check.permission";}}s:7:"schemes";a:0:{}s:7:"methods";a:2:{i:0;s:3:"GET";i:1;s:4:"POST";}s:9:"condition";s:0:"";s:8:"compiled";O:33:"Drupal\Core\Routing\CompiledRoute":11:{s:4:"vars";a:0:{}s:11:"path_prefix";s:0:"";s:10:"path_regex";s:41:"#^/admin/automatic_updates/readiness$#sDu";s:11:"path_tokens";a:1:{i:0;a:2:{i:0;s:4:"text";i:1;s:34:"/admin/automatic_updates/readiness";}}s:9:"path_vars";a:0:{}s:10:"host_regex";N;s:11:"host_tokens";a:0:{}s:9:"host_vars";a:0:{}s:3:"fit";i:7;s:14:"patternOutline";s:34:"/admin/automatic_updates/readiness";s:8:"numParts";i:3;}}',
    'number_parts' => '3',
  ))
  ->execute();
// cSpell:enable

$routes = [
  'system.batch_page.html',
  'system.status',
  'system.theme_install',
  'update.confirmation_page',
  'update.module_install',
  'update.module_update',
  'update.report_install',
  'update.report_update',
  'update.settings',
  'update.status',
  'update.theme_update',
];
foreach ($routes as $name) {
  $route = $connection->select('router', 'r')
    ->fields('r', ['route'])
    ->condition('name', $name)
    ->execute()
    ->fetchField();
  $route = unserialize($route);
  assert($route instanceof Route, "Route $name is not unserializable.");
  $route->setOption('_automatic_updates_readiness_messages', 'skip');
  $connection->update('router')
    ->fields([
      'route' => serialize($route),
    ])
    ->condition('name', $name)
    ->execute();
}

// Reset the SQL mode.
if ($connection->databaseType() === 'mysql') {
  $connection->query("SET sql_mode = '$sql_mode'");
}