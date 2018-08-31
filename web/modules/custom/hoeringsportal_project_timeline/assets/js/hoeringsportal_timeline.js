import vis from 'vis/dist/vis.js'

Drupal.behaviors.timeline = {
  attach: function (context, settings) {
    console.log(settings);
    // DOM element where the Timeline will be attached
    var container = document.getElementById('visualization')

    // Create a DataSet (allows two way data-binding)
    var items = new vis.DataSet([
      {
        id: 1,
        className: 'abc',
        content: '<div data-container="body" data-toggle="popover" title="Popover title" data-placement="top" data-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus.">item 1</div>',
        start: '2018-04-20'
      },
      {id: 2, content: 'item 2', start: '2018-01-14'},
      {id: 3, content: 'item 3', start: '2018-04-18'},
      {id: 4, content: 'item 4', start: '2018-04-16', end: '2018-04-19'},
      {id: 5, content: 'item 5', start: '2018-04-25'},
      {id: 6, content: 'item 6', start: '2018-04-27', type: 'point'}
    ])

    var newItems = new vis.DataSet({});
    for (var i = 0; i < settings.timelineItems.length; i++) {
      var item = settings.timelineItems[i];
      console.log(item);
      newItems.add({
        id: i,
        className: item.type + ' ' + item.state,
        content: '<div class="timeline-item-inner" data-container="timeline-item-id-' + i + '" data-toggle="popover" title="' + item.title + '" data-placement="top" data-content="' + item.startDate + ' - ' + item.endDate + '">' + item.title + '</div>',
        start: item.startDate
      });
    }

    var now = Date.now()

    // Configuration for the Timeline
    var options = {
      zoomable: false,
      start: now - 31540000000,
      end: now + 31540000000
    }

    // Create a Timeline
    var timeline = new vis.Timeline(container, newItems, options)

    timeline.on('click', function (properties) {
      if(properties.what == 'item') {
        $(function () {
          $('.timeline-item-inner').popover({
            container: 'timeline-item-id-' + properties.item
          })
        })
        console.log('asdf');
      }
      console.log(properties);
    });
  }
}