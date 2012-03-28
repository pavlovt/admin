jQuery ->
  $('.chosen').chosen()
  $('.datepicker').datepicker({
    "dateFormat": "dd.mm.yy"
  })
  $('.button').button()

  # show confirmation dialog before deleting
  $('a.delete').bind 'click', (е) ->
    answer = confirm("Потвърдете изтриването")
    return false if not answer