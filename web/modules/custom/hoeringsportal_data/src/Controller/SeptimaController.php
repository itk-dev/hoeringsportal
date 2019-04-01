<?php

namespace Drupal\hoeringsportal_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Septima controller.
 */
class SeptimaController extends ControllerBase {

  /**
   * Index action.
   */
  public function index(Request $request) {
    $content['menu'] = [
      '#type' => 'container',
    ];

    $ids = array_keys($this->getWidgets());
    foreach ($ids as $id) {
      $url = Url::fromRoute('hoeringsportal_data.septima_controller',
        ['widget' => $id]);
      $url->setOptions([
        'attributes' => [
          'class' => ['btn', 'btn-primary'],
        ],
      ]);
      $content['menu'][$id] = [
        '#markup' => Link::fromTextAndUrl($id, $url)->toString(),
      ];
    }

    $widget = $request->get('widget', reset($ids));

    if (NULL !== $widget) {
      $widgetUrl = $this->getUrlGenerator()->generateFromRoute('hoeringsportal_data.septima_controller_widget', ['id' => $widget]);

      $content['map'] = [
        '#markup' => '<div data-widget-url="' . htmlspecialchars($widgetUrl) . '"></div>',
        '#attached' => [
          'library' => [
            'hoeringsportal_data/septima-widget',
          ],
        ],
      ];
    }

    $content['#cache'] = ['max-age' => 0];

    return $content;
  }

  /**
   * Widget action.
   */
  public function widget(Request $request, $id) {
    $widget = $this->getWidget($id);

    if (NULL === $widget) {
      throw new BadRequestHttpException('Invalid widget: ' . $id);
    }

    return new JsonResponse($widget);
  }

  /**
   * Get a widget by id.
   */
  private function getWidget($id) {
    $widgets = $this->getWidgets();

    return $widgets[$id] ?? NULL;
  }

  /**
   * Get all widgets.
   */
  private function getWidgets() {
    return [
      'project.local-plan' => [
        'map' => [
          'srs' => 'EPSG:25832',
          'maxZoomLevel' => 0,
          'minZoomLevel' => 11,
          'view' => [
            'zoomLevel' => 6,
            'x' => 575159,
            'y' => 6223373,
          ],

          'layer' => [

            [
              'namedlayer' => '#gst',
            ],

            [
              'srs' => 'EPSG:4326',
              'disable' => FALSE,
              'features' => TRUE,
              'features_host' => 'https://rimi-aarhus.cartodb.com/api/v2/sql?'
              . http_build_query([
                'format' => 'geojson',
                'q' => '
SELECT project.*,
       plandata.*
FROM   (SELECT project_id,
               project_title,
               project_teaser,
               project_description,
               project_start,
               project_finish,
               project_url,
               CAST(REGEXP_SPLIT_TO_TABLE(project_local_plan_list, \',\') AS INTEGER) AS planid
        FROM   table_f_ey6qadbbgt2t7plublg
        WHERE  project_geometry_type = \'local_plan\') AS project,
       geoserver_getfeature_1 AS plandata
WHERE  project.planid = plandata.planid
                    ',
              ]),
              'features_dataType' => 'jsonp',
              'features_style' => [
                'namedstyle' => '#011',
              ],

              'template_info' => '
<div class="widget-hoverbox-title">{{project_title}}</div>
<div class="widget-hoverbox-sub">
 <div>{{project_teaser}} <a href="{{project_url}}">Læs mere …</a></div>
 <div>Start: <% print(moment(project_start).format("DD/MM/YYYY"))%></div>
 <div>Slut: <% print(moment(project_finish).format("DD/MM/YYYY"))%></div>
</div>
            ',
              'template_search_title' => '{{project_title}}',
              'template_search_description' => '{{project_description}}',
              'name' => 'Høringer',
              'type' => 'geojson',
              'userfilter' => [
                'project_start' => [
                  'type' => 'daterange',
                  'label' => 'Start (vælg fra og til)',
                  'maxDateColumn' => 'project_start_date',
                  'format' => 'DD/MM/YYYY',
                  // 'min' => 'today-30d',
                  // 'max' => 'today+1y',
                  // 'startDate' => 'today-30d',
                  // 'endDate' => 'today+1y',.
                  'urlParamNames' => [
                    'min' => 'startdato',
                    'max' => 'slutdato',
                  ],
                  'showShortcuts' => TRUE,
                  'shortcuts' => [
                    'next' => [
                      'week',
                      'month',
                      'year',
                    ],
                  ],
                ],
                'project_finish' => [
                  'type' => 'daterange',
                  'label' => 'Slut (vælg fra og til)',
                  'maxDateColumn' => 'project_reply_deadline',
                  'format' => 'DD/MM/YYYY',
                  // 'min' => 'today-30d',
                  // 'max' => 'today+1y',
                  // 'startDate' => 'today-30d',
                  // 'endDate' => 'today+1y',.
                  'urlParamNames' => [
                    'min' => 'startdato',
                    'max' => 'slutdato',
                  ],
                  'showShortcuts' => TRUE,
                  'shortcuts' => [
                    'next' => [
                      0 => 'week',
                      1 => 'month',
                      2 => 'year',
                    ],
                  ],
                ],
              ],
            ],

          ],

          'controls' => [

            [
              'info' => [
                'disable' => FALSE,
                'eventtype' => 'click',
                'type' => 'popup',
              ],
            ],

            [
              'filter' => [
                'disable' => FALSE,
                'detach' => 'filter',
                'combine' => TRUE,
              ],
            ],

            [
              'search' => [
                'displaytext' => 'Søg',
                'minResolution' => 1,
                'features_style' => [
                  'namedstyle' => '#011',
                ],
                'driver' => [
                  [
                    'type' => 'dawa',
                    'options' => [
                      'kommunekode' => '751',
                    ],
                  ],
                  [
                    'type' => 'local',
                    'options' => [
                      'singular' => 'Arrangement',
                      'plural' => 'Arrangementer',
                    ],
                  ],
                ],
              ],
            ],

          ],

        ],
      ],

      'project.point' => [
        'map' => [
          'srs' => 'EPSG:25832',
          'maxZoomLevel' => 0,
          'minZoomLevel' => 11,
          'view' => [
            'zoomLevel' => 6,
            'x' => 575159,
            'y' => 6223373,
          ],

          'layer' => [

            [
              'namedlayer' => '#gst',
            ],

            [
              'srs' => 'EPSG:4326',
              'disable' => FALSE,
              'features' => TRUE,
              'features_host' => 'https://rimi-aarhus.cartodb.com/api/v2/sql?'
              . http_build_query([
                'format' => 'geojson',
                'q' => '
SELECT project.*
  FROM table_f_ey6qadbbgt2t7plublg AS project
 WHERE project_geometry_type = \'point\'
                    ',
              ]),
              'features_dataType' => 'jsonp',
              'features_style' => [
                'namedstyle' => '#011',
              ],

              'template_info' => '
<div class="widget-hoverbox-title">{{project_title}}</div>
<div class="widget-hoverbox-sub">
 <div>{{project_teaser}} <a href="{{project_url}}">Læs mere …</a></div>
 <div>Start: <% print(moment(project_start).format("DD/MM/YYYY"))%></div>
 <div>Slut: <% print(moment(project_finish).format("DD/MM/YYYY"))%></div>
</div>
            ',
              'template_search_title' => '{{project_title}}',
              'template_search_description' => '{{project_description}}',
              'name' => 'Høringer',
              'type' => 'geojson',
              'userfilter' => [
                'project_start' => [
                  'type' => 'daterange',
                  'label' => 'Start (vælg fra og til)',
                  'maxDateColumn' => 'project_start_date',
                  'format' => 'DD/MM/YYYY',
                  // 'min' => 'today-30d',
                  // 'max' => 'today+1y',
                  // 'startDate' => 'today-30d',
                  // 'endDate' => 'today+1y',.
                  'urlParamNames' => [
                    'min' => 'startdato',
                    'max' => 'slutdato',
                  ],
                  'showShortcuts' => TRUE,
                  'shortcuts' => [
                    'next' => [
                      'week',
                      'month',
                      'year',
                    ],
                  ],
                ],
                'project_finish' => [
                  'type' => 'daterange',
                  'label' => 'Slut (vælg fra og til)',
                  'maxDateColumn' => 'project_reply_deadline',
                  'format' => 'DD/MM/YYYY',
                  // 'min' => 'today-30d',
                  // 'max' => 'today+1y',
                  // 'startDate' => 'today-30d',
                  // 'endDate' => 'today+1y',.
                  'urlParamNames' => [
                    'min' => 'startdato',
                    'max' => 'slutdato',
                  ],
                  'showShortcuts' => TRUE,
                  'shortcuts' => [
                    'next' => [
                      0 => 'week',
                      1 => 'month',
                      2 => 'year',
                    ],
                  ],
                ],
              ],
            ],

          ],

          'controls' => [

            [
              'info' => [
                'disable' => FALSE,
                'eventtype' => 'click',
                'type' => 'popup',
              ],
            ],

            [
              'filter' => [
                'disable' => FALSE,
                'detach' => 'filter',
                'combine' => TRUE,
              ],
            ],

            [
              'search' => [
                'displaytext' => 'Søg',
                'minResolution' => 1,
                'features_style' => [
                  'namedstyle' => '#011',
                ],
                'driver' => [
                  [
                    'type' => 'dawa',
                    'options' => [
                      'kommunekode' => '751',
                    ],
                  ],
                  [
                    'type' => 'local',
                    'options' => [
                      'singular' => 'Arrangement',
                      'plural' => 'Arrangementer',
                    ],
                  ],
                ],
              ],
            ],

          ],

        ],
      ],

      'hearing.local-plan' => [
        'map' => [
          'srs' => 'EPSG:25832',
          'maxZoomLevel' => 0,
          'minZoomLevel' => 11,
          'view' => [
            'zoomLevel' => 6,
            'x' => 575159,
            'y' => 6223373,
          ],

          'layer' => [

            [
              'namedlayer' => '#gst',
            ],

            [
              'srs' => 'EPSG:4326',
              'disable' => FALSE,
              'features' => TRUE,
              'features_host' => 'https://rimi-aarhus.cartodb.com/api/v2/sql?'
              . http_build_query([
                'format' => 'geojson',
                'q' => '
SELECT hearing.*,
       plandata.*
FROM   (SELECT hearing_id,
               hearing_title,
               hearing_teaser,
               hearing_description,
               hearing_content_state,
               hearing_type,
               hearing_reply_deadline,
               hearing_start_date,
               hearing_url,
               hearing_project_url,
               COALESCE(hearing_project_title, \'(intet)\') AS hearing_project_title,
               CAST(REGEXP_SPLIT_TO_TABLE(hearing_local_plan_list, \',\') AS INTEGER) AS planid
        FROM   jnt6324g5clm1was_ttepg
        WHERE  hearing_geometry_type = \'local_plan\') AS hearing,
       geoserver_getfeature_1 AS plandata
WHERE  hearing.planid = plandata.planid
                    ',
              ]),
              'features_dataType' => 'jsonp',
              'features_style' => [
                'namedstyle' => '#011',
              ],

              'template_info' => '
<div class="widget-hoverbox-title">{{hearing_title}}</div>
<div class="widget-hoverbox-sub">
 <% if ("(intet)" !== hearing_project_title) { %><div>Project: <a href="{{hearing_project_url}}">{{hearing_project_title}}</a></div><% } %>
 <div>{{hearing_teaser}} <a href="{{hearing_url}}">Læs mere …</a></div>
 <div>Høringsstart: <% print(moment(hearing_start_date).format("DD/MM/YYYY"))%></div>
 <div>Høringsfrist: <% print(moment(hearing_reply_deadline).format("DD/MM/YYYY"))%></div>
 <div>Status: {{hearing_content_state}}</div>
</div>
            ',
              'template_search_title' => '{{hearing_title}}',
              'template_search_description' => '{{hearing_description}}',
              'name' => 'Høringer',
              'type' => 'geojson',
              'userfilter' => [
                'hearing_project_title' => [
                  'type' => 'multiselect',
                  'label' => 'Project',
                  'allDefaultSelected' => TRUE,
                ],
                'hearing_content_state' => [
                  'type' => 'multiselect',
                  'label' => 'Status',
                  'allDefaultSelected' => TRUE,
                ],
                'hearing_start_date' => [
                  'type' => 'daterange',
                  'label' => 'Høringsstart (vælg fra og til)',
                  'maxDateColumn' => 'hearing_start_date',
                  'format' => 'DD/MM/YYYY',
                  // 'min' => 'today-30d',
                  // 'max' => 'today+1y',
                  // 'startDate' => 'today-30d',
                  // 'endDate' => 'today+1y',.
                  'urlParamNames' => [
                    'min' => 'startdato',
                    'max' => 'slutdato',
                  ],
                  'showShortcuts' => TRUE,
                  'shortcuts' => [
                    'next' => [
                      'week',
                      'month',
                      'year',
                    ],
                  ],
                ],
                'hearing_reply_deadline' => [
                  'type' => 'daterange',
                  'label' => 'Høringsfrist (vælg fra og til)',
                  'maxDateColumn' => 'hearing_reply_deadline',
                  'format' => 'DD/MM/YYYY',
                  // 'min' => 'today-30d',
                  // 'max' => 'today+1y',
                  // 'startDate' => 'today-30d',
                  // 'endDate' => 'today+1y',.
                  'urlParamNames' => [
                    'min' => 'startdato',
                    'max' => 'slutdato',
                  ],
                  'showShortcuts' => TRUE,
                  'shortcuts' => [
                    'next' => [
                      0 => 'week',
                      1 => 'month',
                      2 => 'year',
                    ],
                  ],
                ],
              ],
            ],

          ],

          'controls' => [

            [
              'info' => [
                'disable' => FALSE,
                'eventtype' => 'click',
                'type' => 'popup',
              ],
            ],

            [
              'filter' => [
                'disable' => FALSE,
                'detach' => 'filter',
                'combine' => TRUE,
              ],
            ],

            [
              'search' => [
                'displaytext' => 'Søg',
                'minResolution' => 1,
                'features_style' => [
                  'namedstyle' => '#011',
                ],
                'driver' => [
                  [
                    'type' => 'dawa',
                    'options' => [
                      'kommunekode' => '751',
                    ],
                  ],
                  [
                    'type' => 'local',
                    'options' => [
                      'singular' => 'Arrangement',
                      'plural' => 'Arrangementer',
                    ],
                  ],
                ],
              ],
            ],

          ],

        ],
      ],

      'hearing.point' => [
        'map' => [
          'srs' => 'EPSG:25832',
          'maxZoomLevel' => 0,
          'minZoomLevel' => 11,
          'view' => [
            'zoomLevel' => 6,
            'x' => 575159,
            'y' => 6223373,
          ],

          'layer' => [

            [
              'namedlayer' => '#gst',
            ],

            [
              'srs' => 'EPSG:4326',
              'disable' => FALSE,
              'features' => TRUE,
              'features_host' => 'https://rimi-aarhus.cartodb.com/api/v2/sql?'
              . http_build_query([
                'format' => 'geojson',
                'q' => '
SELECT hearing.*
  FROM jnt6324g5clm1was_ttepg AS hearing
 WHERE hearing_geometry_type = \'point\'
                    ',
              ]),
              'features_dataType' => 'jsonp',
              'features_style' => [
                'namedstyle' => '#011',
              ],

              'template_info' => '
<div class="widget-hoverbox-title">{{hearing_title}}</div>
<div class="widget-hoverbox-sub">
 <% if ("(intet)" !== hearing_project_title) { %><div>Project: <a href="{{hearing_project_url}}">{{hearing_project_title}}</a></div><% } %>
 <div>{{hearing_teaser}} <a href="{{hearing_url}}">Læs mere …</a></div>
 <div>Høringsstart: <% print(moment(hearing_start_date).format("DD/MM/YYYY"))%></div>
 <div>Høringsfrist: <% print(moment(hearing_reply_deadline).format("DD/MM/YYYY"))%></div>
 <div>Status: {{hearing_content_state}}</div>
</div>
            ',
              'template_search_title' => '{{hearing_title}}',
              'template_search_description' => '{{hearing_description}}',
              'name' => 'Høringer',
              'type' => 'geojson',
              'userfilter' => [
                'hearing_project_title' => [
                  'type' => 'multiselect',
                  'label' => 'Project',
                  'allDefaultSelected' => TRUE,
                ],
                'hearing_content_state' => [
                  'type' => 'multiselect',
                  'label' => 'Status',
                  'allDefaultSelected' => TRUE,
                ],
                'hearing_start_date' => [
                  'type' => 'daterange',
                  'label' => 'Høringsstart (vælg fra og til)',
                  'maxDateColumn' => 'hearing_start_date',
                  'format' => 'DD/MM/YYYY',
                  // 'min' => 'today-30d',
                  // 'max' => 'today+1y',
                  // 'startDate' => 'today-30d',
                  // 'endDate' => 'today+1y',.
                  'urlParamNames' => [
                    'min' => 'startdato',
                    'max' => 'slutdato',
                  ],
                  'showShortcuts' => TRUE,
                  'shortcuts' => [
                    'next' => [
                      'week',
                      'month',
                      'year',
                    ],
                  ],
                ],
                'hearing_reply_deadline' => [
                  'type' => 'daterange',
                  'label' => 'Høringsfrist (vælg fra og til)',
                  'maxDateColumn' => 'hearing_reply_deadline',
                  'format' => 'DD/MM/YYYY',
                  // 'min' => 'today-30d',
                  // 'max' => 'today+1y',
                  // 'startDate' => 'today-30d',
                  // 'endDate' => 'today+1y',.
                  'urlParamNames' => [
                    'min' => 'startdato',
                    'max' => 'slutdato',
                  ],
                  'showShortcuts' => TRUE,
                  'shortcuts' => [
                    'next' => [
                      0 => 'week',
                      1 => 'month',
                      2 => 'year',
                    ],
                  ],
                ],
              ],
            ],

          ],

          'controls' => [

            [
              'info' => [
                'disable' => FALSE,
                'eventtype' => 'click',
                'type' => 'popup',
              ],
            ],

            [
              'filter' => [
                'disable' => FALSE,
                'detach' => 'filter',
                'combine' => TRUE,
              ],
            ],

            [
              'search' => [
                'displaytext' => 'Søg',
                'minResolution' => 1,
                'features_style' => [
                  'namedstyle' => '#011',
                ],
                'driver' => [
                  [
                    'type' => 'dawa',
                    'options' => [
                      'kommunekode' => '751',
                    ],
                  ],
                  [
                    'type' => 'local',
                    'options' => [
                      'singular' => 'Arrangement',
                      'plural' => 'Arrangementer',
                    ],
                  ],
                ],
              ],
            ],

          ],

        ],
      ],
    ];
  }

}
