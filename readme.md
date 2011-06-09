Plus One Button
===============

Add Google +1 (Plus One) buttons throughout your WordPress powered website. Built using best practices in WordPress plugin development providing you a safe and secure way to add this feature to your posts and pages. There are many options available for customization. Please read below for details.

Automatic Theme Integration
---------------------------

* Supports both xhtml and html5 markup.
* Choose from [44 languages](http://code.google.com/apis/+1button/#languages).
* Insert button relative to the content in singular templates.
* Insert button relative to the content and/or excerpt in archive templates.
* Support for posts, pages and media as well as any custom post types installed on your site.

Manual Theme Integration
------------------------

If you find that the automatic options are limiting in any way, you can un-select all of them and use the `mfields-plus-one-button` action directly in your theme files. Below you will find examples of different ways to customize the action. 

    /**
     * Default usage.
     * Should be used inside The Loop.
     */
    do_action( 'mfields-plus-one-button' );



    /**
     * Small button with count.
     */
    do_action( 'mfields-plus-one-button', array(
        'size'  => 'small',
        'count' => 'true',
        ) );



    /**
     * Small button without count.
     */
    do_action( 'mfields-plus-one-button', array(
        'size'  => 'small',
        'count' => 'false',
        ) );



    /**
     * Medium button with count.
     */
    do_action( 'mfields-plus-one-button', array(
        'size'  => 'medium',
        'count' => 'true',
        ) );



    /**
     * Medium button without count.
     */
    do_action( 'mfields-plus-one-button', array(
        'size'  => 'medium',
        'count' => 'false',
        ) );



    /**
     * Tall button.
     * Count is always displayed with this size.
     * Passing the 'count' argument will have no effect.
     */
    do_action( 'mfields-plus-one-button', array(
        'size'  => 'tall',
        ) );



    /**
     * Medium button.
     * Count will be shown.
     * Custom url is provided.
     * This example shows the best way to use this action
     * outside of The Loop.
     */
    do_action( 'mfields-plus-one-button', array(
        'size'  => 'medium',
        'count' => 'true',
        'url'   => 'http://mfields.org/',
        ) );



    /**
     * Medium button.
     * Count will be shown.
     * Custom url is provided.
     * Markup is set to "html".
     */
    do_action( 'mfields-plus-one-button', array(
        'size'   => 'medium',
        'count'  => 'true',
        'url'    => 'http://mfields.org/',
        'markup' => 'html',
        ) );



Support
-------

If you have questions about integrating this plugin into your site, please [add a new thread to the WordPress Support Forum](http://wordpress.org/tags/plus-one-button?forum_id=10#postform). I try to answer these, but I may not always be able to. In the event that I cannot there may be someone else who can help.

Bugs, Suggestions
-----------------

Development of this plugin is hosted in a public repository on [Github](https://github.com/mfields/mfields-plus-one). If you find a bug in this plugin or have a suggestion to make it better, please [create a new issue](https://github.com/mfields/mfields-plus-one/issues/new)

Installation
------------

1. Download
1. Unzip the package and upload to your /wp-content/plugins/ directory.
1. Log into WordPress and navigate to the "Plugins" panel.
1. Activate the plugin.
1. Click the "Plus One" link under the Settings section in the admin menu. There you be able to configure all available options.

Changelog
---------

= 0.1.2 =

* Fix double button bug.
* Add global option for button alignment.

= 0.1.1 =

* HTML attribute fix for "size" - dropping "data-" prefix.

= 0.1 =

* Original Release -> works with WordPress 3.1.