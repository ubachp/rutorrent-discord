//plugin.loadLang();
plugin.mark = 0;
plugin.hstTimeout = null;

plugin.actionNames = ['', '', '', ''];

if(plugin.canChangeOptions())
{
    plugin.addAndShowSettings = theWebUI.addAndShowSettings;
    theWebUI.addAndShowSettings = function( arg )
    {
        if(plugin.enabled)
        {
            $('#discordpopo_webhook').val( theWebUI.discordpopo.discordpopo_webhook );
            $('#discordpopo_avatar').val( theWebUI.discordpopo.discordpopo_avatar );
            $('#discordpopo_pushuser').val( theWebUI.discordpopo.discordpopo_pushuser );
            $$('discordpopo_enabled').checked = ( theWebUI.discordpopo.discordpopo_enabled != 0 );
            $$('discordpopo_addition').checked = ( theWebUI.discordpopo.discordpopo_addition != 0 );
            $$('discordpopo_finish').checked = ( theWebUI.discordpopo.discordpopo_finish != 0 );
            $$('discordpopo_deletion').checked = ( theWebUI.discordpopo.discordpopo_deletion != 0 );

            $('#discordpopo_enabled').change();

            //plugin.rebuildNotificationsPage();
        }
        plugin.addAndShowSettings.call(theWebUI,arg);
    }

    theWebUI.discordpopoWasChanged = function()
    {
        return(($$('discordpopo_enabled').checked != ( theWebUI.discordpopo.discordpopo_enabled != 0 )) ||
        ($$('discordpopo_addition').checked != ( theWebUI.discordpopo.discordpopo_addition != 0 )) ||
        ($$('discordpopo_finish').checked != ( theWebUI.discordpopo.discordpopo_finish != 0 )) ||
        ($$('discordpopo_deletion').checked != ( theWebUI.discordpopo.discordpopo_deletion != 0 )) ||
        ($('#discordpopo_avatar').val() != theWebUI.discordpopo.discordpopo_webhook) ||
        ($('#discordpopo_pushuser').val() != theWebUI.discordpopo.discordpopo_webhook) ||
        ($('#discordpopo_webhook').val() != theWebUI.discordpopo.discordpopo_webhook));
    }

    plugin.setSettings = theWebUI.setSettings;
    theWebUI.setSettings = function()
    {
        plugin.setSettings.call(this);
        if( plugin.enabled && this.discordpopoWasChanged() )
            this.request( "?action=setdiscordpopo" );
    }

    rTorrentStub.prototype.setdiscordpopo = function()
    {
        this.content = "cmd=set" +
            "&discordpopo_addition=" + ( $$('discordpopo_addition').checked ? '1' : '0' ) +
            "&discordpopo_deletion=" + ( $$('discordpopo_deletion').checked  ? '1' : '0' ) +
            "&discordpopo_finish=" + ( $$('discordpopo_finish').checked  ? '1' : '0' ) +
            "&discordpopo_enabled=" + ( $$('discordpopo_enabled').checked  ? '1' : '0' ) +
            "&discordpopo_avatar=" + $('#discordpopo_avatar').val() +
            "&discordpopo_pushuser=" + $('#discordpopo_pushuser').val() +
            "&discordpopo_webhook=" + $('#discordpopo_webhook').val();

        this.contentType = "application/x-www-form-urlencoded";
        this.mountPoint = "plugins/discordpopopush/action.php";
        this.dataType = "script";
    }
}

if(plugin.canChangeTabs() || plugin.canChangeColumns())
{
    plugin.config = theWebUI.config;
    theWebUI.config = function(data)
    {
        plugin.config.call(theWebUI,data);
    }

    rTorrentStub.prototype.getdiscordpopo = function()
    {
        this.content = "cmd=get&mark=" + plugin.mark;
        this.contentType = "application/x-www-form-urlencoded";
        this.mountPoint = "plugins/history/action.php";
        this.dataType = "json";
    }

    if(!$type(theWebUI.getTrackerName))
    {
        theWebUI.getTrackerName = function(announce)
        {
            var domain = '';
            if(announce)
            {
                var parts = announce.match(/^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/);
                if(parts && (parts.length>6))
                {
                    domain = parts[6];
                    if(!domain.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/))
                    {
                        parts = domain.split(".");
                        if(parts.length>2)
                        {
                            if($.inArray(parts[parts.length-2]+"", ["co", "com", "net", "org"])>=0 ||
                                $.inArray(parts[parts.length-1]+"", ["uk"])>=0)
                                parts = parts.slice(parts.length-3);
                            else
                                parts = parts.slice(parts.length-2);
                            domain = parts.join(".");
                        }
                    }
                }
            }
            return(domain);
        }
    }

    if(plugin.canChangeMenu())
    {
        /*dxSTable.prototype.historySelect = function(e,id)
        {
            if(plugin.enabled && plugin.allStuffLoaded && (e.which==3))
            {
                var self = "theWebUI.getTable('"+this.prefix+"').";
                theContextMenu.clear();
                theContextMenu.add([theUILang.Remove, self+"cmdHistory('delete')"]);
                theContextMenu.show(e.clientX,e.clientY);
            }
        }*/
    }
}

if(plugin.canChangeMenu())
{

    plugin.createMenu = theWebUI.createMenu;
    theWebUI.createMenu = function( e, id )
    {
        plugin.createMenu.call(this, e, id);
        if(plugin.enabled && plugin.allStuffLoaded && theWebUI.discordpopo.discordpopo_enabled)
        {
            /*var table = this.getTable("trt");
            var el = theContextMenu.get(theUILang.peerAdd);
            if( el )
            {
                if(table.selCount==1)
                {
                    theContextMenu.add(el,[CMENU_CHILD, 'Pushbullet',
                        [
                            [ theUILang.turnNotifyOn, theWebUI.torrents[id].pushbullet ? "theWebUI.setPushbullet('')" : null ],
                            [ theUILang.turnNotifyOff, theWebUI.torrents[id].pushbullet ? null : "theWebUI.setPushbullet('1')" ]
                        ]]);
                }
                else
                {
                    theContextMenu.add(el,[CMENU_CHILD, 'Pushbullet',
                        [
                            [ theUILang.turnNotifyOn, "theWebUI.setPushbullet('1')" ],
                            [ theUILang.turnNotifyOff, "theWebUI.setPushbullet('')" ]
                        ]]);
                }
            }*/
        }
    }
}

plugin.onLangLoaded = function()
{
    injectScript(plugin.path+"/desktop-notify.js",function()
    {
        plugin.attachPageToOptions( $("<div>").attr("id","st_discordpopo").html(
            "<fieldset>"+
            "<legend><a href='https://discordapp.com/developers/applications/me' target='_blank'>Discord Notifications Popo</a></legend>"+
            "<div class='checkbox'>" +
            "<input type='checkbox' id='discordpopo_enabled' onchange=\"linked(this, 0, ['discordpopo_webhook','discordpopo_avatar','discordpopo_pushuser','discordpopo_addition','discordpopo_deletion','discordpopo_finish']);\"/>"+
            "<label for='discordpopo_enabled'>Enabled</label>"+
            "</div>" +
            "<div>" +
            "<label for='discordpopo_webhook' id='lbl_discordpopo_webhook' class='disabled'>Discord Webhook URL</label>"+
            "<input type='text' id='discordpopo_webhook' class='TextboxLarge' disabled='true' />"+
            "</div>" +
            "<div>" +
            "<label for='discordpopo_avatar' id='lbl_discordpopo_avatar' class='disabled'>Override Avatar URL</label>"+
            "<input type='text' id='discordpopo_avatar' class='TextboxLarge' disabled='true' />"+
            "</div>" +
            "<div>" +
            "<label for='discordpopo_pushuser' id='lbl_discordpopo_pushuser' class='disabled'>Override Push Username</label>"+
            "<input type='text' id='discordpopo_pushuser' class='TextboxLarge' disabled='true' />"+
            "</div>" +
            "<div class='checkbox'>" +
            "<input type='checkbox' id='discordpopo_addition' disabled='true' />"+
            "<label for='discordpopo_addition' id='lbl_discordpopo_addition' class='disabled'>Addition</label>"+
            "</div>" +
            "<div class='checkbox'>" +
            "<input type='checkbox' class='disabled' id='discordpopo_deletion' disabled='true' />"+
            "<label for='discordpopo_deletion' id='lbl_discordpopo_deletion' class='disabled'>Deletion</label>"+
            "</div>" +
            "<div class='checkbox'>" +
            "<input type='checkbox' id='discordpopo_finish' disabled='true' />"+
            "<label for='discordpopo_finish' id='lbl_discordpopo_finish' class='disabled'>Finish</label>"+
            "</div>" +
            "</fieldset>"
        )[0], "Discord Popo" );
        plugin.actionNames = ['', "Added", "Finished", "Deleted"];
        plugin.markLoaded();
    });
}

plugin.onRemove = function()
{
    plugin.removePageFromOptions("st_discordpopo");
    //theRequestManager.removeRequest( "trt", plugin.reqId1 );
}

/*plugin.langLoaded = function()
{
    if(plugin.enabled)
        plugin.onLangLoaded();
}*/
if(plugin.enabled)
    plugin.onLangLoaded();