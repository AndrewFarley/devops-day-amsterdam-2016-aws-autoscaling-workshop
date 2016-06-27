<!DOCTYPE html><html><head></head><body>
This is a test <br/>
This server is <?php echo gethostname(); ?></br>
Your IP is <?php 
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
    echo $_SERVER['HTTP_X_FORWARDED_FOR'];
else
    echo $_SERVER['REMOTE_ADDR']; ?></br>
Thanks for coming!
</body></html>