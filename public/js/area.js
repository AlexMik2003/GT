$(document).ready(function () {

    var count = 1;
    $('#add_contacts').click(function(){
        if($(this).data('clicked',true)){
           $("#contacts").clone().insertBefore("#add_contacts");
            count++;
        }
        $("#hide_contacts").val(count);
    });

    var table = $('#area').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "management/json",

        },
        "columns": [
            {"data": "area"},
            {"data": "address"},
            {"data": "area_contacts"},
            {"data": "device_count"},
            {"data": "comments"},
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
                'targets': 0,
                'searchable': true,
                'orderable': true,
            },
            {
                'targets': [1,3],
                'searchable': false,
                'orderable': true,
            },
            {
                'targets': [2,4],
                'searchable': false,
                'orderable': false,
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
