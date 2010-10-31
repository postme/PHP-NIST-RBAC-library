$(document).ready(function(){
	
  // load additional javascript libraries
  $.ajaxSetup({async: false, cache: true});
  $.getScript(path + "/js/activebar2.js");
  $.getScript(path + "/js/jquery.tablesorter.min.js");
  $.getScript(path + "/js/jquery.metadata-min.js");
  $.getScript(path + "/js/jquery.corner.js");
  $.getScript(path + "/js/jquery.DOMWindow.js");
  $.getScript(path + "/js/custom.js");
  $.ajaxSetup({async: true, cache: false});
  
});