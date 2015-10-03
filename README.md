# LegacyPHP-To-Symfony
A solution to handle the migration of a PHP application to Symfony

# WARNING : Use at your OWN RISK ! This solution is working for me on my project, but I haven't tested it elsewhere ! 
There's probably a better way to do this, and a more secure one, and it may not work for you depending of your needs.

This is an adaptation of the following tutorial : http://symfonybricks.com/it/brick/wrap-legacy-php-code-by-a-symfony2-application

You'll certainly have to adapt/tweak things, I'm not posting this Controller as a "all-purpose solution" but as an example
of what's been working for me. If you find yourself in the need of migrating a legacy PHP application to Symfony2, this may help
you to perform a progressive migration.

The idea is to place your whole legacy application into the "Web" folder of Symfony. The controller will act as a "catch-all" route
that'll route everything to your legacy app using CURL to forward GET/POST request.

Once the controller is in place, you can access your whole application using Symfony2 route, and
you can manually migrate your legacy app part by part, creating new route that override 
the "catch-all" route.

Known issues :

- Legacy app's cookies are not working, this is because I'm only forwarding the PHPSSID to every CURL request, but not cookies data. You may
achieve this using CURLOPT_COOKIEJAR/CURLOPT_COOKIEFILE to write/read cookies data. Remember to keep a different cookie file for each of your user or
they'll all try to store their data into the same file, and you don't want that.
- Multi-file upload is not working, that's because of the way the POST data are handled, this can probably be fixed.

The Curl doc can be helpful if you need to adapt things : http://php.net/manual/fr/function.curl-setopt.php




