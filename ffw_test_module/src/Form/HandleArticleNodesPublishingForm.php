<?php

namespace Drupal\ffw_test_module\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HandleArticleNodesPublishingForm extends FormBase {

  /**
   * Type of the node.
   */
  const ARTICLE_CONTENT_TYPE = 'article';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnection;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * @var LoggerInterface $logger
   */
  protected $logger;

  /**
   * HandleArticleNodesPublishingForm constructor.
   *
   * @param Connection $db_connection
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param LoggerInterface $logger
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(Connection $db_connection, EntityTypeManagerInterface $entity_type_manager, LoggerInterface $logger) {
    $this->dbConnection = $db_connection;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('logger.channel.fw_test_module')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ffw_test_module_handle_article_nodes_publishing_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $article_nodes = $this->getNodesByType(self::ARTICLE_CONTENT_TYPE);

    $form['ffw_publishing_container'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Control publishing options'),
    ];

    // Select box for the node
    $form['ffw_publishing_container']['article_nodes'] = [
      '#type' => 'select',
      '#title' => $this->t('Node title'),
      '#title_display' => 'invisible',
      '#required' => TRUE,
      '#default_value' => !empty($article_nodes) ? array_key_first($article_nodes) : null,
      '#options' => $article_nodes,
      '#empty_option' => $this->t('- Select -'),
    ];

    // Select box to control the node publishing options
    $form['ffw_publishing_container']['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Publish status'),
      '#title_display' => 'invisible',
      '#required' => TRUE,
      '#default_value' => NodeInterface::PUBLISHED,
      '#options' => [
        NodeInterface::PUBLISHED => $this->t('Published'),
        NodeInterface::NOT_PUBLISHED => $this->t('Unpublished'),
      ],
    ];

    // Select box to control the sticky options
    $form['ffw_publishing_container']['sticky'] = [
      '#type' => 'select',
      '#title' => $this->t('Sticky status'),
      '#title_display' => 'invisible',
      '#required' => TRUE,
      '#default_value' => NodeInterface::PUBLISHED,
      '#options' => [
        NodeInterface::STICKY => $this->t('Sticky'),
        NodeInterface::NOT_STICKY => $this->t('Not sticky'),
      ],
    ];

    // Form action for node update
    $form['ffw_publishing_container']['actions']['update'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
      '#op' => 'update',
    ];

    // Form action for node delete
    $form['ffw_publishing_container']['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#op' => 'delete',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the triggering element
    $trigger = $form_state->getTriggeringElement();
    // Get the triggering operation (update/delete)
    $op = $trigger['#op'];
    // Get the submitted values
    $values = $form_state->getValues()['ffw_publishing_container'];

    $node_id = $values['article_nodes'];
    $node = $this->nodeStorage->load($node_id);

    if (!$node) {
      return;
    }

    try {
      // Update status & sticky for given node.
      if ($op == 'update') {
        $node->set('sticky', $values['sticky']);
        $node->set('status', $values['status']);
        $node->save();
      }
      elseif ($op == 'delete') {
        $node->delete();
      }
    }
    catch (\Exception $exception) {
      $this->logger->notice($exception->getMessage());
    }

    if ($op === 'update') {
      $this->messenger()->addMessage($this->t('The node has been updated!'));
    }
    elseif ($op === 'delete') {
      $this->messenger()->addMessage($this->t('The node has been deleted!'));
    }
  }

  /**
   * Helper method to fetch the associative array with node ids and their titles
   *
   * @param string $type
   *
   * @return array
   */
  protected function getNodesByType($type = ''): array {
    $type = !empty($type) ? $type : static::ARTICLE_CONTENT_TYPE;

    try {
      $db_query = $this->dbConnection->select('node_field_data', 'node_d')
        ->condition('node_d.type', $type)
        ->fields('node_d', ['nid', 'title']);
      $nodes = $db_query->execute()->fetchAllKeyed();
    }
    catch (\Exception $exception) {
      // return empty array.
      return [];
    }

    return $nodes;
  }

}
