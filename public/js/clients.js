$(document).ready(function () {

    var count = 1;
    $('#add_net').click(function(){
        if($(this).data('clicked',true)){
            $("#client_network").clone().insertBefore("#add_net");
            count++;
        }
        $("#hide_clients_net").val(count);
    });

    var table = $('#clients').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "management/json",

        },
        "columns": [
            {"data": "act_number"},
            {"data": "client_name"},
            {"data": "client_it_name"},
            {"data": "client_address"},
            {"data": "client_network"},
            {"data": "client_manager"},
            {"data": "client_sla"},
            {"data": "id"},
        ],

        'columnDefs': [{
            'targets': 7,
            'searchable': false,
            'orderable': false,
            'className': 'dt-body-center',
            'render': function (data, type, full, meta) {
                return '<a href="edit/'+data+'" class="btn btn-warning navbar-btn" role="button" id="act_btn">Edit</a>&emsp;' +
                    '<a href="delete/'+data+'" class="btn btn-danger navbar-btn" role="button" id="act_btn">Delete</a>';

            }
        },

            {
                'targets': [0,1,2,3,5],
                'searchable': true,
                'orderable': true,
            },
            {
                'targets': [4,6],
                'searchable': false,
                'orderable': true,
            }],

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

});

