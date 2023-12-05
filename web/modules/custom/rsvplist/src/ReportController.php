<?php
/**
 * @file
 * Provide site administrators with a list of all the RSVP List signups
 * so they know who is attending their events.
 */

namespace Drupal\rsvplist\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

class ReportController extends ControllerBase{
  /**
   * Gets and returns all RSVPs for all nodes.
   * These are returned as an associative array, with each row
   * containing the username, the node title, and email of RSVP.
   *
   * @return array/null
  */
  protected function load(){
    try{
      $database = \Drupal::database();
      $select_query = $database->select('rsvplist', 'r');
      $select_query->join('users_field_data', 'u', 'r.uid = u.uid');
      $select_query->join('node_field_data', 'n', 'r.nid = n.nid');

      $select_query->addField('u', 'name', 'username');
      $select_query->addField('n', 'title');
      $select_query->addField('r', 'mail');

      $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }
    catch(\Exception $e){
      \Drupal::messenger()->addStatus(
        $this->t('Unable to access the database at this time. Please try again later.')
      );
      return NULL;
    }
  }

  public function report(){
    $content = [];

    $content['message'] = [
      '#markup' => $this->t('Below is a list of all Event RSVPs including username,
      email address and the name of th event they will be attending.'),
    ];

    $headers = [
      $this->t('Username'),
      $this->t('Event'),
      $this->t('Email'),
    ];

    $table_rows = $this->load();

    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $table_rows,
      '#empty' => $this->t('No entries available.'),
    ];

    $content['#cache']['max-age'] = 0;

    return $content;
  }
}
