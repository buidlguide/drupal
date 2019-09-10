<?php

namespace Drupal\Tests\nodeorder\Kernel;

/**
 * Tests module installation.
 *
 * @group nodeorder
 */
class NodeorderInstallTest extends NodeorderInstallTestBase {

  /**
   * Tests module installation.
   */
  public function testInstall() {
    $schema = $this->database->schema();

    $column_exists = $schema->fieldExists('taxonomy_index', 'weight');
    $this->assertTrue($column_exists);

    $index_exists = $schema->indexExists('taxonomy_index', 'weight');
    $this->assertTrue($index_exists);

    $extension_config = $this->container->get('config.factory')->getEditable('core.extension');
    $this->assertEqual($extension_config->get('module.nodeorder'), 5);
  }

}
