jQuery(document).ready( function($) {
    /*---------Settings page------*/
    if($('.wpmb_settings').length){
        //init dynamic tabs
        $('.wrap .nav-tab-wrapper a').click(function(event){
            event.preventDefault();
            // Limit effect to the container element.
            var context = $(this).closest('.nav-tab-wrapper').parent();
            $('.nav-tab-wrapper a', context).removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.nav-tab-panel', context).hide();
            $( $(this).attr('href'), context ).show();
        });

        //settings page - general settings tab
        $('.wrap #general-options #post-body-content form').submit(function(event){
            /* stop form from submitting normally */
            event.preventDefault();
            var form = $(this);
            if(form.hasClass('reset_all')){ //ask user about remove
                var conf = confirm("Are you sure you want to delete all data (that will delete all links , referrals, blocked ips/domains ...)?");
                if(conf == false){
                    return false;
                }
            }
            $.ajax({
                type: "POST",
                url: ajaxurl ,
                data: $(this).serializeArray(),
                success: function(response){
                    var obj = $.parseJSON(response);
                    //display notify
                    $('.wpmb_settings #general-options').prepend('<div style="width:99%; padding: 5px;display:none;" class="'+ (obj.status?'updated':'error') +' below-h2 notice">'+ obj.message +'</div>').find('.notice').show('slow').delay(5000).hide('slow',function(){$(this).remove();});

                    //remove data from pages
                    if(form.hasClass('reset_all') && obj.status){
                        //blocked ip page
                        $('#block-ip #post-body-content .inside').empty();
                        //blocked domain page
                        $('#block-domain #post-body-content .inside').empty();
                        //blocked referrer page
                        $('#block-referrer #post-body-content .inside').empty();
                    }
                }
            });
            return false;
        });

        //settings page - blocked ip tab
        $('.wrap #block-ip .sidebar form').submit(function(event){
            /* stop form from submitting normally */
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: ajaxurl ,
                data: $(this).serializeArray(),
                success: function(response){
                    var obj = $.parseJSON(response);
                    //display notify
                    $('.wpmb_settings #block-ip').prepend('<div style="width:99%; padding: 5px;display:none;" class="'+ (obj.status?'updated':'error') +' below-h2 notice">'+ obj.message +'</div>').find('.notice').show('slow').delay(5000).hide('slow',function(){$(this).remove();});

                    //add to sidebar
                    if(obj.status == true){
                        var html = '';
                        html += '<form id="ip-'+ obj.id+'" method="POST" action="">';
                            html +='<table class="widefat">';
                                html +='<tr valign="top">';
                                    html +='<td class="row-title">';
                                        html +='<label>'+ obj.ip +'</label>';
                                    html +='</td>';
                                    html +='<td align="right">';
                                        html +='<input class="button-primary" type="submit" name="submit" value="Remove">';
                                    html +='</td>';
                                html +='</tr>';
                            html +='</table>';
                            html +='<input type="hidden" name="id" value="'+ obj.id +'">';
                            html +='<input type="hidden" name="action" value="delete_block_ip">';
                            html +='<input type="hidden" name="ajax" value="true">';
                        html += '</form>';
                        $('.wrap #block-ip #post-body-content .inside').append(html).find('#ip-'+ obj.id).submit(remove_block_ip);
                    }
                }
            });
            return false;
        });

        //settings page - blocked ip tab (remove ip from block list)
        function remove_block_ip(event){
            /* stop form from submitting normally */
            var form = $(this);
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: ajaxurl ,
                data: $(this).serializeArray(),
                success: function(response){
                    var obj = $.parseJSON(response);
                    //display notify
                    $('.wpmb_settings #block-ip').prepend('<div style="width:99%; padding: 5px;display:none;" class="'+ (obj.status?'updated':'error') +' below-h2 notice">'+ obj.message +'</div>').find('.notice').show('slow').delay(5000).hide('slow',function(){$(this).remove();});

                    //add to sidebar
                    if(obj.status == true && obj.id != 'add_block_ip'){
                    	console.log(obj);
                        form.hide('slow',function(){
                            $(this).remove();
                        });
                    }
                }
            });
            return false;
        };
        $('.wrap.wpmb_settings #block-ip #post-body-content form').submit(remove_block_ip);


        //settings page - blocked domain tab
        $('.wrap #block-domain .sidebar form').submit(function(event){
            /* stop form from submitting normally */
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: ajaxurl ,
                data: $(this).serializeArray(),
                success: function(response){
                    var obj = $.parseJSON(response);
                    //display notify
                    $('.wpmb_settings #block-domain').prepend('<div style="width:99%; padding: 5px;display:none;" class="'+ (obj.status?'updated':'error') +' below-h2 notice">'+ obj.message +'</div>').find('.notice').show('slow').delay(5000).hide('slow',function(){$(this).remove();});

                    //add to sidebar
                    if(obj.status == true){
                        var html = '';
                        html += '<form id="domain-'+ obj.id +'" method="POST" action="">';
                            html +='<table class="widefat">';
                                html +='<tr valign="top">';
                                    html +='<td class="row-title">';
                                        html +='<label><a href="'+ obj.domain +'">'+ obj.domain +'</a></label>';
                                    html +='</td>';
                                    html +='<td align="right">';
                                        html +='<input class="button-primary" type="submit" name="submit" value="Remove">';
                                    html +='</td>';
                                html +='</tr>';
                            html +='</table>';
                            html +='<input type="hidden" name="id" value="'+ obj.id +'">';
                            html +='<input type="hidden" name="action" value="delete_block_domain">';
                            html +='<input type="hidden" name="ajax" value="true">';
                        html += '</form>';
                        $('.wrap #block-domain #post-body-content .inside').append(html).find('#domain-'+ obj.id).submit(remove_block_domain);

                    }
                }
            });
            return false;
        });

        //settings page - blocked domain tab (remove domain from block list)
        function remove_block_domain(event){
            /* stop form from submitting normally */
            var form = $(this);
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: ajaxurl ,
                data: $(this).serializeArray(),
                success: function(response){
                    var obj = $.parseJSON(response);
                    //display notify
                    $('.wpmb_settings #block-domain').prepend('<div style="width:99%; padding: 5px;display:none;" class="'+ (obj.status?'updated':'error') +' below-h2 notice">'+ obj.message +'</div>').find('.notice').show('slow').delay(5000).hide('slow',function(){$(this).remove();});

                    //add to sidebar
                    if(obj.status == true && obj.id != 'add_block_domain'){
                        form.hide('slow',function(){
                            $(this).remove();
                        });
                    }
                }
            });
            return false;
        }
        $('.wrap.wpmb_settings #block-domain #post-body-content form').submit(remove_block_domain);

        //settings page -  blocked referrers tab (add link to block list)
        $('.wrap #block-referrer .sidebar form').submit(function(event){
            /* stop form from submitting normally */
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: ajaxurl ,
                data: $(this).serializeArray(),
                success: function(response){
                    var obj = $.parseJSON(response);
                    //display notify
                    $('.wpmb_settings #block-referrer').prepend('<div style="width:99%; padding: 5px;display:none;" class="'+ (obj.status?'updated':'error') +' below-h2 notice">'+ obj.message +'</div>').find('.notice').show('slow').delay(5000).hide('slow',function(){$(this).remove();});

                    //add to sidebar
                    if(obj.status == true){
                        var html = '';
                        html += '<form id="domain-'+ obj.id +'" method="POST" action="">';
                            html +='<table class="widefat">';
                                html +='<tr valign="top">';
                                    html +='<td class="row-title">';
                                        html +='<label><a href="'+ obj.referrer +'">'+ obj.referrer +'</a></label>';
                                    html +='</td>';
                                    html +='<td align="right">';
                                        html +='<input class="button-primary" type="submit" name="submit" value="Remove">';
                                    html +='</td>';
                                html +='</tr>';
                            html +='</table>';
                            html +='<input type="hidden" name="id" value="'+ obj.id +'">';
                            html +='<input type="hidden" name="action" value="delete_block_referrer">';
                            html +='<input type="hidden" name="ajax" value="true">';
                        html += '</form>';
                        $('.wrap #block-referrer #post-body-content .inside').append(html).find('#domain-'+ obj.id).submit(remove_block_referrer);

                    }
                }
            });
            return false;
        });

        //settings page - blocked referrers tab (remove link from block list)
        function remove_block_referrer(event){
            /* stop form from submitting normally */
            var form = $(this);
            event.preventDefault();
            $.ajax({
                type: "POST",
                url: ajaxurl ,
                data: $(this).serializeArray(),
                success: function(response){
                    var obj = $.parseJSON(response);
                    //display notify
                    $('.wpmb_settings #block-referrer').prepend('<div style="width:99%; padding: 5px;display:none;" class="'+ (obj.status?'updated':'error') +' below-h2 notice">'+ obj.message +'</div>').find('.notice').show('slow').delay(5000).hide('slow',function(){$(this).remove();});
                    //add to sidebar
                    if(obj.status == true && obj.id != 'add_block_referrer'){
                        form.hide('slow',function(){
                            $(this).remove();
                        });
                    }
                }
            });
            return false;
        }
        $('.wrap.wpmb_settings #block-referrer #post-body-content form').submit(remove_block_referrer);

        //Settings page - Email frequency
        $('#email-frequency').change(function(){
            if($(this).val() == '0'){
                $('#email-frequency-day,#email-frequency-hour-min').hide();
            }else if($(this).val() == 'daily'){
                $('#email-frequency-day').hide();
                $('#email-frequency-hour-min').show();
            }else if($(this).val() == 'weekly'){
                $('#email-frequency-day,#email-frequency-hour-min').show();
            }
        });

        //Advanced settings show/hide
        $('.advanced-settings-trigger a').click(function(){
            $('.advanced-settings-trigger a').toggle();
            $('.advanced-settings').toggle('slow');
            return false;
        });

        //Settings page - Cron description show/hide
        $('#cron').change(function(){
            $('#ownCronDescription,#wpCronDescription').toggle('slow');
            if($(this).val() == '0'){
                $('#cron_recurrence_row').hide();
                $('#cron_recurrence_desc_row').hide();
            }else{
            	$('#cron_recurrence_row').show();
            	$('#cron_recurrence_desc_row').show();
            }            
        });

    }
    /*---------/Settings page------*/

    /*---------Dashboard page------*/
    if($('.wpmb_dashboard').length){
        //add change events
        $('.wpmb_dashboard .actions select.actionsSelect').change(function(){
            $(this).parents('form').submit();
        });
        //dashboard page - remove backlink
        $('.wpmb_dashboard .actions form').submit(function(event){
            /* stop form from submitting normally */
            event.preventDefault();
            if($(this).find('select[name="action"] option:selected').val()=='') return false;
            $.ajax({
                type: "POST",
                url: ajaxurl ,
                data: $(this).serializeArray(),
                success: function(response){
                    var obj = $.parseJSON(response);
                    //display notify
                    $('.wpmb_dashboard').prepend('<div style="width:99%; padding: 5px;display:none;" class="'+ (obj.status?'updated':'error') +' below-h2 notice">'+ obj.message +'</div>').find('.notice').show('slow').delay(5000).hide('slow',function(){$(this).remove();});

                    //remove row
                    if(obj.status == true && obj.action == 'delete_backlink'){
                        $('.wpmb_dashboard tr.row-'+obj.id).hide('slow',function(){
                            $(this).remove();
                        });
                    }

                    //highlight
                    if(obj.status == true && obj.action == 'highlight_backlink'){
                        $('.wpmb_dashboard tr.row-'+obj.id).addClass('highlight');
                        $('.wpmb_dashboard tr.row-'+obj.id+' .actions select.actionsSelect').find('option:eq(0)').prop('selected', true);
                        $('.wpmb_dashboard tr.row-'+obj.id+' .actions select.actionsSelect').find('option[value="highlight_backlink"]').val('unhighlight_backlink').text('Unhighlight');
                    }

                    //unhighlight
                    if(obj.status == true && obj.action == 'unhighlight_backlink'){
                        $('.wpmb_dashboard tr.row-'+obj.id).removeClass('highlight');
                        $('.wpmb_dashboard tr.row-'+obj.id+' .actions select.actionsSelect').find('option:eq(0)').prop('selected', true);
                        $('.wpmb_dashboard tr.row-'+obj.id+' .actions select.actionsSelect').find('option[value="unhighlight_backlink"]').val('highlight_backlink').text('Highlight');
                    }
                }
            });
            return false;
        });
    }

    /*---------/Dashboard page------*/
});