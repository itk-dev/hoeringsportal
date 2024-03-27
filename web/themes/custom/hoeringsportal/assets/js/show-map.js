/**
 * @file
 * Add map display config.
 */

import Map from 'ol/Map.js'
import View from 'ol/View.js'
import { defaults as defaultControls } from 'ol/control.js'
import GeoJSON from 'ol/format/GeoJSON.js'
import { Tile as TileLayer, Vector as VectorLayer } from 'ol/layer.js'
import { OSM, Vector as VectorSource } from 'ol/source.js'
import { Circle as CircleStyle, Fill, Stroke, Style, Icon } from 'ol/style.js'
import { fromLonLat } from 'ol/proj.js'
import { defaults as defaultInteractions } from 'ol/interaction.js'

require('./ol.css')

const mapElement = document.getElementById('map')
if (mapElement !== null && mapElement.dataset.geojson) {
  const image = new Icon({
    src: '/themes/custom/hoeringsportal/static/images/flag.png',
    anchor: [0.5, 1]
  })

  const styles = {
    Point: new Style({
      image
    }),
    LineString: new Style({
      stroke: new Stroke({
        color: 'rgba(0, 132, 134, 1)',
        width: 1
      })
    }),
    MultiLineString: new Style({
      stroke: new Stroke({
        color: 'rgba(0, 132, 134, 1)',
        width: 1
      })
    }),
    MultiPoint: new Style({
      image
    }),
    MultiPolygon: new Style({
      stroke: new Stroke({
        color: 'rgba(0, 132, 134, 1)',
        width: 1
      }),
      fill: new Fill({
        color: 'rgba(0, 132, 134, 1)'
      })
    }),
    Polygon: new Style({
      stroke: new Stroke({
        color: 'rgba(0, 132, 134, 1)',
        lineDash: [4],
        width: 3
      }),
      fill: new Fill({
        color: 'rgba(0, 132, 134, .1)'
      })
    }),
    GeometryCollection: new Style({
      stroke: new Stroke({
        color: 'rgba(0, 132, 134, 1)',
        width: 2
      }),
      fill: new Fill({
        color: 'rgba(0, 132, 134, 1)'
      }),
      image: new CircleStyle({
        radius: 10,
        fill: null,
        stroke: new Stroke({
          color: 'rgba(0, 132, 134, 1)'
        })
      })
    }),
    Circle: new Style({
      stroke: new Stroke({
        color: 'rgba(0, 132, 134, 1)',
        width: 4
      }),
      fill: new Fill({
        color: 'rgba(0, 132, 134, 1)'
      })
    })
  }

  const styleFunction = function (feature) {
    return styles[feature.getGeometry().getType()]
  }

  const geojsonObject = mapElement.dataset.geojson

  const vectorSource = new VectorSource({
    features: (new GeoJSON({})).readFeatures(geojsonObject, {
      featureProjection: 'EPSG:3857'
    })
  })

  const vectorLayer = new VectorLayer({
    source: vectorSource,
    style: styleFunction
  })

  const map = new Map({
    layers: [
      new TileLayer({
        source: new OSM()
      }),
      vectorLayer
    ],
    target: 'map',
    controls: defaultControls({
      attributionOptions: {
        collapsible: false
      }
    }),
    view: new View({
      center: fromLonLat([10.1890, 56.1619]),
      zoom: 13
    }),
    interactions: defaultInteractions({ mouseWheelZoom: false })
  })

  const source = vectorLayer.getSource()
  map.getView().fit(source.getExtent(), map.getSize())

  // If the map contains only a single point we have reached max zoom level (which
  // defaults to 28). Zoom out a bit.
  if (map.getView().getZoom() > 15) {
    map.getView().setZoom(15)
  }
}
