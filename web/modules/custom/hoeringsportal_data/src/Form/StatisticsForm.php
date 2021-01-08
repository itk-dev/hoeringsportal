<?php

namespace Drupal\hoeringsportal_data\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for statistics.
 */
class StatisticsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hoeringsportal_data_export';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $values = $this->getRequest()->query->all();
    $startDate = new \DateTimeImmutable('first day of january');
    if (isset($values['start_date'])) {
      try {
        $startDate = new \DateTimeImmutable($values['start_date']);
      }
      catch (\Exception $exception) {
      }
    }
    $endDate = $startDate->add(new \DateInterval('P1Y'));
    if (isset($values['end_date'])) {
      try {
        $endDate = new \DateTimeImmutable($values['end_date']);
      }
      catch (\Exception $exception) {
      }
    }
    $contentType = $values['content_type'] ?? 'hearing';

    $form['#method'] = 'get';

    $options = [
      'hearing' => (string) $this->t('Hearing'),
      'public_meeting' => (string) $this->t('Public meeting'),
    ];
    asort($options);

    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#options' => $options,
      '#default_value' => $contentType,
      '#required' => TRUE,
    ];

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Start date'),
      '#default_value' => $startDate->format('Y-m-d'),
      '#required' => TRUE,
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('End date'),
      '#default_value' => $endDate->format('Y-m-d'),
      '#required' => TRUE,
    ];

    $form['show'] = [
      '#type' => 'inline_template',
      '#template' => '<button class="btn btn-success" name="show" value="show">' . $this->t('Show result') . '</button>',
    ];

    // This is a hack!
    if ('show' === $values['show'] ?? NULL) {
      $form['export'] = [
        '#type' => 'inline_template',
        '#template' => '<button class="btn" name="export" value="csv">' . $this->t('Export result (CSV)') . '</button>',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
