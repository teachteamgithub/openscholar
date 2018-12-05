<?php

/**
 * @file
 * Contains Drupal\gdoc_field\Plugin\Field\FieldFormatter\GdocFieldFormatter.
 */

namespace Drupal\os_office_embed\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'os_office_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "os_office_embed",
 *   label = @Translation("Embedded Office Documents"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class OsOfficeEmbedFormatter extends FileFormatterBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      // Implement default settings.
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return array(
      // Implement settings form.
    ) + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $entity = $this->fieldDefinition->getTargetEntityTypeId();
      $bundle = $this->fieldDefinition->getTargetBundle();
      $field_name = $this->fieldDefinition->getName();
      $field_type = $this->fieldDefinition->getType();
      $file_uri = $file->getFileUri();
      $filename = $file->getFileName();
      $uri_scheme = \Drupal::service("file_system")->uriScheme($file_uri);

      if ($uri_scheme == 'public') {
        $url = file_create_url($file->getFileUri());
        $elements[$delta] = array(
          '#theme' => 'os_office_embed',
          '#url' => $url,
          '#filename' => $filename,
          '#delta' => $delta,
          '#entity' => $entity,
          '#bundle' => $bundle,
          '#field_name' => $field_name,
          '#field_type' => $field_type,
        );

      }
      else {
        drupal_set_message(
          t('The file (%file) is not publicly accessible. It must be publicly available in order for the Office embed to be able to access it.',
          array('%file' => $filename)
          ),
          'error',
          FALSE
        );
      }
    }

    return $elements;
  }

}
