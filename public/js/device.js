$(document).ready(function () {

    var route = document.location.origin;

    var table = $('#device').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "management/json",

        },
        "columns": [
            {"data": "name"},
            {"data": "ip"},
            {"data": "area_name"},
            {"data": "status"},
            {"data": "id"},
        ],

        'columnDefs': [
            {
            'targets': 4,
            'searchable': false,
            'orderable': false,
            'className': 'dt-body-center',
            'render': function (data, type, full, meta) {
                return '<input type="checkbox" name="device_id[]" value="' + $('<div/>').text(data).html() + '">';


                }
            },

            {
                'targets': 0,
                'searchable': true,
                'orderable': true,
                'className': 'dt-body-center',
                'render': function (data, type, full, meta) {
                    return  '<a href="'+route+'/deviceInform/'+data["id"]+'/summary" class="device_link">'+data["name"]+'</a>';
                }
            },
            {
                'targets': [1,2,3],
                'searchable': false,
                'orderable': false,
            },
        ],

        "language": {
            "lengthMenu": "Display _MENU_ records per page",
            "zeroRecords": "Nothing found - sorry",
            "info": "Showing page _PAGE_ of _PAGES_",
            "infoEmpty": "No records available",
            "infoFiltered": "(filtered from _MAX_ total records)",
            "search": "Search:",
            "paginate": {
                "previous": "Previous page",
                "next": "Next page"
            }
        }

    });

    // Handle click on "Select all" control
    $('#check_all_device').on('click', function(){
        // Get all rows with search applied
        var rows = table.rows({ 'search': 'applied' }).nodes();
        // Check/uncheck checkboxes for all rows in the table
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

    // Handle click on checkbox to set state of "Select all" control
    $('#device tbody').on('change', 'input[type="checkbox"]', function(){
        // If checkbox is not checked
        if(!this.checked){
            var el = $('#check_all_device').get(0);
            // If "Select all" control is checked and has 'indeterminate' property
            if(el && el.checked && ('indeterminate' in el)){
                // Set visual state of "Select all" control
                // as 'indeterminate'
                el.indeterminate = true;
            }
        }
    });

    // Handle form submission event
    $('#device_form').on('submit', function(e){
        var form = this;

        // Iterate over all checkboxes in the table
        table.$('input[type="checkbox"]').each(function(){
            // If checkbox doesn't exist in DOM
            if(!$.contains(document, this)){
                // If checkbox is checked
                if(this.checked){
                    // Create a hidden element
                    $(form).append(
                        $('<input>')
                            .attr('type', 'hidden')
                            .attr('name', this.name)
                            .val(this.value)
                    );
                }
            }
        });
    });

    $.getJSON( "summary/snmp", function( data ) {
        var table =  $(".deviceInform>tbody");
        $.each(data['system'],function (key,value) {
            table.append("<tr><td><strong>"+key.toUpperCase()+"</strong></td><td>"+value+"</td></tr>");
        });

        google.charts.load('current', {'packages':['gauge']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var gauge = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Memory', 0],
                ['CPU', 0]
            ]);

            var options = {
                width: 450, height: 170,
                redFrom: 90, redTo: 100,
                yellowFrom:75, yellowTo: 90,
                minorTicks: 5
            };

            var chart = new google.visualization.Gauge(document.getElementById('gauge'));

            chart.draw(gauge, options);

            setInterval(function() {
                gauge.setValue(0, 1, data['gauge']['memory']);
                chart.draw(gauge, options);
            }, 5000);
            setInterval(function() {
                gauge.setValue(1, 1, data['gauge']['cpu']);
                chart.draw(gauge, options);
            }, 5000);
        }
    });

});

