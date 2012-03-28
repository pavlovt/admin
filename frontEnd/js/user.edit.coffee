jQuery ->
  # set the value == 1 if the checkbox is checked
  $('input[type=checkbox]').bind 'click', ->
    if $(this).attr('checked')
      $(this).val(1)
    else
      $(this).val(0)

  # check checkboxes with value == 1
  for i in $('input[type=checkbox]')
    # +$(i).val() converts the val to number because in CS == is like === in JS
    $(i).attr('checked', true) if +$(i).val() == 1