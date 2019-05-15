<?php

//File for form alters and forms

use Drupal\Component\Utility\Html;

function buidl_helper_form_contact_message_eb_create_buidl_guide_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {

  $entity_id = 0;
  $entity_type = 'node';
  if(isset($_REQUEST['id']) && intval($_REQUEST['id'])) {
    $entity_id = $form['field_eb_entity_id']['widget'][0]['value']['#default_value'] = intval($_REQUEST['id']);
  }
  if(isset($_REQUEST['type']) && trim($_REQUEST['type'])) {
    $entity_type = $form['field_eb_entity_type']['widget'][0]['value']['#default_value'] = trim($_REQUEST['type']);
  }  

  if($entity_id && $entity_type) {
    $form['eb_preselect_preview'] = array(
      '#type' => 'markup',
      '#markup' => _buidl_helper_get_entity_view($entity_id, $entity_type),
      '#title' => t('Contact for'),
    );
    unset($form['field_eb_project_name']);
  }
  

  $form['#cache'] = ['max-age' => 0];
  
  if(isset($_SERVER['HTTP_REFERER']) && trim($_SERVER['HTTP_REFERER'])) {
    $form['field_eb_referer_url']['widget'][0]['value']['#default_value'] = trim($_SERVER['HTTP_REFERER']);
  }
}

