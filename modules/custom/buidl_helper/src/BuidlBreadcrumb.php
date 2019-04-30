<?php

namespace Drupal\buidl_helper;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

/**
 * {@inheritdoc}
 */
class BuidlBreadcrumb implements BreadcrumbBuilderInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The menu link manager interface.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\Taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * The menu active trail interface.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrailInterface
   */
  protected $menuActiveTrail;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MenuLinkManagerInterface $menu_link_manager,
    EntityManagerInterface $entityManager,
    MenuActiveTrailInterface $menu_active_trail
  ) {
    $this->config = '';
    $this->menuLinkManager = $menu_link_manager;
    $this->entityManager = $entityManager;
    $this->termStorage = $entityManager->getStorage('taxonomy_term');
    $this->menuActiveTrail = $menu_active_trail;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // This might be a "node" with no fields, e.g. a route to a "revision" URL,
    // so we don't check for taxonomy fields on unfieldable nodes:
    $node_object = $route_match->getParameters()->get('node');
    $node_is_fieldable = $node_object instanceof FieldableEntityInterface;
    if ($node_is_fieldable) {
      return TRUE;
    }
    return FALSE;
    $term_object = $route_match->getParameters()->get('taxonomy_term');
    if($term_object && $term_object->getEntityTypeId == 'taxonomy_term') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $node_object = $route_match->getParameters()->get('node');
    $node_is_fieldable = $node_object instanceof FieldableEntityInterface;
    $breadcrumb->addLink(Link::createFromRoute('Buidl', '<front>'));

    //First solve the Nodes 
    if($node_is_fieldable) {
      switch($node_object->bundle()) {
        case 'eb_organization':
          $_link = Url::fromUri('base:/organizations');
          $_text = 'Organizations';
          $breadcrumb->addLink(Link::fromTextAndUrl($_text, $_link));
        break;

        case 'eb_collections': 
        case 'eb_project':
        case 'eb_ideas': 
          $_links = _buidl_helper_get_entity_term($node_object, 'field_eb_categories');
          if($_links) {
            foreach($_links as $_link_) { 
              $breadcrumb->addLink($_link_);
            }
          }
        break;

        case 'eb_faq': 
        break;

        case 'eb_guide': 
          $_result = _buidl_helper_get_guide_parents_tree($node_object, $breadcrumb);
          if($_result) {
            $breadcrumb = $_result;
          }
        break;

        case 'eb_ticket': 
        break;         
        
        default:

      }
    }

    return $breadcrumb;
    // Generate the VOCABULARY breadcrumb.
    if ($node_is_fieldable) {

      // Check all taxonomy terms applying to the current page.
      foreach ($node_object->getFields() as $field) {

        if ($field->getSetting('target_type') == 'taxonomy_term') {
          $vocabulary_bundles = $field->getSettings()['handler_settings']['target_bundles'];
          // For now doing for nodes with single vocabulary as reference.
          $vocabulary_machine_name = reset($vocabulary_bundles);
          $entity_type = 'taxonomy_vocabulary';
          $vocabulary_label = $vocabulary_bundles[$vocabulary_machine_name];
          $vocabulary_entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($vocabulary_machine_name);
          $taxonomy_breadcrumb_path = isset($vocabulary_entity->getThirdPartySettings("taxonomy_breadcrumb", "taxonomy_breadcrumb_path")['taxonomy_breadcrumb_path']) ? $vocabulary_entity->getThirdPartySettings("taxonomy_breadcrumb", "taxonomy_breadcrumb_path")['taxonomy_breadcrumb_path'] : '';
          if ($taxonomy_breadcrumb_path) {
            $breadcrumb->addLink(Link::fromTextAndUrl($vocabulary_label, Url::fromUri('base:/' . $taxonomy_breadcrumb_path)));
          }

          // Generate the TERM breadcrumb.
          foreach ($field->referencedEntities() as $term) {
            if ($term->get('taxonomy_breadcrumb_path')->getValue()) {
              $breadcrumb->addLink(Link::fromTextAndUrl($term->get('taxonomy_breadcrumb_path')->getValue()[0]['title'], Url::fromUri($term->get('taxonomy_breadcrumb_path')->getValue()[0]['uri'])));
            }
            else {
              $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]));
            }
          }
        }
      }
    }
    return $breadcrumb;
  }

}
