<?php

namespace Drupal\hoeringsportal_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Maps controller.
 */
class MapsController extends ControllerBase {

  /**
   * Index action.
   */
  public function index(Request $request) {
    $widget = $request->get('widget', 'alles-zusammen');

    if (NULL !== $widget) {
      $widgetUrl = $this->getUrlGenerator()->generateFromRoute('hoeringsportal_data.maps_controller_widget', ['id' => $widget]);

      $content['map'] = [
        '#markup' => '<div class="maps-septima" data-widget-height="100%" data-widget-url="' . htmlspecialchars($widgetUrl) . '"></div>',
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

    if ('dump-data-url' === $request->get('action')) {
      foreach ($widget['map']['layer'] as $layer) {
        if (isset($layer['features_host'])) {
          $url = $layer['features_host'];
          echo '<a href="' . $url . '">' . $url . '</a>', PHP_EOL;
        }
      }
      echo PHP_EOL;
      exit;
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

    $widgets['alles-zusammen'] = [
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

SELECT 1 AS index,
       \'area\' AS type,
       project_id,
       project_title,
       project_teaser,
       project_description,
       project_start,
       project_finish,
       project_url,
       the_geom
  FROM (SELECT project_id,
               project_title,
               project_teaser,
               project_description,
               project_start,
               project_finish,
               project_url,
               CAST(REGEXP_SPLIT_TO_TABLE(project_area_list, \',\') AS INTEGER) AS area_id
          FROM table_f_ey6qadbbgt2t7plublg
         WHERE project_area_list != \'\') AS project,
       (SELECT CAST(nr AS INTEGER) AS id,
               the_geom
          FROM jay3dzjvfb4f6ftegaypcg) AS area
 WHERE project.area_id = area.id

UNION

SELECT 2 AS index,
       \'local_plan\' AS type,
       project_id,
       project_title,
       project_teaser,
       project_description,
       project_start,
       project_finish,
       project_url,
       the_geom
  FROM (SELECT project_id,
               project_title,
               project_teaser,
               project_description,
               project_start,
               project_finish,
               project_url,
               CAST(REGEXP_SPLIT_TO_TABLE(project_local_plan_list, \',\') AS INTEGER) AS planid
          FROM table_f_ey6qadbbgt2t7plublg
         WHERE project_local_plan_list != \'\') AS project,
       geoserver_getfeature_1 AS plandata
 WHERE project.planid = plandata.planid

UNION

SELECT 3 AS index,
       \'point\' AS type,
       project_id,
       project_title,
       project_teaser,
       project_description,
       project_start,
       project_finish,
       project_url,
       the_geom
  FROM table_f_ey6qadbbgt2t7plublg AS project
 WHERE project_geometry_type = \'point\'

                    ',
            ]),
            'features_dataType' => 'jsonp',
            'features_style' => [
              'namedstyle' => '#001',
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
            'layername' => 'project_local_plan',
            'name' => 'Initiativer',
            'type' => 'geojson',
            '//userfilter' => [
              'project_start' => [
                'type' => 'daterange',
                'label' => 'Start (vælg fra og til)',
                'maxDateColumn' => 'project_start_date',
                'format' => 'DD/MM/YYYY',
                'min' => 'today-30d',
                'max' => 'today+1y',
                'startDate' => 'today-30d',
                'endDate' => 'today+1y',
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

          [
            'srs' => 'EPSG:4326',
            'disable' => FALSE,
            'features' => TRUE,
            'features_host' => 'https://rimi-aarhus.cartodb.com/api/v2/sql?'
            . http_build_query([
              'format' => 'geojson',
              'q' => '

SELECT 1 AS index,
       \'area\' AS type,
       hearing_id,
       hearing_title,
       hearing_teaser,
       hearing_description,
       hearing_content_state,
       hearing_type,
       hearing_reply_deadline,
       hearing_start_date,
       hearing_url,
       hearing_project_url,
       hearing_replies_count,
       hearing_replies_url,
       hearing_project_title,
       the_geom
  FROM (SELECT hearing_id,
               hearing_title,
               hearing_teaser,
               hearing_description,
               hearing_content_state,
               hearing_type,
               hearing_reply_deadline,
               hearing_start_date,
               hearing_url,
               hearing_project_url,
               hearing_replies_count,
               hearing_replies_url,
               COALESCE(hearing_project_title, \'(intet)\') AS hearing_project_title,
               CAST(REGEXP_SPLIT_TO_TABLE(hearing_area_list, \',\') AS INTEGER) AS area_id
          FROM jnt6324g5clm1was_ttepg
         WHERE hearing_area_list != \'\') AS hearing,
       (SELECT CAST(nr AS INTEGER) AS id,
               the_geom
          FROM jay3dzjvfb4f6ftegaypcg) AS area
 WHERE hearing.area_id = area.id

UNION

SELECT 2 AS index,
       \'plan_data\' AS type,
       hearing_id,
       hearing_title,
       hearing_teaser,
       hearing_description,
       hearing_content_state,
       hearing_type,
       hearing_reply_deadline,
       hearing_start_date,
       hearing_url,
       hearing_project_url,
       hearing_replies_count,
       hearing_replies_url,
       hearing_project_title,
       the_geom
  FROM (SELECT hearing_id,
               hearing_title,
               hearing_teaser,
               hearing_description,
               hearing_content_state,
               hearing_type,
               hearing_reply_deadline,
               hearing_start_date,
               hearing_url,
               hearing_project_url,
               hearing_replies_count,
               hearing_replies_url,
               COALESCE(hearing_project_title, \'(intet)\') AS hearing_project_title,
               CAST(REGEXP_SPLIT_TO_TABLE(hearing_local_plan_list, \',\') AS INTEGER) AS planid
          FROM jnt6324g5clm1was_ttepg
         WHERE hearing_local_plan_list != \'\') AS hearing,
       geoserver_getfeature_1 AS plandata
 WHERE hearing.planid = plandata.planid

UNION

SELECT 3 AS index,
       \'point\' AS type,
       hearing_id,
       hearing_title,
       hearing_teaser,
       hearing_description,
       hearing_content_state,
       hearing_type,
       hearing_reply_deadline,
       hearing_start_date,
       hearing_url,
       hearing_project_url,
       hearing_replies_count,
       hearing_replies_url,
       hearing_project_title,
       the_geom
  FROM jnt6324g5clm1was_ttepg AS hearing
 WHERE hearing_geometry_type = \'point\'
                    ',

            ]),
            'features_dataType' => 'jsonp',
            'features_style' => [
              'namedstyle' => '#007',
            ],

            'template_info' => '
<div class="widget-hoverbox-title">{{hearing_title}}</div>
<div class="widget-hoverbox-sub">
 <% if ("(intet)" !== hearing_project_title) { %><div>Project: <a href="{{hearing_project_url}}">{{hearing_project_title}}</a></div><% } %>
 <div>{{hearing_teaser}} <a href="{{hearing_url}}">Læs mere …</a></div>
 <div>#replies: <a href="{{hearing_replies_url}}">{{hearing_replies_count}}</a></div>
 <div>Høringsstart: <% print(moment(hearing_start_date).format("DD/MM/YYYY"))%></div>
 <div>Høringsfrist: <% print(moment(hearing_reply_deadline).format("DD/MM/YYYY"))%></div>
 <div>Status: {{hearing_content_state}}</div>
</div>
            ',
            'template_search_title' => '{{hearing_title}}',
            'template_search_description' => '{{hearing_description}}',
            'layername' => 'hearing_point',
            'name' => 'Høringer',
            'type' => 'geojson',
            'userfilter' => [
              'hearing_start_date' => [
                'type' => 'daterange',
                'label' => 'Høring i gang (vælg fra og til)',
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
            ],
          ],

        ],

        'controls' => [
          [
            'fullscreen' => [
              'disable' => FALSE,
            ],

            'info' => [
              'disable' => FALSE,
              'eventtype' => 'click',
              'type' => 'popup',
            ],

            'filter' => [
              'disable' => FALSE,
              'detach' => 'filter',
              'combine' => TRUE,
            ],

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

            'layerswitch' => [
              'disable' => FALSE,
              'layers' => 'all',
              'selectAll' => FALSE,
              'showbuttons' => TRUE,
              'showlegend' => TRUE,
            ],
          ],
        ],

      ],
    ];

    $widgets['filter14'] = [
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
            'features_host' => 'https://mida.cartodb.com/api/v2/sql?'
            . http_build_query([
              'format' => 'GeoJSON',
              'q' => '

with arr_plads (arr_id, plads_id)
 as
(SELECT cartodb_id as arr_id, regexp_split_to_table(pladser, E\',\') AS plads_id FROM arrangementer),
 arr_geometry (arr_id, geom_tot) as (select ap.arr_id, st_union(p.the_geom) as geom_tot from arr_plads ap join arrangementspladser p on (btrim(ap.plads_id) = p.name) group by ap.arr_id) select a.cartodb_id, a.category_title, a.pladser, a.ref, a.arrangementets_starttidspunkt as startdato, a.arrangementets_sluttidspunkt as slutdato, g.geom_tot as the_geom from arrangementer a join arr_geometry g on (a.cartodb_id = g.arr_id)',
            ]),
            'features_dataType' => 'jsonp',
            'features_style' => [
              'namedstyle' => '#010',
            ],
            'template_info' => '<div class="widget-hoverbox-title">xxxxx [[category_title]]</div><div class=\'widget-hoverbox-sub\'><div>[[pladser]]</div><div>Start => <% print(moment(startdato).format("DD/MM-YYYY"))%></div><div>Slut => <% print(moment(slutdato).format("DD/MM-YYYY"))%></div><div>Pladse => [[pladser]]</div></div>',
            'template_search_title' => '[[pladser]]',
            'template_search_description' => '[[category_title]] ',
            'name' => 'Arrangementer',
            'type' => 'geojson',
            'userfilter' => [
              'startdato' => [
                'type' => 'daterange',
                'label' => 'Hvornår (vælg fra og til)',
                'maxDateColumn' => 'slutdato',
                'format' => 'DD/MM/YYYY',
                'min' => 'today-5d',
                'max' => 'today+5d',
                'startDate' => 'today-5d',
                'endDate' => 'today+5d',
                'urlParamNames' => [
                  'min' => 'startdato',
                  'max' => 'slutdato',
                ],
                'showShortcuts' => TRUE,
                'shortcuts' => ['next' => ['week', 'month', 'year']],
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
    ];

    $widgets['hmm'] = [
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
SELECT * FROM jnt6324g5clm1was_ttepg WHERE hearing_geometry_type = \'point\'

                    ',
            ]),
            'features_dataType' => 'jsonp',
            'features_style' => [
              'namedstyle' => '#011',
            ],

            'template_info' => '
<div class="widget-hoverbox-title">{{hearing_title}}</div>
<div class="widget-hoverbox-sub">
 <div>{{hearing_teaser}} <a href="{{hearing_url}}">Læs mere …</a></div>
 <div>Start: <% print(moment(hearing_start_date).format("DD/MM/YYYY"))%></div>
 <div>Slut: <% print(moment(hearing_reply_deadline).format("DD/MM/YYYY"))%></div>
</div>
            ',
            // 'template_search_title' => '{{project_title}}',
            // 'template_search_description' => '{{project_description}}',
            // 'layername' => 'project_local_plan',.
            'name' => 'Høring',
            'type' => 'geojson',
            'userfilter' => [
              'hearing_start_date' => [
                'type' => 'daterange',
                'label' => 'Start (vælg fra og til)',
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
    ];

    $widgets['project.local-plan'] = [
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
       plandata.the_geom
FROM   (SELECT project_id,
               project_title,
               project_teaser,
               project_description,
               project_start,
               project_finish,
               project_url,
               CAST(REGEXP_SPLIT_TO_TABLE(project_local_plan_list, \',\') AS INTEGER) AS planid
        FROM   table_f_ey6qadbbgt2t7plublg
        WHERE  project_local_plan_list != \'\') AS project,
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
            'layername' => 'project_local_plan',
            'name' => 'Initiativ (lokalplan)',
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
    ];

    $widgets['project.point'] = [
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
SELECT
project_id,
               project_title,
               project_teaser,
               project_description,
               project_start,
               project_finish,
               project_url,
               NULL AS planid,
               the_geom
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
            'layername' => 'project_point',
            'name' => 'Initiativ (adresse)',
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
    ];

    $widgets['hearing.local-plan'] = [
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
               hearing_replies_count,
               hearing_replies_url,
               COALESCE(hearing_project_title, \'(intet)\') AS hearing_project_title,
               CAST(REGEXP_SPLIT_TO_TABLE(hearing_local_plan_list, \',\') AS INTEGER) AS planid
        FROM   jnt6324g5clm1was_ttepg
        WHERE  hearing_local_plan_list != \'\') AS hearing,
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
 <div>#replies: <a href="{{hearing_replies_url}}">{{hearing_replies_count}}</a></div>
 <div>Høringsstart: <% print(moment(hearing_start_date).format("DD/MM/YYYY"))%></div>
 <div>Høringsfrist: <% print(moment(hearing_reply_deadline).format("DD/MM/YYYY"))%></div>
 <div>Status: {{hearing_content_state}}</div>
</div>
            ',
            'template_search_title' => '{{hearing_title}}',
            'template_search_description' => '{{hearing_description}}',
            'layername' => 'hearing_local_plan',
            'name' => 'Høringer (lokalplan)',
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
    ];

    $widgets['hearing.point'] = [
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
 <div>#replies: <a href="{{hearing_replies_url}}">{{hearing_replies_count}}</a></div>
 <div>Høringsstart: <% print(moment(hearing_start_date).format("DD/MM/YYYY"))%></div>
 <div>Høringsfrist: <% print(moment(hearing_reply_deadline).format("DD/MM/YYYY"))%></div>
 <div>Status: {{hearing_content_state}}</div>
</div>
            ',
            'template_search_title' => '{{hearing_title}}',
            'template_search_description' => '{{hearing_description}}',
            'layername' => 'hearing_point',
            'name' => 'Høringer (adresse)',
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
    ];

    $widgets['hearing.area'] = [
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
       area.the_geom
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
               hearing_replies_count,
               hearing_replies_url,
               COALESCE(hearing_project_title, \'(intet)\') AS hearing_project_title,
               CAST(REGEXP_SPLIT_TO_TABLE(hearing_area_list, \',\') AS INTEGER) AS area_id
          FROM jnt6324g5clm1was_ttepg
         WHERE hearing_area_list != \'\') AS hearing,
       (SELECT CAST(nr AS INTEGER) AS id,
               the_geom
        FROM jay3dzjvfb4f6ftegaypcg) AS area
WHERE  hearing.area_id = area.id
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
 <div>#replies: <a href="{{hearing_replies_url}}">{{hearing_replies_count}}</a></div>
 <div>Høringsstart: <% print(moment(hearing_start_date).format("DD/MM/YYYY"))%></div>
 <div>Høringsfrist: <% print(moment(hearing_reply_deadline).format("DD/MM/YYYY"))%></div>
 <div>Status: {{hearing_content_state}}</div>
</div>
            ',
            'template_search_title' => '{{hearing_title}}',
            'template_search_description' => '{{hearing_description}}',
            'layername' => 'hearing_point',
            'name' => 'Høringer (adresse)',
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
    ];

    /*
    $widgets['hearing.local-plan+point'] = $widgets['hearing.local-plan'];
    $map = &$widgets['hearing.local-plan+point']['map'];
    $map['layer'][] = $widgets['hearing.point']['map']['layer'][1];
    unset($map['layer'][1]['userfilter']);
    unset($map['layer'][2]['userfilter']);

    $map['layer'][1]['userfilter'] = [
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
    ];

    // $map['controls'] = [];
    $map['controls'][] = [
    'layerswitch' => [
    'disable' => false,
    'layers' => 'all',
    'selectAll' => false,
    'showbuttons' => true,
    'showlegend' => true,
    ],
    ];
    //*/

    return $widgets;
  }

}
