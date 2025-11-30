<?php

namespace Drupal\Tests\better_exposed_filters\FunctionalJavascript;

use Drupal\views\Views;

/**
 * Tests BEF pagers.
 *
 * @group better_exposed_filters
 */
class BetterExposedFiltersAjaxTest extends BetterExposedFiltersTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->turnAjaxOn();
  }

  /**
   * Tests ajax pager links.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPagerAjax(): void {
    $view = Views::getView('bef_test');

    // Enable AJAX on the test view.
    \Drupal::configFactory()->getEditable('views.view.bef_test')
      ->set('display.default.display_options.use_ajax', TRUE)
      ->save();

    $this->setBetterExposedOptions($view, [
      'pager' => [
        'plugin_id' => 'bef_links',
      ],
    ]);

    // Visit the bef-test page.
    $this->drupalGet('bef-test');

    $this->clickLink('10');
    // Verify ajax runs not a reload.
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Tests that bef_links triggers ajax call.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testLinkAjax(): void {
    $view = Views::getView('bef_test');

    $this->setBetterExposedOptions($view, [
      'filter' => [
        'field_bef_integer_value' => [
          'plugin_id' => 'bef_links',
        ],
      ],
    ]);

    $this->drupalGet('/bef-test');
    $page = $this->getSession()->getPage();

    $this->assertSession()->pageTextContains('Page one');
    $this->assertSession()->pageTextContains('Page two');

    $page->clickLink('One');
    // Verify ajax runs not a reload.
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertSession()->pageTextContains('Page one');
    $this->assertSession()->pageTextNotContains('Page two');
  }

}
