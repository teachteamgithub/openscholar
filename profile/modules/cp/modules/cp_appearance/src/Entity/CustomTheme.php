<?php

namespace Drupal\cp_appearance\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\file\Entity\File;

/**
 * Defines the CustomTheme entity.
 *
 * @ConfigEntityType(
 *   id = "cp_custom_theme",
 *   label = @Translation("Cp Custom Theme"),
 *   handlers = {
 *     "form" = {
 *       "add" = "\Drupal\cp_appearance\Entity\Form\CustomThemeForm",
 *       "edit" = "\Drupal\cp_appearance\Entity\Form\CustomThemeForm",
 *     },
 *   },
 *   admin_permission = "manage cp appearance",
 *   config_prefix = "custom_theme",
 *   entity_keys = {
 *     "id" = "id",
 *     "base_theme" = "base_theme",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "base_theme",
 *     "favicon",
 *     "images",
 *   },
 *   links = {
 *     "add-form" = "/cp/appearance/custom-themes/add",
 *     "edit-form" = "/cp/appearance/custom-themes/{cp_custom_theme}/edit"
 *   }
 * )
 */
class CustomTheme extends ConfigEntityBase implements CustomThemeInterface {

  public const CUSTOM_THEMES_LOCATION = 'custom_themes';

  public const ABSOLUTE_CUSTOM_THEMES_LOCATION = DRUPAL_ROOT . '/../' . self::CUSTOM_THEMES_LOCATION;

  /**
   * The machine name of the custom theme.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the custom theme.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function setBaseTheme(string $theme): CustomThemeInterface {
    $this->set('base_theme', $theme);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseTheme(): ?string {
    return $this->get('base_theme');
  }

  /**
   * {@inheritdoc}
   */
  public function getFavicon(): ?int {
    return $this->get('favicon');
  }

  /**
   * {@inheritdoc}
   */
  public function setFavicon(int $favicon): CustomThemeInterface {
    $this->set('favicon', $favicon);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getImages(): ?array {
    return $this->get('images');
  }

  /**
   * {@inheritdoc}
   */
  public function setImages(array $images): CustomThemeInterface {
    $this->set('images', $images);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    $custom_theme_directory_path = self::ABSOLUTE_CUSTOM_THEMES_LOCATION . '/' . $this->id();
    $status = file_prepare_directory($custom_theme_directory_path, FILE_CREATE_DIRECTORY);

    if (!$status) {
      throw new CustomThemeException(t('Unable to create directory for storing the theme. Please contact the site administrator for support.'));
    }

    /** @var \Drupal\file\FileInterface $favicon */
    $favicon = File::load($this->getFavicon());
    file_unmanaged_move($favicon->getFileUri(), 'file://' . $custom_theme_directory_path . '/favicon.ico');
  }

}
