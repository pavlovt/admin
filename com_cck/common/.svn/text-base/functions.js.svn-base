function loadContent(loadTo, loadFrom, param) {
	//if (param.length) param = $.parseJSON(param);
	//$j('#content').height($j('#content').height());
	$j('#' + loadTo).empty();
	$j.loader({ className:"blue-with-image-2", content:'' });
	$j.post(loadFrom, param, function(data) {
		$j('#' + loadTo).html(data);
		$j.loader('close');
	});
}

function unserialize(data){
    var data = data.split("&");
    var s = {};
    $j.map(data, function(n){ n=n.split('='); s[n[0]]=n[1] });
    return s;
}

//submit data by post or get method
//ie: submit('t.form.php', {p:"ququ!"}, "get");
function submit(url, params, method) {
	method = method || "post"; // Set method to post by default, if not specified.
	if (params && typeof params != 'object') { // we have params = 'q=5&p=1' so convert them to object -> {"q":5,"p":1}
		params = unserialize(params);
	}

var form = $j('<form>');
if (url.length > 0) form.attr('action', url);
form.attr('method', method);
//if(newWindow){ form.attr('target', '_blank'); }

var addParam = function(paramName, paramValue){
   var input = $j('<input type="hidden">');
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

function validateForm(myForm) {
	myForm.validate({
         errorClass: "errormessage",
         //onkeyup: false,
         errorClass: 'error',
         validClass: 'valid',
         success: function(error)
         {
            // Use a mini timeout to make sure the tooltip is rendred before hiding it
            setTimeout(function() {
               myForm.find('.valid').qtip('destroy');
            }, 1);
         },
         errorPlacement: function(error, element)
         {
            // Set positioning based on the elements position in the form
            //var corners = ['right center', 'left center'],
              // flipIt = $(element).parents('span.right').length > 0,
               position = {
                  my: 'left center',
                  at: 'right center'
               };

            // Apply the tooltip only if it isn't valid
            $j(element).filter(':not(.valid)').qtip({
               overwrite: false,
               content: error,
               position: position,
               show: {
                  event: false,
                  ready: true
               },
               hide: false,
               style: {
                  classes: 'ui-tooltip-shadow ui-tooltip-rounded ui-tooltip-red' // Make it red... the classic error colour!
               }
            });
         }
   })

}