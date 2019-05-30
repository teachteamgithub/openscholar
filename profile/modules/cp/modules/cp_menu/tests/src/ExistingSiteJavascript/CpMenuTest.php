<?php

namespace Drupal\Tests\cp_menu\ExistingSiteJavaScript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * CpMenuTest.
 *
 * @group functional-javascript
 * @group cp-menu
 */
class CpMenuTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Group Id.
   *
   * @var string
   */
  protected $id;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-menu',
      ],
    ]);
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->id = $this->group->id();
    // Test as groupAdmin.
    $this->drupalLogin($this->groupAdmin);
  }

  /**
   * Tests Menu List drag re-ordering.
   */
  public function testMenuLinksReorder() {

    $this->visit('/test-menu/cp/build/menu');
    $session = $this->assertSession();
    $page = $this->getCurrentPage();

    $weight_field = $page->find('css', '.Home select.menu-weight');
    // Original weight.
    $weight_original = $weight_field->getValue();
    $link = $page->find('css', '.Home .tabledrag-handle');
    // Drag from odd row to even row.
    $link->dragTo($page->find('css', '.draggable.even'));
    // Check if changes display warning message.
    $session->waitForElementVisible('css', '.tabledrag-changed-warning');
    // Save the settings.
    $page->pressButton('edit-submit');
    // Changed weight.
    $weight_new = $weight_field->getValue();
    // Compare if link is re ordered.
    $this->assertNotEquals($weight_new, $weight_original);
  }

  /**
   * Tests Add New Menu.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAddNewMenu() {

    $this->visit('/test-menu/cp/build/menu');
    $session = $this->assertSession();
    $page = $this->getCurrentPage();
    $page->clickLink('add_new_menu');
    $session->waitForElementVisible('css', '#new-menu-modal-form');
    $edit = [
      'title' => 'Third Menu',
      'menu_name' => 'third_menu',
    ];
    $this->submitForm($edit, 'Save');
    $session->waitForText('Third Menu');
  }

}
