<?php

namespace Drupal\schema_recipe\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaImageBase;

/**
 * Provides a plugin for the 'image' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_recipe_image",
 *   label = @Translation("image"),
 *   description = @Translation("RECOMMENDED BY GOOGLE. The primary image for this item."),
 *   name = "image",
 *   group = "schema_recipe",
 *   weight = 8,
 *   type = "image",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaRecipeImage extends SchemaImageBase {

}
