<?php

declare(strict_types=1);

namespace Drupal\iconify_icons_provider\Plugin\IconExtractor;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Theme\Icon\IconDefinition;
use Drupal\iconify_icons\IconifyServiceInterface;
use Drupal\Core\Theme\Icon\Attribute\IconExtractor;
use Drupal\Core\Theme\Icon\Exception\IconPackConfigErrorException;
use Drupal\Core\Theme\Icon\IconExtractorBase;
use Drupal\Core\Theme\Icon\IconPackExtractorForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the icon extractor.
 *
 * @internal
 *   Plugin classes are internal.
 */
#[IconExtractor(
  id: 'iconify',
  label: new TranslatableMarkup('Iconify'),
  description: new TranslatableMarkup('Provides Iconify list of Icons.'),
  forms: [
    'settings' => IconPackExtractorForm::class,
  ]
)]
class IconifyExtractor extends IconExtractorBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a IconifyExtractor object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\iconify_icons\IconifyServiceInterface $iconify
   *   Drupal Iconify service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected IconifyServiceInterface $iconify,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('iconify_icons.iconify_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function discoverIcons(): array {
    $config = $this->configuration['config'] ?? [];

    if (!isset($config['collections'])) {
      throw new IconPackConfigErrorException(sprintf('Missing `config: collections` in your definition, extractor %s require this value.', $this->getPluginId()));
    }

    unset($this->configuration['config']);

    $icons = [];
    foreach ($config['collections'] as $collection) {
      $icons_collection = $this->iconify->getIconsByCollection($collection);
      if (empty($icons_collection)) {
        continue;
      }

      foreach ($icons_collection as $icon_id) {
        if (!is_string($icon_id)) {
          continue;
        }

        $source = sprintf($this->iconify::DESIGN_DOWNLOAD_API_ENDPOINT, $collection, $icon_id);
        $id = IconDefinition::createIconId($this->configuration['id'], $icon_id);
        $icons[$id] = ['source' => $source];
      }
    }

    return $icons;
  }

}
