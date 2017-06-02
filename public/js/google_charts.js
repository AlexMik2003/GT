$(document).ready(function () {

    var url = $(location).attr('href');
    var route = document.location.origin+"/";

    var href = url.split(route);
    switch (href[1])
    {
        case "map/info":
            google.charts.load('current', {packages: ['map']});
            google.charts.setOnLoadCallback(drawMap);
                function drawMap () {
                    var data = new google.visualization.DataTable();
                    data.addColumn('number', 'LATITUDE', 'Latitude');
                    data.addColumn('number', 'LONGITUDE', 'Longitude');
                    data.addColumn('string', 'AREA', 'Area');
                    var uri = $(location).attr('href');

                    $.getJSON( uri+"/json", function( item ) {
                        $.each(item, function (key, val) {
                            data.addRows([
                                [val['lat'],val['long'],val['area']],
                            ]);
                        });

                        var options = {
                            mapType: 'styledMap',
                            zoomLevel: 12,
                            showTooltip: true,
                            showInfoWindow: true,
                            useMapTypeControl: true,
                            maps: {
                                // Your custom mapTypeId holding custom map styles.
                                styledMap: {
                                    name: 'Styled Map', // This name will be displayed in the map type control.
                                    styles: [
                                        {featureType: 'poi.attraction',
                                            stylers: [{color: '#fce8b2'}]
                                        },
                                        {featureType: 'road.highway',
                                            stylers: [{hue: '#0277bd'}, {saturation: -50}]
                                        },
                                        {featureType: 'road.highway',
                                            elementType: 'labels.icon',
                                            stylers: [{hue: '#000'}, {saturation: 100}, {lightness: 50}]
                                        },
                                        {featureType: 'landscape',
                                            stylers: [{hue: '#259b24'}, {saturation: 10}, {lightness: -22}]
                                        }
                                    ]}}
                        };

                        var map = new google.visualization.Map(document.getElementById('map'));
                        map.draw(data, options);
                    });
                }
            break;
        case "dashboard":
            google.charts.load('current', {packages: ['table']});
            google.charts.setOnLoadCallback(drawTable);
        function drawTable() {
            var cssClassNames = {
                'headerRow': 'darkblue-font large-font bold-font',
                'tableRow': '',
                'oddTableRow': '',
                'selectedTableRow': 'large-font',
               };

            var options = {width: '100%', height: '100%','allowHtml': true, 'cssClassNames': cssClassNames};

            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Host');
            data.addColumn('number', 'Avg');
            data.addColumn('number', 'Cur');

            var uri = $(location).attr('href');

            $.getJSON( uri+"/json", function( item ) {
                $.each(item, function (key, val) {
                    data.addRows([
                        [val['host'],val['avg'],val['cur']],
                    ]);
                });

                var Info = new google.visualization.Table(document.getElementById('Info'));
                Info.draw(data, options);
           });
        }
        break;
    }

});
