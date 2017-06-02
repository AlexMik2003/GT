$(document).ready(function () {

    var route = document.location.origin;

     var table = $('#ip').DataTable({
     "processing": true,
     "serverSide": true,
     "ajax": {
     "url": "management/json",

     },
     "columns": [
         {"data": "network"},
         {"data": "name"},
         {"data": "class"},
         {"data": "type"},
         {"data": "hosts"},
         {"data": "free_used"},
         {"data": "id"},
     ],

     'columnDefs': [
         {
         'targets': 6,
         'searchable': false,
         'orderable': false,
         'className': 'dt-body-center',
         'render': function (data, type, full, meta) {
         return '<input type="checkbox" name="network_id[]" value="' + $('<div/>').text(data).html() + '">';
         }
     },

         {
         'targets': 0,
         'searchable': false,
         'orderable': true,
         'className': 'dt-body-center',
         'render': function (data, type, full, meta) {
         return  '<a href="'+route+'/networkInform/'+data["id"]+'/summary" class="network_link">'+data["network"]+'</a>';
         }
     },
         {
         'targets': 1,
         'searchable': true,
         'orderable': true,
         },
         {
             'targets': [2,3,4,5],
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
    $('#check_all_net').on('click', function(){
        // Get all rows with search applied
        var rows = table.rows({ 'search': 'applied' }).nodes();
        // Check/uncheck checkboxes for all rows in the table
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

    // Handle click on checkbox to set state of "Select all" control
    $('#ip tbody').on('change', 'input[type="checkbox"]', function(){
        // If checkbox is not checked
        if(!this.checked){
            var el = $('#check_all_net').get(0);
            // If "Select all" control is checked and has 'indeterminate' property
            if(el && el.checked && ('indeterminate' in el)){
                // Set visual state of "Select all" control
                // as 'indeterminate'
                el.indeterminate = true;
            }
        }
    });

    // Handle form submission event
    $('#network_form').on('submit', function(e){
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
});
