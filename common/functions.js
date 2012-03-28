function loadContent(loadTo, loadFrom, param) {
  //if (param.length) param = $.parseJSON(param);
  //$('#content').height($('#content').height());
  $('#' + loadTo).empty();
  $.loader({ className:"blue-with-image-2", content:'' });
  $.post(loadFrom, param, function(data) {
    $('#' + loadTo).html(data);
    $.loader('close');
  });
}

function unserialize(data){
    var data = data.split("&");
    var s = {};
    $.map(data, function(n){ n=n.split('='); s[n[0]]=n[1] });
    return s;
}

//submit data by post or get method
//ie: submit('t.form.php', {p:"ququ!"}, "get");
function submit(url, params, method) {
  method = method || "post"; // Set method to post by default, if not specified.
  if (params && typeof params != 'object') { // we have params = 'q=5&p=1' so convert them to object -> {"q":5,"p":1}
    params = unserialize(params);
  }

  if (url.length == 0)
    url = window.location.href;

  var form = $('<form>');
  if (url.length > 0) form.attr('action', url);
  form.attr('method', method);
  //if(newWindow){ form.attr('target', '_blank'); }
  
  var addParam = function(paramName, paramValue){
     var input = $('<input type="hidden">');
     input.attr({ 'name':   paramName,
                  'value':  paramValue });
     form.append(input);
  };
  
  // Params is an Array.
  if(params instanceof Array){
     for(var i=0; i<params.length; i++){
         addParam(i, params[i]);
     }
  }
  
  // Params is an Associative array or Object.
  if(params instanceof Object){
     for(var key in params){
         addParam(key, params[key]);
     }
  }
  //console.log(form); return;
  // Submit the form, then remove it from the page
  form.appendTo(document.body);
  form.submit();
  //form.remove();
}

function reloadPage () {
	// use this instead or reload(); reload() will cause some browsers such as Chrome to reload cached content
	// don't use window.location.href = window.location.href - we want remove the parameters
	//window.location.href = window.location.protocol + "//" + window.location.host + window.location.pathname;
	window.location.href = window.location.href;

}