<?php

namespace Drupal\iq_multidomain_extensions\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The settings form for iq_multidomain_extensions.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Create a new instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ModuleHandlerInterface $moduleHandler, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configFactory);
    $this->moduleHandler = $moduleHandler;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iq_multidomain_extensions_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['iq_multidomain_extensions.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('iq_multidomain_extensions.settings');
    $stylingProfileThemeSwitch = $this->moduleHandler->moduleExists('styling_profiles_domain_switch');

    $form['menu'] = [
      '#type' => 'details',
      '#title' => $this->t('Menu'),
      '#description' => $this->t('This section allows to preset the menu settings.'),
      '#open' => TRUE,
    ];

    $form['menu']['create_menu'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create a menu per domain.'),
      '#description' => $this->t('Whether to automatically create a menu for a new domain entry.'),
      '#default_value' => !empty($config->get('create_menu')) ? $config->get('create_menu') : 0,
    ];

    $contentTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    $form['menu']['menu_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content types for new menu.'),
      '#description' => $this->t('Content types for new menu.'),
      '#options' => $contentTypesList,
      '#default_value' => $config->get('menu_content_types') ?? [],
    ];

    $form['menu']['create_menu'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create a menu per domain.'),
      '#description' => $this->t('Whether to automatically create a menu for a new domain entry.'),
      '#default_value' => !empty($config->get('create_menu')) ? $config->get('create_menu') : 0,
    ];

    $form['styling'] = [
      '#type' => 'details',
      '#title' => $this->t('Design'),
      '#description' => $this->t('This section allows to automate design and styling when creating a new domain.'),
    ];
    $form['styling']['create_styling_profile'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Create styling profile'),
      '#description' => $this->t('Whether to automatically create a styling profile with each new domain.') . ((!$stylingProfileThemeSwitch) ? '<br />' . $this->t('Only available if styling_profiles_domain_switch is installed.') : ''),
      '#default_value' => $config->get('create_styling_profile'),
      '#disabled' => !$stylingProfileThemeSwitch,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('iq_multidomain_extensions.settings')
      ->set('create_menu', $form_state->getValue('create_menu'))
      ->set('menu_content_types', $form_state->getValue('menu_content_types'))
      ->set('create_styling_profile', $form_state->getValue('create_styling_profile'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
