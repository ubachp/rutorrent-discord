<?php

$req = new rXMLRPCRequest(array(
    rTorrentSettings::get()->getOnInsertCommand(array('tdiscordpopo'.getUser(), getCmd('cat='))),
    rTorrentSettings::get()->getOnFinishedCommand(array('tdiscordpopo'.getUser(), getCmd('cat='))),
    rTorrentSettings::get()->getOnEraseCommand(array('tdiscordpopo'.getUser(), getCmd('cat=')))
));
$req->run();
