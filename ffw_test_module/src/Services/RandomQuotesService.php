<?php

namespace Drupal\ffw_test_module\Services;

use Drupal\Component\Serialization\Json;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Class RandomQuotesService
 *
 * @package Drupal\ffw_test_module\Services
 */
class RandomQuotesService {

  const GET_RANDOM_QUOTE_URL = 'https://api.whatdoestrumpthink.com/api/v1/quotes/random';

  /**
   * @var LoggerInterface $logger
   */
  protected $logger;

  /**
   * The http client
   *
   * @var \GuzzleHttp\ClientInterface $httpClient
   */
  protected $httpClient;

  /**
   * RandomQuotesService constructor.
   *
   * @param LoggerInterface $logger
   * @param ClientInterface $http_client
   */
  public function __construct(LoggerInterface $logger, ClientInterface $http_client) {
    $this->logger = $logger;
    $this->httpClient = $http_client;
  }

  /**
   * Get the random quote.
   *
   * @return string|null
   */
  public function getRandomQuoteFromUrl() {
    $response_data = $this->getDataFromResponse(self::GET_RANDOM_QUOTE_URL);
    $response = json_decode($response_data, TRUE);

    return isset($response['message']) ? strip_tags($response['message']) : null;
  }

  /**
   * @param string $url
   *
   * @return json|bool
   */
  private function getDataFromResponse(string $url) {
    try {
      $response = $this->httpClient->get($url);
    }
    catch (RequestException $exception) {
      $this->logger->notice($exception->getMessage());

      return FALSE;
    }

    return $response->getBody();
  }
}
