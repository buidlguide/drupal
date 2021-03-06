<?php

namespace Drupal\Tests\search_api_sorts\Functional;

use Drupal\Core\Url;

/**
 * Tests the default functionality of Search API sorts.
 *
 * @group search_api_sorts
 */
class IntegrationTest extends SortsFunctionalBase {

  /**
   * Tests sorting.
   */
  public function testFramework() {
    $this->drupalLogin($this->adminUser);

    // Add sorting on ID.
    $this->drupalGet('admin/config/search/search-api/index/' . $this->indexId . '/sorts');
    $this->drupalGet('admin/config/search/search-api/index/' . $this->indexId . '/sorts/' . $this->escapedDisplayId);
    $edit = [
      'sorts[id][status]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save settings');

    // Check for non-existence of the block first.
    $this->drupalGet('search-api-sorts-test');
    $this->assertSession()->linkNotExists('ID');

    $block_settings = [
      'region' => 'footer',
      'id' => 'sorts_id',
    ];
    $this->drupalPlaceBlock('search_api_sorts_block:' . $this->displayId, $block_settings);

    // Make sure the block is available and the ID link is shown, check that the
    // sorting applied is in alphabetical order.
    $this->drupalGet('search-api-sorts-test');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('ID');
    $this->assertPositions([
      'default | foo bar baz foobaz föö',
      'default | foo test foobuz',
      'default | foo baz',
      'default | bar baz',
    ]);

    // Click on the link and assert that the url now has changed, also check
    // that the sort order is still the same.
    $this->clickLink('ID');
    $this->assertSession()->statusCodeEquals(200);
    $url = Url::fromUserInput('/search-api-sorts-test', ['query' => ['sort' => 'id', 'order' => 'asc']]);
    $this->assertUrl($url);
    $this->assertPositions([
      'default | foo bar baz foobaz föö',
      'default | foo test foobuz',
      'default | foo baz',
      'default | bar baz',
    ]);

    // Click on the link again and assert that the url is now changed again and
    // that the sort order now also has changed.
    $this->clickLink('ID');
    $this->assertSession()->statusCodeEquals(200);
    $url = Url::fromUserInput('/search-api-sorts-test', ['query' => ['sort' => 'id', 'order' => 'desc']]);
    $this->assertUrl($url);
    $this->assertPositions([
      'default | bar baz',
      'default | foo baz',
      'default | foo test foobuz',
      'default | foo bar baz foobaz föö',
    ]);
  }

  /**
   * Tests that only enabled configs are saved.
   */
  public function testSavedConfigs() {
    $this->drupalLogin($this->adminUser);

    // Add sorting on ID, Authored on and Type.
    $this->drupalGet('admin/config/search/search-api/index/' . $this->indexId . '/sorts/' . $this->escapedDisplayId);
    $edit = [
      'sorts[id][status]' => TRUE,
      'sorts[created][status]' => TRUE,
      'sorts[type][status]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, 'Save settings');

    $configs_to_be_saved = ['id', 'created', 'type'];
    $configs_not_to_be_saved = ['search_api_relevance',
      'keywords',
      'category',
      'width',
    ];

    // Assert that only enabled sorts are saved in the database.
    foreach ($configs_to_be_saved as $config_id) {
      $this->assertNotEmpty($this->container->get('entity_type.manager')->getStorage('search_api_sorts_field')
        ->load($this->escapedDisplayId . '_' . $config_id), t("Config @config_name was not saved as expected", ['@config_name' => $config_id]));
    }
    foreach ($configs_not_to_be_saved as $config_id) {
      $this->assertEmpty($this->container->get('entity_type.manager')->getStorage('search_api_sorts_field')
        ->load($this->escapedDisplayId . '_' . $config_id), t("Config @config_name that should not have been saved was saved unexpectedly", ['@config_name' => $config_id]));
    }
  }

}
