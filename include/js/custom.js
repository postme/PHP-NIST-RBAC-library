$(document).ready(function(){
	
	  initBinding();
        
    // Ajax call   
    $('select.autosubmit').change(function() {
        $("#ajax").val("1");
        var data = $("#rbac_form").serialize();
        $.ajax({
            type: "POST",
            url: "index.php",
            dataType: "html",
            data: data,
            success: function(html) {
                $('.storycontent').empty().append(html);
                initBinding();
		        }
            
        });
        return false;  
    });
    
    // Bit of cosmetic fluff, rounded corners for the menu boxes
    $('#box1').corner();
    $('#box2').corner();
    $('#box3').corner();
    $('#box4').corner();
        
    // When clicking a checkbox on a page that doesn't have a label with
    // classname=dropdown add the class=strikethrough to the td's that are
    // adjacent to the td containing the checkbox
    $('.check_me').click(function() {
    	  if ($(".dropdown").length == 0 ) {
            if ($(this).attr('checked') == false) {
                $(this).parent().siblings().removeClass('strikethrough');
            }
            if ($(this).attr('checked') == true) {
                $(this).parent().siblings().addClass('strikethrough');
            }
        }        
    });
    
    // When the reset button of the form is clicked remove all strikethroughs
    $('#resetbutton').click(function() {
        $('.check_me').each(
            function() {
                $(this).parent().siblings().removeClass('strikethrough');
            }
        );
    });
    
    // Check all checkboxes of a form when the checkbox with
    // id=select_deselect is clicked
    $('#select_deselect').click(function() {
        var checked_status = this.checked;
        $('.check_me').each(function() {
            this.checked = checked_status;
        });
    });
           
    $('.modal').openDOMWindow({ 
        height: 300,
        width: 500,
        eventType: 'click', 
        windowSourceID: '#dialog', 
        windowPadding: 0,
        borderSize: 2, 
        borderColor: '#fff',
        draggable: 1,
        windowBGColor: '#eee',
        overlay: 1,
        overlayColor: '#000',
        overlayOpacity: '85'
    }); 
    
    $("select").each(function() {
        // Keep track of the selected option.
        var selectedValue = $(this).val();
        // Sort all the options by text. I could easily sort these by val.
        $(this).html($("option", $(this)).sort(function(a, b) {
            return a.text == b.text ? 0 : a.text < b.text ? -1 : 1
        }));
        // Select one option.
        $(this).val(selectedValue);
    });
    
            
});

function initBinding() { 

    $('.stripeMe').tablesorter({
        widgets: ['zebra']
    });
    $('.stripeMe tr')
    .mouseover(function() {
        $(this).addClass('over');
    })
    .mouseout(function() {
        $(this).removeClass('over');
    });
    
    $('#box1').corner();
    $('#box2').corner();
    $('#box3').corner();
    $('#box4').corner();
    
    $('.check_me').click(function() {
    	  if ($(".dropdown").length == 0 ) {
            if ($(this).attr('checked') == false) {
                $(this).parent().siblings().removeClass('strikethrough');
            }
            if ($(this).attr('checked') == true) {
                $(this).parent().siblings().addClass('strikethrough');
            }
        }        
    });
    
    $('select.autosubmit').change(function() {
        $("#ajax").val("1");
        var data = $("#rbac_form").serialize();
        $.ajax({
            type: "POST",
            url: "index.php",
            dataType: "html",
            data: data,
            success: function(html) {
                $('.storycontent').empty().append(html);
            	  initBinding();
		        }
        });
        return false;  
    });
    
    $('#resetbutton').click(function() {
        $('.check_me').each(
            function() {
                $(this).parent().siblings().removeClass('strikethrough');
            }
        );
    });
        
    $('#select_deselect').click(function() {
        var checked_status = this.checked;
        $('.check_me').each(function() {
            this.checked = checked_status;
        });
    });
    
    $('.modal').openDOMWindow({ 
        height: 300,
        width: 500,
        eventType: 'click', 
        windowSourceID: '#dialog', 
        windowPadding: 0,
        borderSize: 2, 
        borderColor: '#fff',
        draggable: 1, 
        windowBGColor: '#eee',
        overlay: 1,
        overlayColor: '#000',
        overlayOpacity: '85'
    }); 

    $("select").each(function() {
        // Keep track of the selected option.
        var selectedValue = $(this).val();
        // Sort all the options by text. I could easily sort these by val.
        $(this).html($("option", $(this)).sort(function(a, b) {
            return a.text == b.text ? 0 : a.text < b.text ? -1 : 1
        }));
        // Select one option.
        $(this).val(selectedValue);
    });

}