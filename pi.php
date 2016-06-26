<!DOCTYPE html><html><head></head><body>
Lets calculate pi...<br/>
<?php

// Flush the screen (above) so the user sees something while we calculate
echo "<br> Beginning to calculate..." . str_pad('',4096)."<br/>";
ob_flush();
flush();
sleep(1);


/////////////////////////////////
// Simple CPU intensive example
// Copied from: https://coderwall.com/p/g35wya/calculating-pi-using-php
/////////////////////////////////
$pi = 4; $top = 4; $bot = 3; $minus = TRUE;
$accuracy = 100000;

for($i = 0; $i < $accuracy; $i++)
{
  $pi += ( $minus ? -($top/$bot) : ($top/$bot) );
  $minus = ( $minus ? FALSE : TRUE);
  $bot += 2;
}
echo "We have calculated Pi ~=: " . $pi . "<br>";

?>
This server is <?php echo gethostname(); ?></br>
Your IP is <?php echo $_SERVER['REMOTE_ADDR']; ?></br>
Thanks for coming!
</body></html>
