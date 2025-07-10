<?php

namespace Drupal\tmgmt_deepl_glossary;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\tmgmt\TranslatorInterface;
use Drupal\tmgmt_deepl_glossary\Entity\DeeplMultilingualGlossary;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * A service for managing DeepL glossary API calls.
 */
class DeeplMultilingualGlossaryApi implements DeeplMultilingualGlossaryApiInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The guzzle HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * The translator.
   *
   * @var \Drupal\tmgmt\TranslatorInterface
   */
  protected TranslatorInterface $translator;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * Constructs a new DeeplGlossaryApi.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The guzzle HTTP client.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The guzzle HTTP client.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ClientInterface $http_client, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->httpClient = $http_client;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public function setTranslator(TranslatorInterface $translator): void {
    $this->translator = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public function createMultilingualGlossary(string $name, array $dictionaries): array {
    // Get url to glossary endpoint.
    /** @var \Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator $deepl_translator */
    $deepl_translator = $this->translator->getPlugin();
    $url = $deepl_translator->getGlossaryUrl();

    // Build query params.
    $query_params = [];
    $query_params['name'] = trim($name);
    $query_params['dictionaries'] = [];
    foreach ($dictionaries as $dictionary) {
      assert(is_array($dictionary));
      $query_params['dictionaries'][] = [
        'source_lang' => $dictionary['source_lang'],
        'target_lang' => $dictionary['target_lang'],
        'entries' => $dictionary['entries'],
        'entries_format' => $dictionary['entries_format'],
      ];
    }
    // Add header.
    $headers = [];
    $headers['Content-Type'] = 'application/json';
    $response = $this->doRequest($url, 'POST', $query_params, $headers);
    if ($response) {
      return is_array($response['content']) ? $response['content'] : [];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function createMultilingualGlossaryDictionary(string $glossary_id, string $source_lang, string $target_lang, string $entries, string $entries_format = 'tsv'): array {
    // Get url to glossary endpoint.
    /** @var \Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator $deepl_translator */
    $deepl_translator = $this->translator->getPlugin();
    $url = $deepl_translator->getGlossaryUrl();
    // Set correct API endpoint.
    $url .= '/' . $glossary_id . '/dictionaries';

    // Build array for dictionary.
    $query_params = [
      'source_lang' => $source_lang,
      'target_lang' => $target_lang,
      'entries' => $entries,
      'entries_format' => $entries_format,
    ];

    // Add header.
    $headers = [];
    $headers['Content-Type'] = 'application/json';
    $response = $this->doRequest($url, 'PUT', $query_params, $headers);

    if ($response) {
      return is_array($response['content']) ? $response['content'] : [];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function editMultilingualGlossary(string $glossary_id, string $name, array $dictionaries = []): array {
    // Get url to glossary endpoint.
    /** @var \Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator $deepl_translator */
    $deepl_translator = $this->translator->getPlugin();
    $url = $deepl_translator->getGlossaryUrl();
    // Set correct API endpoint.
    $url .= '/' . $glossary_id;

    // Build query params.
    $query_params = [];
    $query_params['name'] = trim($name);
    $query_params['dictionaries'] = [];
    foreach ($dictionaries as $dictionary) {
      assert(is_array($dictionary));
      $query_params['dictionaries'][] = [
        'source_lang' => $dictionary['source_lang'],
        'target_lang' => $dictionary['target_lang'],
        'entries' => $dictionary['entries'],
        'entries_format' => $dictionary['entries_format'],
      ];
    }

    // Add header.
    $headers = [];
    $headers['Content-Type'] = 'application/json';
    $response = $this->doRequest($url, 'PATCH', $query_params, $headers);
    if ($response) {
      return is_array($response['content']) ? $response['content'] : [];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function doRequest(string $url, string $method = 'GET', array $query_params = [], array $headers = []): ?array {
    // Default authorization header.
    /** @var array<string, string|string[]> $headers */
    $headers['Authorization'] = 'DeepL-Auth-Key ' . $this->translator->getSetting('auth_key');

    // Build query string.
    if (isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json') {
      $body = json_encode($query_params);
    }
    else {
      $body = http_build_query($query_params);
    }

    // Build request object.
    assert(is_string($body));
    $request = new Request($method, $url, $headers, $body);

    // Send the request with the query.
    try {
      $response = $this->httpClient->send($request);
      // Get response body.
      $response_content = $response->getBody()->getContents();

      // Check if content is of type json.
      if ($this->isJsonString($response_content)) {
        return [
          'content' => json_decode($response_content, TRUE),
        ];
      }
      else {
        return [
          'content' => $response_content,
        ];
      }
    }
    catch (RequestException $e) {
      $this->processRequestError($e);
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMultilingualGlossaries(): array {
    // Get url to glossary endpoint.
    /** @var \Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator $deepl_translator */
    $deepl_translator = $this->translator->getPlugin();
    $url = $deepl_translator->getGlossaryUrl();

    // Get all glossaries.
    $response = $this->doRequest($url);
    if (is_array($response) && is_array($response['content']) && is_array($response['content']['glossaries'])) {
      return $response['content']['glossaries'];
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMultilingualGlossaryMetadata(string $glossary_id): array {

    // Get url to glossary endpoint.
    /** @var \Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator $deepl_translator */
    $deepl_translator = $this->translator->getPlugin();
    $url = $deepl_translator->getGlossaryUrl();
    // Add glossary_id to $url.
    $url .= '/' . $glossary_id;

    // Get all glossaries.
    $response = $this->doRequest($url);
    if (is_array($response) && is_array($response['content'])) {
      return $response['content'];
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultilingualGlossary(string $glossary_id): void {
    // Get url to glossary endpoint.
    /** @var \Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator $deepl_translator */
    $deepl_translator = $this->translator->getPlugin();
    $url = $deepl_translator->getGlossaryUrl();
    // Add glossary_id to $url.
    $url .= '/' . $glossary_id;
    // Perform delete request.
    $this->doRequest($url, 'DELETE');
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultilingualGlossaryDictionary(string $glossary_id, string $source_lang, string $target_lang): void {
    // Get url to glossary endpoint.
    /** @var \Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator $deepl_translator */
    $deepl_translator = $this->translator->getPlugin();
    $url = $deepl_translator->getGlossaryUrl();
    // Add glossary_id to $url.
    $url .= '/' . $glossary_id . '/dictionaries?source_lang=' . $source_lang . '&target_lang=' . $target_lang;
    // Perform delete request.
    $this->doRequest($url, 'DELETE');
  }

  /**
   * Build batch operations for fetching glossaries.
   */
  public function buildGlossariesFetchBatch(bool $delete_obsolete_free_glossaries = FALSE): void {
    $deepl_translators = DeeplMultilingualGlossary::getAllowedTranslators();
    if (count($deepl_translators) > 0) {
      $operations = [];

      // First, fetch all glossaries and dictionaries.
      foreach (array_keys($deepl_translators) as $deepl_translator) {
        /** @var \Drupal\tmgmt\TranslatorInterface $translator */
        $translator = $this->entityTypeManager->getStorage('tmgmt_translator')->load($deepl_translator);
        // Fetch glossaries and all dictionaries from DeepL API.
        $operations[] = [
          '\Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiBatch::fetchGlossariesAndDictionaries',
          [$translator, $delete_obsolete_free_glossaries],
        ];
      }

      // Define batch job.
      $batch = [
        'title' => $this->t('Syncing glossaries'),
        'operations' => $operations,
        'finished' => '\Drupal\tmgmt_deepl_glossary\DeeplMultilingualGlossaryApiBatch::syncGlossariesFinishedCallback',
      ];

      batch_set($batch);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMultilingualGlossaryEntries(string $glossary_id, string $source_lang, string $target_lang): array {
    // Get url to glossary endpoint.
    /** @var \Drupal\tmgmt_deepl\Plugin\tmgmt\Translator\DeeplTranslator $deepl_translator */
    $deepl_translator = $this->translator->getPlugin();
    $url = $deepl_translator->getGlossaryUrl();
    // Add glossary_id to $url.
    $url .= '/' . $glossary_id . '/entries?source_lang=' . $source_lang . '&target_lang=' . $target_lang;

    // Perform request.
    $headers = [];
    $headers['Content-Type'] = 'application/json';

    /** @var array $result */
    $result = $this->doRequest($url, 'GET', [], $headers);

    // Check for entries.
    if (isset($result['content']) && is_array($result['content']) && is_array($result['content']['dictionaries'])) {
      $dictionary = reset($result['content']['dictionaries']);
      assert(is_array($dictionary));
      // Get entries.
      $entries = (isset($dictionary['entries']) && is_string($dictionary['entries'])) ? $dictionary['entries'] : '';
      // Build array of glossary entries.
      $lines = explode(PHP_EOL, $entries);

      $glossary_entries = [];
      foreach ($lines as $line) {
        $glossary_entries[] = explode("\t", $line);
      }
      return $glossary_entries;
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function hasMultilingualGlossaryDictionary(string $glossary_id, string $source_lang, string $target_lang): bool {
    $glossary_metadata = $this->getMultilingualGlossaryMetadata($glossary_id);
    // Check for dictionaries.
    if (isset($glossary_metadata['dictionaries']) && is_array($glossary_metadata['dictionaries'])) {
      foreach ($glossary_metadata['dictionaries'] as $dictionary) {
        // In case we already have a dictionary return TRUE.
        assert(is_array($dictionary));
        if ($dictionary['source_lang'] == strtolower($source_lang) && $dictionary['target_lang'] == strtolower($target_lang)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Validate string with json content.
   *
   * @param string $string
   *   The string containing json or normal text.
   *
   * @return bool
   *   Whether string is of type json.
   */
  protected function isJsonString(string $string): bool {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
  }

  /**
   * Process possible request errors.
   *
   * @param \GuzzleHttp\Exception\RequestException $e
   *   The request exception.
   */
  protected function processRequestError(RequestException $e): void {
    $message = '';
    if ($e->hasResponse()) {
      $response = $e->getResponse();
      if ($response instanceof ResponseInterface) {
        // Get response body.
        $response_content = $response->getBody()->getContents();

        if ($this->isJsonString($response_content)) {
          /** @var object $response_content */
          $response_content = json_decode($response_content);
          $message = $response_content->message ?? '';
          $detail = $response_content->detail ?? '';
          $message = $this->t('DeepL API service returned an error: @message @detail', [
            '@message' => $message,
            '@detail' => $detail,
          ]);
        }
        else {
          $message = $this->t('DeepL API service returned following error: @error', ['@error' => $response->getReasonPhrase()]);
        }
      }
    }
    else {
      $response = $e->getHandlerContext();
      $error = $response['error'] ?? 'Unknown error';
      $message = $this->t('DeepL API service returned following error: @error', ['@error' => $error]);
    }
    $this->messenger->addError($message);
  }

}
