<?php

namespace Drupal\hoeringsportal_data\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Form for statistics.
 */
class StatisticsForm extends FormBase {
  public const REPORT = 'report';

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
    $startDate = new \DateTimeImmutable('first day of January');
    if (isset($values['start_date'])) {
      try {
        $startDate = new \DateTimeImmutable($values['start_date']);
      }
      catch (\Exception $exception) {
      }
    }
    $endDate = $startDate->modify('last day of December');
    if (isset($values['end_date'])) {
      try {
        $endDate = new \DateTimeImmutable($values['end_date']);
      }
      catch (\Exception $exception) {
      }
    }

    $reports = $form_state->getBuildInfo()['args'][0]['reports'] ?? NULL;
    if (empty($reports)) {
      return [
        '#theme' => 'status_messages',
        '#message_list' => [
          'warning' => [
            $this->t('There are no reports available.'),
          ],
        ],
      ];
    }

    $form['#method'] = 'get';

    $options = array_map(static fn (array $info) => $info['title'], $reports);

    $form[self::REPORT] = [
      '#type' => 'select',
      '#title' => $this->t('Report'),
      '#options' => $options,
      '#default_value' => $values[self::REPORT] ?? NULL,
      '#required' => TRUE,
    ];

    $form['start_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Start date'),
      '#default_value' => $startDate->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
      '#required' => TRUE,
    ];

    $form['end_date'] = [
      '#type' => 'date',
      '#title' => $this->t('End date'),
      '#default_value' => $endDate->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
      '#required' => TRUE,
    ];

    $form['show'] = [
      '#type' => 'inline_template',
      '#template' => '<button class="button button--primary" name="show" value="show">' . $this->t('Show result') . '</button>',
    ];

    // This is a hack!
    if ('show' === ($values['show'] ?? NULL)) {
      $form['export'] = [
        '#type' => 'inline_template',
        '#template' => implode('', [
          '<button class="button" name="export" value="csv">' . $this->t('Export result (CSV)') . '</button>',
          '<button class="button" name="export" value="xlsx">' . $this->t('Export result (XLSX)') . '</button>',
        ]),
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
