<?php

namespace Drupal\ffw_test_module\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CustomSiteSettingsForm
 *
 * @package Drupal\ffw_test_module\Form
 */
class CustomSiteSettingsForm extends ConfigFormBase {

  /**
   * The id of the system site config
   */
  const SYSTEM_SITE_CONFIG_NAME = 'system.site';

  /**
   * Minimum number of the site name symbols.
   */
  const MINIMUM_NUMBER_OF_SITE_NAME_SYMBOLS = 6;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ffw_test_module_custom_site_settings_form_id';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::SYSTEM_SITE_CONFIG_NAME];
  }

  /**
   * Our custom form builder
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the editable system site config
    $config = $this->config(self::SYSTEM_SITE_CONFIG_NAME);

    $parent_form = parent::buildForm($form, $form_state);
    // Add new textfield to our form
    $form['ffw_test_module_site_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site name'),
      '#placeholder' => $this->t('My new site name'),
      '#default_value' => $config->get('name'),
      '#required' => TRUE,
    ];

    $form += $parent_form;
    // Override the default value of the button
    $form['actions']['submit']['#value'] = 'Save';

    return $form;
  }

  /**
   * Validation handler for our custom form
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $site_name = $form_state->getValue('ffw_test_module_site_name', '');
    $number_of_characters = strlen($site_name);

    if (!empty($site_name) && ($number_of_characters < static::MINIMUM_NUMBER_OF_SITE_NAME_SYMBOLS)) {
      $form_state->setErrorByName(
        'ffw_test_module_site_name',
        $this->t('Site name should contain not less than %minimum number of characters,
         but currently %number_of_characters characters long.',
          [
            '%minimum' => static::MINIMUM_NUMBER_OF_SITE_NAME_SYMBOLS,
            '%number_of_characters' => $number_of_characters,
          ]
        )
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the editable system site config
    $config = $this->config(self::SYSTEM_SITE_CONFIG_NAME);

    $config->set('name', $form_state->getValue('ffw_test_module_site_name', ''));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}

