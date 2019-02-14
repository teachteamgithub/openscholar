<?php

namespace Drupal\os_publications\Plugin\CitationDistribution;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Citation Distribute GoogleScholar service.
 *
 * @CitationDistribute(
 *   id = "citation_distribute_googlescholar",
 *   title = @Translation("Google scholar citation distribute service."),
 *   type = "metadata",
 *   name = "Google Scholar"
 * )
 */
class CitationDistributeGooglescholar implements CitationDistributionInterface, ContainerFactoryPluginInterface {

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function save($id, $plugin) {
    /*
     * google_scholar themes a node if it has an entry in {citation_distribute}
     * with type=google_scholar to reach this point that must have happened, so
     * the change is already saved.
     */
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function mapMetadata($id) {

    $entity = $this->entityTypeManager->getStorage('bibcite_reference')->load($id);

    $metadata = [
      'citation_journal_title' => 'bibcite_secondary_title',
      'citation_publisher' => 'bibcite_publisher',
      'citation_title' => 'title',
      'citation_year' => 'bibcite_year',
      'citation_volume' => 'bibcite_volume',
      'citation_issue' => 'bibcite_issue',
      'citation_issn' => 'bibcite_issn',
      'citation_isbn' => 'bibcite_isbn',
      'citation_language' => 'bibcite_lang',
      'citation_abstract' => 'bibcite_abst_e',
      'citation_abstract_html_url' => 'bibcite_url',
    ];

    foreach ($metadata as $key => $value) {
      $metadata[$key] = (isset($entity->get($value)->value)) ? htmlspecialchars(strip_tags($entity->get($value)->value), ENT_COMPAT, 'ISO-8859-1', FALSE) : NULL;
    }

    /** @var object $keywords */
    $keywords = $entity->get('keywords');
    if (isset($keywords)) {
      foreach ($keywords as $reference) {
        $target_id = $reference->target_id;
        $keyword_obj = $this->entityTypeManager->getStorage('bibcite_keyword')->load($target_id);
        $keywords_arr[] = $keyword_obj->name->value;
      }
    }

    if (isset($keywords_arr)) {
      $metadata['citation_keywords'] = htmlspecialchars(strip_tags(implode(';', $keywords_arr)), ENT_COMPAT, 'ISO-8859-1', FALSE);
    }

    if (isset($entity->bibcite_year, $entity->bibcite_date)) {
      $metadata['citation_publication_date'] = $this->googleScholarDate($entity->bibcite_year->value, $entity->bibcite_date->value);
    }

    /** @var object $contributors */
    $contributors = $entity->get('author');
    if ($contributors) {
      foreach ($contributors as $reference) {
        $target_id = $reference->target_id;
        $contributor_obj = $this->entityTypeManager->getStorage('bibcite_contributor')->load($target_id);
        $contributors_arr[] = $contributor_obj->name->value;
      }
    }
    if (isset($contributors_arr)) {
      $metadata['citation_author'] = $this->googleScholarListAuthors($contributors_arr);
    }

    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function render($id) {
    $metadata = $this->mapMetadata($id);

    /*
     * Themable function to generate message after user submits
     * cite_distribute widget selections
     *
     * @param array $metadata
     *          associative array of GS metadata
     * @return unknown HTML string of that metadata
     */
    $output = [];
    foreach ($metadata as $key => $value) {
      if ($value) {
        if (is_array($value)) {
          foreach ($value as $subvalue) {
            $output[] = [
              '#tag' => 'meta',
              '#attributes' => [
                'name' => $key,
                'content' => $subvalue,
              ],
            ];
          }
        }
        else {
          $output[] = [
            '#tag' => 'meta',
            '#attributes' => [
              'name' => $key,
              'content' => $value,
            ],
          ];
        }
      }
    }
    return $output;
  }

  /**
   * Returns array of author names formatted for google scholar.
   *
   * @param array $contributors
   *   Authors.
   *
   * @return array
   *   Authors List.
   */
  protected function googleScholarListAuthors(array $contributors = []) {
    $authors = [];
    foreach ($contributors as $cont) {
      $authors[] = htmlspecialchars(strip_tags($cont), ENT_COMPAT, 'ISO-8859-1', FALSE);
    }
    return $authors;
  }

  /**
   * Returns $date in YYYY/M/D if possible. just year if not.
   *
   * @param int $year
   *   Year.
   * @param int $date
   *   Date.
   *
   * @return bool|false|string
   *   Date/Year.
   */
  protected function googleScholarDate($year, $date) {
    if ($date) {
      return date('Y/m/d', strtotime($date));
    }

    if ($year) {
      return $year;
    }

    return FALSE;
  }

}
