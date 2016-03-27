$( document ).ready(function() {
    $("p.sitename, p.sitename + p").click(function() { location.href="/"; });
    
    $("form.status-form-confirm input[type='submit']").click(function() {
        var $button = $(this);

        $("form.status-form-confirm div.status-form-confirmtext").each(function() {
            $(this).hide('fast', function() {
            if ($(this).closest('form')[0] !== $button.closest('form')[0])
                    $(this).closest('form').removeClass('status-form-getconfirm');
            $(this).remove();
            })
        });
        $("form.status-form-confirm input[type='button']").each(function () { this.type = 'submit' });
        $("form.status-form-confirm input[type='submit']").attr("disabled", false);
        
        $button.attr("disabled", true);
        
        var $parent = $button.closest('form');
        $parent.find("input[type='submit']").each(function () { this.type = 'button' });
        
        $parent.addClass('status-form-getconfirm');
        var $newdiv1 = $( "<div class=\"status-form-confirmtext\" />" );

        $newdiv1.append($( "<span />" ).text($button.attr('data-confirm-text')));
        
        var $extrainputattr = $button.attr('data-confirm-input');
        var $extrainput = null;
        if ($extrainputattr)
        {
            if ($extrainputattr == 'password')
                $extrainputattr = 'text';
            $extrainput = $( "<input type=\"" + $extrainputattr + "\" class=\"inputbox status-form-extrainput\" />" );
            if ($extrainput[0].willValidate)
                $extrainput.prop("required", true);;
            $newdiv1.append($extrainput);
        }
        
        var $newsubmit = $( "<input type=\"submit\" class=\"button1\" />" );
        $newsubmit.val($button.attr('data-confirm-button'));
        $newsubmit.attr( "class", "button2" );
        $parent.off("submit");
        $parent.on("submit", function() {
            var formData = $parent.serializeArray();
            if ($extrainput)
                formData.push({ name: $button[0].name, value: $extrainput[0].value });
            else
                formData.push({ name: $button[0].name, value: $button[0].value });

            $.ajax({
                type     : $parent.attr('method'),
                cache    : false,
                url      : $parent.attr('action'),
                data     : formData,
                success  : function(data) {
                    // Create Response
                    if (data.Title)
                        $newdiv1.empty().append( $( "<h4 />" ).text(data.Title));
                    else
                        $newdiv1.empty();
                    
                    $newdiv1.append( $( "<span />" ).text( data.Message ));
                    
                    $newdiv1.addClass('status-form-status-' + data.Status.toLowerCase());
                    
                    var $closebutton = $( "<input type=\"submit\" class=\"button1\" />" );
                    if ($button.is('[data-confirm-refresh]') && data.Status.toLowerCase() == "ok")
                        $closebutton.val($button.attr('data-confirm-refreshbutton'));
                    else
                        $closebutton.val($button.attr('data-confirm-close'));
                    $closebutton.attr( "class", "button2" );
                    $closebutton.click(function() {
                        if ($button.is('[data-confirm-refresh]') && data.Status.toLowerCase() == "ok")
                            $newdiv1.hide('fast').delay(750).queue(function(next) { $parent.removeClass('status-form-getconfirm'); $newdiv1.remove(); location.reload(true); next(); });
                        else
                            $newdiv1.hide('fast', function() { $newdiv1.remove(); $parent.removeClass('status-form-getconfirm'); });
                        $button.attr("disabled", false);
                        $parent.find("input[type='button']").each(function () { this.type = 'submit' });
                        return false;
                    });
                    $newdiv1.append($closebutton);
                },
                error   : function(err) {
                    if (err.responseJSON && err.responseJSON.Status)
                        return this.success(err.responseJSON);
                    else if (err.responseJSON && err.responseJSON.message)
                        return this.success({ "Status":"ERR", "Message": err.responseJSON.message, "Title":err.statusText });
                    else
                        return this.success({ "Status":"ERR", "Message": err.responseText, "Title":err.statusText });
                },
            });
            return false;
        })
        $newdiv1.append($newsubmit);

        var $newcancel = $( "<input type=\"button\" class=\"button1\" />" );
        $newcancel.attr( "class", "button2" );
        $newcancel.addClass('status-cancel-button');
        $newcancel.val($button.attr('data-confirm-cancel'));
        $newcancel.click(function() {
            $newdiv1.hide('fast', function() { $newdiv1.remove(); $parent.removeClass('status-form-getconfirm'); });
            $parent.find("input[type='button']").each(function () { this.type = 'submit' });
            $parent.find("input[type='submit']").attr("disabled", false);

            return false;
        });
        $newdiv1.append($newcancel);
        
        $newdiv1.hide();
        $parent.append($newdiv1)
        $newdiv1.show('fast');

        return false;
    });
});