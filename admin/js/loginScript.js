$('input').focus(function(e) {
    var name = $(this).attr('name');
    if ($('label[for="'+name+'"]').length > 0) { 
        $('label[for="'+name+'"]').addClass('active');
    }});
    
    $('input').blur(function(e) {
    var name = $(this).attr('name');
    if ($('label[for="'+name+'"]').length > 0) { 
        $('label[for="'+name+'"]').removeClass('active');
    }});