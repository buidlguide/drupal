<?php

namespace Drupal\Tests\filefield_paths\Functional;

use Drupal\Component\Utility\Unicode;

/**
 * Test update functionality.
 *
 * @group File (Field) Paths
 */
class FileFieldPathsUpdateTest extends FileFieldPathsTestBase {
  /**
   * Test behaviour of Retroactive updates when no updates are needed.
   */
  public function testRetroEmpty() {
    // Create a File field.
    $field_name = Unicode::strtolower($this->randomMachineName());
    $this->createFileField($field_name, 'node', $this->contentType);

    // Trigger retroactive updates.
    $edit = [
      'third_party_settings[filefield_paths][retroactive_update]' => TRUE,
    ];
    $this->drupalPostForm("admin/structure/types/manage/{$this->contentType}/fields/node.{$this->contentType}.{$field_name}", $edit, $this->t('Save settings'));

    // Ensure no errors are thrown.
    $this->assertNoText('Error', t('No errors were found.'));
  }

  /**
   * Test basic Retroactive updates functionality.
   */
  public function testRetroBasic() {
    // Create an Image field.
    $field_name = Unicode::strtolower($this->randomMachineName());
    $this->createImageField($field_name, $this->contentType, []);

    // Modify display settings.
    /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
    $display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load("node.{$this->contentType}.default");
    $display->setComponent($field_name, [
      'settings' => [
        'image_style' => 'thumbnail',
        'image_link'  => 'content',
      ],
    ])->save();

    $this->drupalGet("admin/structure/types/manage/{$this->contentType}/display");
    /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $original_display */
    $original_display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load("node.{$this->contentType}.default");

    // Create a node with a test file.
    /** @var \Drupal\file\Entity\File $test_file */
    $test_file = $this->getTestFile('image');
    $nid = $this->uploadNodeFile($test_file, $field_name, $this->contentType);
    $this->drupalPostForm(NULL, ["{$field_name}[0][alt]" => $this->randomString()], $this->t('Save'));

    // Ensure that the file is in the default path.
    $this->drupalGet("node/{$nid}");
    $date = date('Y-m');
    $this->assertRaw("{$this->publicFilesDirectory}/styles/thumbnail/public/{$date}/{$test_file->getFilename()}", $this->t('The File is in the default path.'));

    // Trigger retroactive updates.
    $edit['third_party_settings[filefield_paths][retroactive_update]'] = TRUE;
    $edit['third_party_settings[filefield_paths][file_path][value]'] = 'node/[node:nid]';
    $this->drupalPostForm("admin/structure/types/manage/{$this->contentType}/fields/node.{$this->contentType}.{$field_name}", $edit, $this->t('Save settings'));

    // Ensure display settings haven't changed.
    // @see https://www.drupal.org/node/2276435
    \Drupal::entityTypeManager()->clearCachedDefinitions();
    $display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load("node.{$this->contentType}.default");
    $this->assert($original_display->getComponent($field_name) === $display->getComponent($field_name), t('Display settings have not changed.'));

    // Ensure that the file path has been retroactively updated.
    $this->drupalGet("node/{$nid}");
    $this->assertRaw("{$this->publicFilesDirectory}/styles/thumbnail/public/node/{$nid}/{$test_file->getFilename()}", $this->t('The File path has been retroactively updated.'));
  }

}
