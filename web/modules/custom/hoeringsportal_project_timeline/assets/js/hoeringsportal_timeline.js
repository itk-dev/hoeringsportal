import vis from 'vis/dist/vis.js'

var settings = drupalSettings;
var timeline = settings.timeline || {};
var items = timeline.items || [];
var options = timeline.options || {};

// Partial implementation of https://www.php.net/manual/en/function.date.php
const formatDate = (date) => {
  const format = options.date_format || null
  if (null === format) {
    return date.toLocaleDateString(document.documentElement.lang || "da")
  }

  // Left pad with zeros (with a maximum of 4 zeros).
  const pad = (value, length = 2) => ('0000'+value.toString()).slice(-length)

  // Split into format character with optional escaping backslash.
  const characters = [...format.matchAll(/\\?./g)].map(m => m[0])
  const values = characters.map(character => {
    let value = character
    switch (character) {
    case 'd': return pad(date.getDate())
    case 'j': return date.getDate()
    case 'm': return pad(date.getMonth()+1)
    case 'n': return date.getMonth()+1
    case 'Y': return date.getFullYear()
    case 'y': return date.getFullYear().toString().slice(-2)
    case 'G': return date.getHours()
    case 'H': return pad(date.getHours())
    case 'i': return pad(date.getMinutes())
    case 's': return pad(date.getSeconds())
    default:
      return '\\' === character[0] ? character[1] : character
    }
  })

  return values.join('')
}

// DOM element where the Timeline will be attached.
var container = document.getElementById('visualization')
var dotColors = {};
// Create a new dataset.
var newItems = new vis.DataSet({});
for (var i = 0; i < items.length; i++) {
  var item = items[i];
  var date = new Date(item.startDate);
  // @todo modify display of date in popup.
  // var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  var popoverLabel = item.label;
  var buttonLink = '';
  var description = '<div>' + item.description + '</div></br>';

  if (item.description == null) {
    description = ''
  }

  // Only show link if a node exists or link is set.
  if (item.type === 'hearing') {
    buttonLink = '<a class="btn-sm btn-primary" href="/node/' + item.nid + '">Gå til høring</a>'
  }
  else if (item.type === 'meeting') {
    buttonLink = '<a class="btn-sm btn-primary" href="/node/' + item.nid + '">Gå til borgermøde</a>'
  }
  else if (item.link) {
    buttonLink = '<a class="btn-sm btn-primary" href="' + item.link + '">Læs mere</a>'
  }


      if (item.description == null){
        description = ''
      }

      // Only show link if a node exists or link is set.
      if (item.nid > 0){
        buttonLink = '<a class="btn-sm btn-primary" href="/node/' + item.nid + '">Gå til høring</a>'
      }
      else if(item.link) {
        buttonLink = '<a class="btn-sm btn-primary" href="' + item.link + '">Læs mere</a>'
      }

  // Setup the popover content.
  var formatted_content =
    '<div class="popover-label">' + popoverLabel + '</div>' +
    '<div class="popover-date">' + formatDate(date) + '</div>' +
    '<h6 class="popover-title">' + item.title + '</h6>' +
    description +
    buttonLink;

      newItems.add({
        id: i,
        style: 'background-color:' + item.color,
        className: item.type + ' ' + item.state,
        content: $('<div/>', {
          'class': 'timeline-item-inner is-' + item.type + ' is-' + item.state,
          'data-toggle': 'timeline-popover',
          'data-html': true,
          'data-placement': 'top',
          'data-content':  formatted_content
        }).html('<i></i><div>' + item.title + '</div>').prop('outerHTML'),
        start: item.startDate
      });

      // Prepare coloring for dots and lines.
      dotColors[item.type] = item.color;
    }

    var now = Date.now()

    // Configuration for the Timeline
    var options = {
      zoomable: false,
      start: now - 31540000000,
      end: now + 31540000000
    }

    // Create a timeline
    var timeline = new vis.Timeline(container, newItems, options)

    $(function () {
      $(container).popover({
        // Selector makes dynamic elements wok as well.
        // https://github.com/twbs/bootstrap/issues/4215
        selector: '[data-toggle="timeline-popover"]'
      });
    })

    // Modify the colors of dot and line.
    Object.keys(dotColors).forEach(function(key) {
      $('.vis-dot.' + key).css('border-color', dotColors[key]);
      $('.vis-line.' + key).css('border-color', dotColors[key]);
    });

    // Hide on timeline drag.
    timeline.on('rangechange', function (properties) {
      $('[data-toggle="timeline-popover"]').popover('hide')
    });

    // Hide on timeline drag.
    timeline.on('click', function (properties) {
      $('[data-toggle="timeline-popover"]').popover('hide')
    });

    // Add "Now" to line.
    var lbl = document.createElement("label");
    lbl.innerHTML = "NU"
    timeline.currentTime.bar.appendChild(lbl);
