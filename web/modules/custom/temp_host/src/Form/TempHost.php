<?php

namespace Drupal\temp_host\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class TempHost extends ConfigFormBase {
  protected function getEditableConfigNames() {
    return ['temphost.adminsettings'];
  }

  public function getFormId() {
    return 'temp_host';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('temphost.adminsettings');

    $form['host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom Host URL'),
      '#description' => $this->t('Enter custom host URL'),
      '#default_value' => $config->get('host'),
    ];
    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('temphost.adminsettings')
    ->set('host', $form_state->getValue('host'))
    ->save();
  }
}
