<?php

use Drupal\views\Views;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Entity\FieldableEntityInterface;

function _buidl_helper_getUserIpAddr(){
  if(!empty($_SERVER['HTTP_CLIENT_IP'])){
      //ip from share internet
      $ip = $_SERVER['HTTP_CLIENT_IP'];
  }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
      //ip pass from proxy
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }else{
      $ip = $_SERVER['REMOTE_ADDR'];
  }
  return $ip;
}


function _buidl_helper_getusers_by_role($role = 'business', $select_array = TRUE){
  $user_storage = \Drupal::service('entity_type.manager')->getStorage('user');
  $ids = $user_storage->getQuery()
    ->condition('status', 1)
    ->condition('roles', $role)
    ->execute();
  $users = $user_storage->loadMultiple($ids);
  if($select_array && $users && count($users)) {
    $output = array();
    foreach($users as $uid => $user) {
      $_value = '';
      $_value .= $user->get('field_ko_name')->value;
      $_value .= ' -- ';
      $_value .= $user->get('field_ko_mobile')->value;
      $_value .= ' -- ';
      $_value .= $user->getEmail();
      $output[$uid] = $_value;
    }
    return $output;
  }
  return $users;
}



function _buidl_helper_goto($path) { 
  $response = new RedirectResponse($path);
  $response->send();
  return;
}

function _buidl_helper_debug(&$data, $title = 'buidl debug', $format = 'pre'){
  $msg = '<pre>' . print_R($data, TRUE) . '</pre>';
  \Drupal::logger($title)->notice($msg);
}


function _buidl_helper_deleteAll($str) {
  //It it's a file.
  if (is_file($str)) {
      //Attempt to delete it.
      return unlink($str);
  }
  //If it's a directory.
  elseif (is_dir($str)) {
      //Get a list of the files in this directory.
      $scan = glob(rtrim($str,'/').'/*');
      //Loop through the list of files.
      foreach($scan as $index=>$path) {
          //Call our recursive function.
          deleteAll($path);
      }
      //Remove the directory itself.
      return @rmdir($str);
  }
}

//Get any entity and term field and return terms as parents as associative array 
function _buidl_helper_get_entity_term(&$entity, $field_name, $delta = 0, $link = TRUE) {
  if($entity && $entity instanceof FieldableEntityInterface && $entity->hasField($field_name)) {
    $terms = $entity->get($field_name)->referencedEntities();
    foreach ($terms as $term) {
      if($term && $term->id()) {
        $storage = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term');
        $parents = $storage->loadAllParents($term->id());
        if($parents && count($parents)) {
          $links = array();
          foreach($parents as &$parent) {
            if($link) {
              $links[] = Link::createFromRoute($parent->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $parent->id()]);
            }
            else {
              $links[] = $parent->getName();
            }
            
          }
          if($links) {
            return array_reverse($links);
          }
        }
        break;
      }
    }
  }
  return FALSE;
}


//

//If breadcrumb = breadcrumb, return breadcrumb, else return array for url alias 

//Rule 1 : cats/cat/parents/title
//Rule 2 : cats/cat/title
//Rule 3 : tags/tag/title

//for URL alias : just array of items or just url 
function _buidl_helper_get_guide_parents_tree(&$guide, $breadcrumb = FALSE) {
  $URL = NULL;
  $breadcrumb_mode = TRUE;
  if(!$breadcrumb) {
    $URL = array();
    $breadcrumb_mode = FALSE;
  }
  $is_project_referred = FALSE;
  if($guide && $guide instanceof FieldableEntityInterface && $guide->hasField('field_eb_project')) {    
    $eb_projects = $guide->get('field_eb_project')->referencedEntities();
    if($eb_projects && count($eb_projects)) {
      foreach($eb_projects as $_eb_project) {
        
        $eb_project_is_fieldable = $_eb_project instanceof FieldableEntityInterface;
        if($eb_project_is_fieldable) {
          $is_project_referred = TRUE;
          $_links = _buidl_helper_get_entity_term($_eb_project, 'field_eb_categories', 0, $breadcrumb_mode);
          
          if($_links) {
            foreach($_links as $_link_) { 
              if($breadcrumb) {
                $breadcrumb->addLink($_link_);
              }
              else {
                $URL[] = $_link_;
              }
            }
            if($breadcrumb) {
              $breadcrumb->addLink(Link::createFromRoute($_eb_project->getTitle(), 'entity.node.canonical', ['node' => $_eb_project->id()]));
            }
            else {
              $URL[] = $_eb_project->getTitle();
            }
            break;
          }
        }
      }
    }
  }

  //Get Guide Stuffs 
  if(!$is_project_referred) {
    $_links = _buidl_helper_get_entity_term($guide, 'field_eb_categories', 0, $breadcrumb_mode);
    if(!$_links) {
      $_links = _buidl_helper_get_entity_term($guide, 'field_eb_tags', 0, $breadcrumb_mode);
    }
    if($_links) {
      foreach($_links as $_link_) { 
        if($breadcrumb) {
          $breadcrumb->addLink($_link_);
        }
        else {
          $URL[] = $_link_;
        }
      }
    }
  }  
  if($breadcrumb) {
    return $breadcrumb;
  }
  return $URL;
}