<?php

namespace Drupal\ffw_test_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ffw_test_module\Services\RandomQuotesService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RandomQuotesBlock
 *
 * @Block(
 *   id = "ffw_test_module_random_quote_block",
 *   admin_label = @Translation("Random quotes"),
 *   category = @Translation("Custom"),
 * )
 */
class RandomQuotesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Random quotes service.
   *
   * @var RandomQuotesService $randomQuote
   */
  protected $randomQuote;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RandomQuotesService $random_quote
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->randomQuote = $random_quote;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ffw_test_module.get_random_quote_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $block = [
      '#theme' => 'ffw_test_module_get_random_quote',
      '#quote' => $this->randomQuote->getRandomQuoteFromUrl(),
    ];

    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}

