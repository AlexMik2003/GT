$(document).ready(function () {

    var count = 1;
    $('#add_peer_net').click(function(){
        if($(this).data('clicked',true)){
            $("#peer_network").clone().insertBefore("#add_peer_net");
            count++;
        }
        $("#hide_peers_net").val(count);
    });

    var table = $('#peers').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "management/json",

        },
        "columns": [
            {"data": "peer_name"},
            {"data": "peer_it_name"},
            {"data": "peer_network"},
            {"data": "peer_as"},
            {"data": "peer_vlan"},
            {"data": "id"},
        ],

        'columnDefs': [{
            'targets': 5,
            'searchable': false,
            'orderable': false,
            'className': 'dt-body-center',
            'render': function (data, type, full, meta) {
                return '<a href="edit/'+data+'" class="btn btn-warning navbar-btn" role="button" id="act_btn">Edit</a>&emsp;' +
                    '<a href="delete/'+data+'" class="btn btn-danger navbar-btn" role="button" id="act_btn">Delete</a>';

            }
        },

            {
                'targets': [0,1,3],
                'searchable': true,
                'orderable': true,
            },
            {
                'targets': [2,4],
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

