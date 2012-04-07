=== Plugin Name ===
Contributors: ankit_frenz, piyushmishra
Tags: facebook comments,wp-fb,wordpress-facebook comments
Requires at least: 2.8.0
Tested up to: 3.1
Stable tag: 1.2.6

This plugin allows you merge WordPress and Facebook Comment system fully.With the ability to keep synchronized and updated comments at both ends.

== Description ==
This plugin allows you merge WordPress and Facebook Comment system fully.With the ability to keep synchronized and updated comments at both ends.

This has many features that enable and pollinate the complete comments system for you in the FB and WP at the same time doesn't get too heavy on your server.

All your posts will be automatically shared via your page in FB.You have complete control on the application that you will be using for this.

== Installation ==
1. Create a page in Facebook for your site if you already dont have one,And note its pageid.
2. Create an application in facebook and note down the application id,key and seceret.
3. Upload the wp-fb directory to the /wp-content/plugins/ directory
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Enter all details in the first section of the wp-fb options page and save before proceeding.
6. Enter your website url to the Fb App from the App settings page.(Fb app setting>website>website url).
7. Edit the canvas page url,if you want.(optional)
8. once saved,click on connect admin account to connect your Fb account to your wp.
9. Enjoy the merging!!


== Errors ==

= Invalid redirect_uri =

You have not set FB Call back URL correctly.Please see the FAQ to know what should be your call back url. 


= Empty Page Token filed =

Possible reasons are:-
* You have page id not set.
* The account you are trying to connect doesnt have Admin rights on the page id you specified.

= Note: This plugin requiers somewhat more advanced configurations than traditional WP plugins, So please carefully checkout the installation guide and the FAQ as well. =

== Frequently Asked Questions ==

= What is a Page id? =

Pageid is a unique identification number assigned to all pages by facebook. 

= How do I get Page id from a facebook page? =

To find your Facebook Page ID of your page Click your Facebook  Page profile picture and now you should be able to see the pageid in the url of the browser.

= What is a facebook application? =

A facebook aplication is a pltform that allows a user to use and interact with the facebook via there apis.

= Do i need some special type of Facebook account to create a page and application? =

Your account must be phone verified.You must also connect your account to developer apps before you can create an application.

= What should be the callback url of the app? =

Callback url is the website url of the site.(This is an important filed, dont miss it out. Set it from FB>app settings>website>website url).

= What should be the canvas url of the app? =

Callback url and canvas url are shown in the options page of the plugin.

= Which Facebook account should i connect to the plugin? =

You can connect any account to the plugin as long as it has Admin rights of the page whose pageid you specified.

= Do i need to manually share the posts in facebook? =

Nope.Each post is shared as soon as its published automatically.

= How often comments are pushed to Facebook? =

Coments are pushed in real time to FB.

= How often comments are pulled from Facebook? =

Coments are pulled in every hour for last 10 posts and every day for all posts.

= What about old posts and comments? =

Currently it works only for new posts.


== Changelog ==

= 1.2.6 =
Faq,installtion,support updates

= 1.2.5 =
Minor Bug fixes.

= 1.2.4 =
Post sharing issue fixed.


= 1.2.3 =
Error fixes with logs.

= 1.2.1 =
* Security Bugs fixed.
* Minor bug fixes.

= 1.1.0-beta =
* Major Bug fixes
* Logs added

= 1.0.1 =
* Cron issues fixed.
* other small issues fixed.

= 1.0 =
* First Beta version.

== Upgrade Notice ==

= 1.2.5 =
This update should fix multiple sharing of same post issue.

= 1.2.4 =
upgrade to remove any issues you might be facing with sharing of posts.

= 1.2.3 =
upgrade to remove any errors you might be facing.

= 1.2.1 =
Major security update. Please update (You may need to reconfigure the plugin. But trust us, its for the last time :)) 

= 1.1.0-beta =
Some major Bug fixes(Please deactivate and activate plugin if you dont see logs or see errors after upgrading from old version)

= 1.0.1 =
Upgrade to fix all cron issues you might be having.

= 1.0 =
First Beta release
