<?php

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;

// Taken from : http://kevinquillen.com/drupal/2017/02/16/manually-add-breadcrumb-links-in-drupal-8

/*
* Implements hook_system_breadcrumb_alter().
*
* Append node title to breadcrumb for certain content types and views that are 
* not in the menu.
*
* @param \Drupal\Core\Breadcrumb\Breadcrumb $breadcrumb
* @param \Drupal\Core\Routing\RouteMatchInterface $route_match
* @param array $context
*/





function dis_buidl_helper_breadcrumbs_system_breadcrumb_alter(Breadcrumb &$breadcrumb, RouteMatchInterface $route_match, array $context) {
 if ($breadcrumb && !\Drupal::service('router.admin_context')->isAdminRoute()) {
   $node = \Drupal::request()->get('node');
   $types = ['article', 'faq', 'product'];
   $request = \Drupal::request();

   // if the node is a type with no menu placement, attach a breadcrumb
   if ($node && in_array($node->bundle(), $types)) {
     $breadcrumb->addLink(Link::createFromRoute($node->getTitle(), '<nolink>'));
     $breadcrumb->addCacheTags(['node:' . $node->id()]);
   }

   // if the page is a view, attach a breadcrumb
   if (preg_match('/view\./', $route_match->getRouteName())) {
     $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
     $view_id = \Drupal::routeMatch()->getMasterRouteMatch()->getParameter('view_id');
     $breadcrumb->addLink(Link::createFromRoute($title, '<nolink>'));
     $breadcrumb->addCacheTags(['config:views.view.' . $view_id]);
     $breadcrumb->addCacheContexts(['route.name']);
   }
 }
}

function _buidl_helper_breadcrumbs_nodes(&$node) { 

}
