=== Walk Around The World ===
Contributors: Ryan Paiva
Donate link: http://osteodiet.com/
Tags: walk, logger, record, world, calculator, step
Requires at least: 2.0.2
Tested up to: 2.7
Stable tag: 1.0

== Description ==
Allow your blog users to help your blog walk around the world! Great for any kind of health blog. Get your readers out there and be healthy!

Users can form teams and record how much they walk each day. Then it displays a graphical flash-based representation as to how far your blog has walked! Together, you and your readers can walk around the world! 


== Installation ==


Install


Step 1: Upload the 'Walk-Around-The-World' folder to your wp-content/plugins/ folder.

Step 2: Enable the 'Walk-Around-The-World' plugin from your plugin management area.


Update


Step 1: Deactivate the plugin.

Step 2: Upload new files.

Step 3: Activate the plugin.

== Frequently Asked Questions ==

Feel free to ask one! Post any questiosn in a comment at:

http://osteodiet.com/walk-around-the-world/

== Usage ==


Step 1: Edit your theme's page.php and add:


<?php
if(function_exists($watw_Plugin->display_watw))
$watw_Plugin->display_watw();
?>

Step 2: Create a wordpress page

Step 3: Go to Settings -> WATW Options and enter the name of your page

And you're done! Be sure to visit Manage/Tools -> WATW Manager to approve Teams and Members


== Live Demo ==

A live demo can be seen at http://osteodiet.com/walk-around-the-world/
