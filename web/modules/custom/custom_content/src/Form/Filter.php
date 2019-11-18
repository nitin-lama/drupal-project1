<?php

namespace Drupal\custom_content\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

class Filter extends FormBase {

  public function getFormId() {
      return 'form_filter';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

      //getting External project Domain from configuration
      $config = $this->config('temphost.adminsettings');
      $external_url = $config->get('host');

      global $base_url;

      // Request to get Content Type of ELX
      $client = \Drupal::httpClient();
      $request = $client->get("$external_url/api/get/content_type");
      $cont_res = json_decode($request->getBody());

      // Adding ANY & None in options
      $data['0'] = '- None -';
      $data['all'] = '- Any -';
      foreach ($cont_res as $key => $value) {
        $data[$key] = $value;
      }

      $form['ctype'] = [
        '#title' => t('Content Type'),
        '#type' => 'select',
        '#options' => $data,
      ];

      // Request to get Translation Language List of ELX
      $client = \Drupal::httpClient();
      $request = $client->get("$external_url/api/v1/languageList");
      $lan_res = json_decode($request->getBody());

      // Adding ANY & None in option
      $lang['0'] = '- None -';
      $lang['all'] = '- Any -';
      foreach ($lan_res as $key => $value) {
        $lang[$value->languageCode] = $value->languageName;
      }

      $form['language'] = [
        '#title' => t('Language'),
        '#type' => 'select',
        '#options' => $lang,
      ];

      // Request to get Market List of ELX
      $client = \Drupal::httpClient();
      $request = $client->get("$external_url/market/export");
      $mark_res = json_decode($request->getBody());

      // Adding ANY & None in option
      $market['0'] = '- None -';
      $market['all'] = '- Any -';
      foreach($mark_res as $key => $value) {
          $market[$value->tid] = $value->name;
      }

        $form['market'] = [
        '#title' => t('Market'),
        '#type' => 'select',
        '#options' => $market,
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Filter'),
        '#button_type' => 'primary',
      ];

       if ($form_state->isRebuilding()) {
          $c_type = $_POST['ctype'];
          $lang_code = $_POST['language'];
          $location = $_POST['market'];

          // Request to get content on the basis of filter.
          $client = \Drupal::httpClient();
          $request = $client->get(sprintf("$external_url/content/export?type=%s&lang=%s&market=%s",$c_type,$lang_code,$location));
          $response3 = json_decode($request->getBody());

          foreach($response3 as $key => $value) {
            $nid = $value->nid;

            //Edit Link
            $url1 = Url::fromUri("$base_url/node/$nid/edit");
            $edit = \Drupal::l(t('<span class="button">Edit</span>'), $url1);

            // Translate Link
            $url2 = Url::fromUri("$base_url/node/$nid/translate");
            $translate = \Drupal::l(t('<span class="button">Translate</span>'), $url2);

            //Delete Link
            $url3 = Url::fromUri("$base_url/node/$nid/delete");
            $delete = \Drupal::l(t('<span class="button">Delete</span>'), $url3);

            $options[] = ['title' => $value->title,
                        'content_type' => $value->type,
                        'author' => $value->uid,
                        'status' => $value->status,
                        'updated' => $value->created,
                        'edit' => $edit,
                        'translate' => $translate,
                        'delete' => $delete,
                        ];
          }

          $header = [
          'title' => t('Title'),
          'content_type' => t('Content type'),
          'author' => t('Author'),
          'status' => t('Status'),
          'updated' => t('Updated'),
          'edit' => t('Edit'),
          'translate' => t('Translate'),
          'delete' => t('Delete'),
          ];

          $form['Mytable'] = [
            '#type' => 'tableselect',
            '#header' => $header,
            '#options' => $options,
            '#empty' => t('No content available'),
            '#prefix' => '<div class = "Mytable" > <br>',
            '#suffix' => '</div>'
          ];
      }

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $content = $form_state->getValue('ctype');
    $language = $form_state->getValue('language');
    $market = $form_state->getValue('market');
    if($content == '0' && $language == '0' && $market == '0') {
      $form_state->setErrorByName('ctype', t('Please select any one filter.'));
    }
}


  public function submitForm(array &$form, FormStateInterface $form_state) {
      $form_state->setRebuild();
  }
}
?>
