<?php

namespace Drupal\iconify_icons_provider\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\iconify_icons\IconifyServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin settings for Iconify Icons Provider.
 *
 * @package Drupal\iconify_icons\Form
 */
class Settings extends ConfigFormBase {

  /**
   * The iconify icons service.
   *
   * @var \Drupal\iconify_icons\IconifyServiceInterface
   */
  protected IconifyServiceInterface $iconify;

  /**
   * Constructs a Settings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager.
   * @param \Drupal\iconify_icons\IconifyServiceInterface $iconify
   *   The iconify icons service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $typedConfigManager, IconifyServiceInterface $iconify) {
    parent::__construct($config_factory, $typedConfigManager);
    $this->iconify = $iconify;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('iconify_icons.iconify_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['iconify_icons_provider.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iconify_icons_provider_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('iconify_icons_provider.settings');
    $form = parent::buildForm($form, $form_state);

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon Collections'),
      '#size' => 60,
      '#maxlength' => 128,
      '#placeholder' => $this->t('Filter collections'),
      '#attributes' => [
        'class' => ['iconify-icons-widget-checkboxes-filter'],
      ],
      '#attached' => [
        'library' => ['iconify_icons/default'],
      ],
    ];

    $form['collections'] = [
      '#type' => 'checkboxes',
      '#options' => $this->getOptions(),
      '#default_value' => $config->get('collections') ?? [],
      '#description' => $this->t('Select the collections which are going to provide the pack of icons. See @collectionIconsLink list.', [
        '@collectionIconsLink' => Link::fromTextAndUrl($this->t('the Iconify icon collections'), Url::fromUri('https://icon-sets.iconify.design/', [
          'attributes' => [
            'target' => '_blank',
          ],
        ]))->toString(),
      ]),
      '#attributes' => [
        'class' => ['iconify-icons-widget-collections'],
      ],
      '#attached' => [
        'library' => ['iconify_icons/default'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $collections = array_filter($form_state->getValue('collections'));
    $this->configFactory->getEditable('iconify_icons_provider.settings')
      ->set('collections', array_combine($collections, $collections))
      ->save();

    // Clear caches after submit the form.
    drupal_flush_all_caches();

    $this->messenger()->addStatus($this->t('Cache has been cleared.'));

    parent::submitForm($form, $form_state);
  }

  /**
   * Gets a formatted collection name for display purposes.
   *
   * @param array $collection
   *   An associative array containing information about the icon collection.
   * @param string $collection_id
   *   The unique identifier of the collection, used to create the link.
   *
   * @return string
   *   A formatted string with the collection's name, category, total icons,
   *   and a link to view the collection on the Iconify website.
   */
  protected function getCustomCollectionName(array $collection, string $collection_id): string {
    return sprintf(
      '<strong>%s</strong> - %s (%d) <a href="https://icon-sets.iconify.design/%s" target="_blank">See icons</a>',
      $collection['name'] ?? 'Unknown Name',
      $collection['category'] ?? 'Uncategorized',
      $collection['total'] ?? 0,
      $collection_id
    );
  }

  /**
   * Gets options for a select list of icon collections.
   *
   * @return array
   *   An associative array where the keys are collection IDs and the values are
   *   formatted collection names for display in a form select element.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \JsonException
   */
  protected function getOptions(): array {
    // Fetch and sort collections by 'total' in descending order.
    $collections = $this->iconify->getCollections();
    uasort($collections, fn($a, $b) => $b['total'] <=> $a['total']);

    $options = [];

    foreach ($collections as $collection_id => $collection) {
      $options[$collection_id] = $this->getCustomCollectionName($collection, $collection_id);
    }

    return $options;
  }

}
