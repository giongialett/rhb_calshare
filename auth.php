<?php
// This is called by Client Application in a pupop (javascript:window.open) for authentification
// Load the auth module, this will redirect us to login if we aren't already logged in.

include 'inc/auth.inc';
$Auth = new modAuth();
?>

<h1>Bitte dieses Fenster schliessen</h1>
<script>
	window.close();
</script>

