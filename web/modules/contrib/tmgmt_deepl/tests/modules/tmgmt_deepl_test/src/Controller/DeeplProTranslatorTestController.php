<?php

namespace Drupal\tmgmt_deepl_test\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Mock services for DeepL Pro translator.
 */
class DeeplProTranslatorTestController extends DeeplTranslatorTestController {

  /**
   * {@inheritDoc}
   */
  public function getUsageData(Request $request): JsonResponse {
    // Authorization failed.
    if ($request->headers->get('Authorization') != 'DeepL-Auth-Key correct deepl key') {
      return new JsonResponse(403, 403);
    }

    // Sample response with usage data.
    $response = [
      'character_count' => 180118,
      'character_limit' => 1500000,
    ];
    return new JsonResponse($response);
  }

}
