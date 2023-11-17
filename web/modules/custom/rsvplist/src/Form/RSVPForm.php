<?php

/**
*@file
*A form to collect an email address for RSVP details.
*/

namespace Drupal\rsvpList\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\Core\Messenger\MessengerInterface;

class RSVPForm extends FormBase {
  /**
   * {@inheritdoc}
  */

  public function getFormId() {
    return 'rsvplist_email_form';
  }

  /**
   * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $node = \Drupal::routeMatch()->getParameter('node');

    if( !(is_null($node)) ){
      $nid = $node->id();
    }
    else{
      $nid = 0;
    }

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email address'),
      '#size' => 25,
      '#description' => $this->t('We will send updates to the email address you provide.'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'button',
      '#value' => $this->t('RSVP'),
    ];

    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    return $form;
  }

    /**
   * {@inheritdoc}
  */

public function validateForm(array &$form, FormStateInterface $form_state){
  $value = $form_state->getValue('email');
  if( !(\Drupal::service('email.validator')->isValid($value)) ){
    $form_state->setErrorByName('email',
      $this->t('It appears that %mail is not a valid email. Please try again',
      ['%mail' => $value]));
  }
}

    /**
   * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try{
      //Database Fields
      $uid = \Drupal::currentUser()->id();
      $full_user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
      $nid = $form_state->getValue('nid');
      $email = $form_state->getValue('email');
      $current_time = \Drupal::time()->getRequestTime();

      $query = \Drupal::database()->insert('rsvplist');
      //Getters
      $query->fields([
        'uid',
        'nid',
        'mail',
        'created',
      ]);

      //Setters
      $query->values([
        $uid,
        $nid,
        $email,
        $current_time,
      ]);

      //Execute the query
      $query->execute();
      \Drupal::messenger()->addMessage(
        $this->t('Thank you for your RSVP, you are on the list for the Event!')
      );

    }
    catch(\Exception $e){
      \Drupal::messenger()->addError(
        $this->t('Unable to save RSVP settings at this time. Please try again.')
      );
    }

  /* $submitted_email = $form_state->getValue('email');
   $messenger = $this->messenger();
   $messenger->addMessage($this->t('The form is working! You entered @entry.',
  ['@entry' => $submitted_email]), MessengerInterface::TYPE_WARNING);*/
   /*$this->messenger()->addMessage($this->t("The form is working! You entered @entry.",
   ['@entry' => $submitted_email]));*/
  }
}
