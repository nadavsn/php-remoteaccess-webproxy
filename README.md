# A web proxy for remote-content access built with php

A simple, NOT anonymous web proxy that is useful for letting
 users access IP/domain-protected sites such as online libraries and
 content providers from wherever they are ("off-campus").
 It is a simple and effective alternative to the commerical product
 "EZProxy".
 
 The core functionality can be found within `proxy.php` which can very easily
 be plugged into an existing project. The additional files are absolutely enough
 for a simple system with a limited number of users, and can be modified an scaled
 to meet your needs (e.g. add a users database, multiple proxied hosts etc.)

## Requirements

A webserver with support for:

 - PHP
 - cURL
 - .htaccess

## Installation

It's php, so there's not much hassle... Just upload the files to your webserver
 and, theoretically, you're ready to go.

However, when uploading the files to the server, web access to the root directory must not
 be in an internal folder. The base URL for the web proxy should be something
 like `https://proxy.my-website.com/` and not `https://www.my-website.com/proxy`. That is
 to allow the proxied source to take over the web path.

## Setup

1. Edit the hostname that you want proxied: Open `index.php` and edit the `proxify()` function:
    - Simple usage
    
        proxify('http', 'example.com');  
    - Secured website
    
        proxify('https', 'example.com');  
    - Proxy with some special functionality defined by you ("plugin" - read bellow for more info):
    
        proxify('http', 'example.com', 'plugin_name');
2. Edit predefined users: Open `users` and edit according to the following pattern
    (user details separated with `:`):

        email (username):md5-hashed password:name
    So for the user "John Doe" with the email "john@example.com" and with the password "12345" we'll have:

        john@example.com:827ccb0eea8a706c4c34a16891f84e7b:John Doe
    Each user is defined in a new line.  
3. Surf to `https://proxy-address`, enter a user's details, and enjoy! (seriously). You can find
 the default user credentials below (see "additional useful info").

## Plugins

The simple reason for using plugins is for being able to manipulate the response from the proxied host
 before you send it finally to the end-user. A plugin is simply a php file with an "init" function that
 receives the response as an array. From there, you can do whatever you want. You can even use it to do
 something else with the response and not output it to the end-user.  
 However, the most appealing reason I see for using a plugin is for tweaking the HTML response do change
 the webpage "just a little bit", e.g. add your organization logo or greet the user (after all, you the user's
 details).

A plugin should be:

 - In a file named `[plugin-name].plugin.php`.
 - With the function `[plugin-name]_init($response)` inside the file.
 
The `$response` parameter is an array with following keys/values:

1. `content-type`: the content (MIME) type of the response.
2. `eff-url`: the "effective" url - the actual URL of the page after redirects (if there were any)
3. `body`: the response body (as HTML, CSS, JS or whatever)

## Additional useful info

 - Bear in mind that all the cookies from the source are **always** forwarded the end-user.
 - Any external call from the page (call for frames; JS; CSS;) are NOT proxied by default and that is
 because the proxy isn't intended to be anonymous and such calls are usually not restricted to a specific IP/domain.
 Direct requests to external assets make the user experience faster. If you want to proxy these too, you can use a plugin
 to overwrite URLs.
 - Default user credentials:
    1. Username: user1@example.com; Password: 12345
    2. Username: user2@example.com; Password: 123456
    3. Username: user3@example.com; Password: 1234567

## Suggestions?

Any suggestions or pull requests are encouraged! I will be happy to improve this little project.

## License

Do whatever you want with this code. I myself got help from a handful of people
and resources, too. Anyway, this project has an MIT license.