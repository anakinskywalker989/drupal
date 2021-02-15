<?php

namespace Drupal\lfi_contact_support\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManager;

/**
 * Class MailCountryMappingForm.
 */
class MailCountryMappingForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $mail_country_mapping = $this->entity;
    $countries = CountryManager::getStandardList();
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CC team'),
      '#maxlength' => 255,
      '#default_value' => $mail_country_mapping->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $mail_country_mapping->id(),
      '#machine_name' => [
        'exists' => '\Drupal\lfi_contact_support\Entity\MailCountryMapping::load',
      ],
      '#disabled' => !$mail_country_mapping->isNew(),
    ];

    /**$form['cc_team'] = [
      '#type' => 'select',
      '#multiple' => FALSE,
      '#options' => [
        'emea' => $this->t('EMEA'),
        'latam' => $this->t('LATAM'),
        'row' => $this->t('ROW'),
        'usca' => $this->t('US/CA'),
      ],
      '#title' => $this->t('Choose CC team'),
      '#required' => TRUE,
    ];*/

    $form['countries'] = [
      '#type' => 'select2',
      '#multiple' => TRUE,
      '#options' => $countries,
      '#title' => $this->t('Select countries'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => 'Email address',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $mail_country_mapping = $this->entity;
    $status = $mail_country_mapping->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Mail country mapping.', [
          '%label' => $mail_country_mapping->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Mail country mapping.', [
          '%label' => $mail_country_mapping->label(),
        ]));
    }
    $form_state->setRedirectUrl($mail_country_mapping->toUrl('collection'));
  }

}
