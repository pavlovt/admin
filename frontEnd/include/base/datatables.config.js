$(document).ready(function() {
   $('#dataTable').dataTable( {
      "bRetrieve": true,
      "bStateSave": false,
      "sPaginationType": "full_numbers",
      "iDisplayLength": 50,
      "bLengthChange": false,
      "sDom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
      "aaSorting": [[ 0, "asc" ]]
   } );
});
