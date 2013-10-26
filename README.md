Speed Reader by David Reilly
============================
Provides non-Chrome access to groups/dials set up using the
[Speed Dial 2](http://speeddial2.com/) Chrome extension.

Github project page:
https://github.com/greyblue9/speed-reader


Speed Reader is a self-contained web page with an included QuickPHP web server intented to offer a Firefox "startup page" for users of the excellent Chrome extension [Speed Dial 2](http://speeddial2.com/).

The page, which is a small PHP web app, attempts to download the raw bookmark ("dials") and groups data from the Speed Dial 2 servers, and then render them as a standard HTML/Javascript/CSS web page for users of Firefox (or any other browser that does not have access to the official Chrome extension). The data it receives is used by Speed Dial 2 in normal operation to sync groups and dials across different computers and Chrome browsers belonging to the same Speed Dial 2 user account.


Installation
============

Setting SpeedDial2 login info
----------------------------------

You need to provide your Speed Dial 2 login credentials before Speed Reader
can function (it downloads your most current set of groups/dials from their
servers each time the main page is displayed).

Open `Auth.class.inc` in a text editor of your choice (the source style is PHP),
and set the values of the two variables, **$username** and **$password**, to
match your credentials as tested on the
[Speed Dial 2 Login](http://speeddial2.com/login) page.

Setting up the webserver
------------------------

### Windows

If you are on Windows, just use the existing QuickPHP webserver, which is
included with all necessary binaries and pre-configured. It should
be all set up to serve the Speed Reader startup page at this URL:

	http://127.0.0.1:5678/

You can then create a shortcut in your Startup programs folder (typically found
in your Windows User directory under
`AppData/Roaming/Microsoft/Windows/Start Menu/Programs/Startup` in Windows 7
and later (possibly Vista as well).

The target for this shortcut should be:

	DRIVE:\path\to\speed-reader\start_hidden.vbs

...where `DRIVE:\path\to\speed-reader\` is substituted with the location of
your Speed Reader main directory.

**NOTE:** I recommend setting your Firefox homepage to your Speed Reader URL,
and downloading the excellent Firefox addon
[New Tab Homepage](http://www.cusser.net/extensions/tabhomepage/) so that
Speed Reader will be shown by default for new tabs as well.

### Other platforms

I suggest using a bundled PHP/Apache package with easy installation and
configuration such as [XAMPP](http://www.apachefriends.org/en/xampp.html)
(XAMPP supports Linux, Windows, Mac OS X, and Solaris) where I would recommend 
[WAMP](http://www.wampserver.com/en/) on a Windows environment. Make sure your 
environment includes PHP 5.3 or higher and that the main Speed Reader directory 
(containing `index.php` and `dials.php`) is set as your web root, or one of your 
virtual host web roots, depending on your preferences.

Typically, in an Apache webserver environment, the web root is specified
with the `DocumentRoot` setting in your active `httpd.conf` file. Apache
typically listens for HTTP connections on port 80 by default (specified
by one or more `Listen` setting lines in the same file), so in its most simple
configuration, the speed dial homepage would be accessible at:

	http://127.0.0.1/

...or, if you prefer (though this may be significantly slower on some systems,
notably on Windows systems, possibly due to IPv6 routing and virtual network
adapters):

	http://localhost/

