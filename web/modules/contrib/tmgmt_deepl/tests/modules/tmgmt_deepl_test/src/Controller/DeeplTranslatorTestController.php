<?php

namespace Drupal\tmgmt_deepl_test\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mock services for DeepL translator.
 */
class DeeplTranslatorTestController {

  /**
   * Helper to trigger mok response error.
   *
   * @param string $domain
   *   - Domain.
   * @param string $reason
   *   - Reason.
   * @param string $message
   *   - Message.
   * @param string|null $locationType
   *   - Location type.
   * @param string|null $location
   *   - Location.
   */
  public function triggerResponseError(string $domain, string $reason, string $message, ?string $locationType = NULL, ?string $location = NULL): JsonResponse {

    $response = [
      'error' => [
        'errors' => [
          'domain' => $domain,
          'reason' => $reason,
          'message' => $message,
        ],
        'code' => 400,
        'message' => $message,
      ],
    ];

    if (isset($locationType)) {
      $response['error']['errors']['locationType'] = $locationType;
    }
    if (isset($location)) {
      $response['error']['errors']['location'] = $location;
    }

    return new JsonResponse($response, 400);
  }

  /**
   * Mock service to translate request.
   */
  public function translate(Request $request): JsonResponse {
    // Return 404 if Authorization header is empty.
    if ($request->headers->get('Authorization') != 'DeepL-Auth-Key correct deepl key') {
      return new JsonResponse(403, 403);
    }

    // Get request content.
    $content = $request->getContent();
    if (is_string($content)) {
      $content = json_decode($content);
    }

    // Check for required parameters.
    if (is_object($content) && !isset($content->text)) {
      return $this->triggerResponseError('global', 'required', 'Required parameter: text', 'parameter', 'text');
    }
    if (is_object($content) && !isset($content->source_lang)) {
      return $this->triggerResponseError('global', 'required', 'Required parameter: source_lang', 'parameter', 'source_lang');
    }
    if (is_object($content) && !isset($content->target_lang)) {
      return $this->triggerResponseError('global', 'required', 'Required parameter: target_lang', 'parameter', 'target_lang');
    }

    $translations = [
      'DE' => 'Hallo Welt',
      'EN' => 'Hello World',
      'FR' => 'Bonjour tout le monde',
    ];

    $response = [];
    if (is_object($content) && isset($content->target_lang)) {
      $response = [
        'translations' => [
          ['text' => $translations[$content->target_lang]],
        ],
      ];
    }

    return new JsonResponse($response);
  }

}
