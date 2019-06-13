<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class GoogleScholarMappingTest.
 *
 * @group kernel
 * @group publications
 */
class GoogleScholarMappingTest extends TestBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $admin;

  /**
   * Normal user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->admin = $this->createUser([], '', TRUE);
  }

  /**
   * Test Metadata Mapping on entity page.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testGoogleScholarMetadataMapping() {
    $this->drupalLogin($this->admin);
    $reference = $this->createReference([
      'title' => 'Google Scholar Mapping test',
      'bibcite_year' => '2019',
      'bibcite_abst_e' => 'This is a test for Google Scholar test.',
      'bibcite_publisher' => 'testpublisher',
      'distribution' => [
        'citation_distribute_googlescholar',
      ],
    ]);
    $this->drupalGet('bibcite/reference/' . $reference->id());

    $this->assertSession()->responseContains('name="citation_title" content="Google Scholar Mapping test"');
    $this->assertSession()->responseContains('name="citation_year" content="2019"');
    $this->assertSession()->responseContains('name="citation_abstract" content="This is a test for Google Scholar test."');
    $this->assertSession()->responseContains('name="citation_publisher" content="testpublisher"');

  }

}
