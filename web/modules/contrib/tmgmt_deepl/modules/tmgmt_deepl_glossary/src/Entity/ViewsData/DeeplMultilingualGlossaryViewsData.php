<?php

namespace Drupal\tmgmt_deepl_glossary\Entity\ViewsData;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the deepl_ml_glossary entity type.
 */
class DeeplMultilingualGlossaryViewsData extends EntityViewsData {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getViewsData(): array {
    $data = parent::getViewsData();

    // Set custom filter for tmgt_translator.
    assert(is_array($data['tmgmt_deepl_ml_glossary']));
    assert(is_array($data['tmgmt_deepl_ml_glossary']['tmgmt_translator']));
    assert(is_array($data['tmgmt_deepl_ml_glossary']['tmgmt_translator']['filter']));
    $data['tmgmt_deepl_ml_glossary']['tmgmt_translator']['filter']['id'] = 'tmgmt_deepl_glossary_allowed_translators';

    // Related dictionaries.
    $data['tmgmt_deepl_ml_glossary']['related_dictionaries'] = [
      'title' => $this->t('Related Dictionaries'),
      'help' => $this->t('Display linked names of related deepl_ml_glossary_dictionary entities.'),
      'field' => [
        'id' => 'deepl_ml_glossary_related_dictionaries',
      ],
    ];
    return $data;
  }

}
