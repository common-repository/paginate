=== Paginate ===
Contributors: ivan82
Tags: page, pagination, paginate, pages, list, lists, users, profiles, files, images, photos
Requires at least: 2.5.1
Tested up to: 3.5.2
Stable tag: 1.0

Display your list with pagination by a simple function call.

== Description ==

Display your list with pagination by a simple function call.
Add multiple paginations to the same page.


**Features**

* Multiple paginations at the same page
* Chose how many items to display per page, default: 10
* Chose adjacent items, default: 6
* Customize on page query variable, default: "page"
* Choose if the on page value should be fetched automatically, default: true
* How to display first and last page buttons, as numbers or as text, default: numbers
* All the buttons are visible by default
* If the previous button is visible
* If the next button is visible
* If the previous button is visible when the first page is reached
* If the next button is visible when the last page is reached
* If the adjacent dots are visible
* If the first button is visible
* If the last button is visible
* Localization support - download the template and submit your language
* All HTML/CSS elements are customizable 


**Changelog**

* 1.0: Plug in release


== Installation ==

*Install and Activate*

1. Download and unzip `paginate.zip`.
2. Upload the `paginate` directory to the `/wp-content/plugins/` directory.
3. Activate the plugin through the `Plugins` menu in WordPress.
4. In your theme, modify the CSS file to change the look and feel of your pagination.

*Implement*

3 line implementation

`<?php
$totalitems = $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->users");
$p = new paginate($totalitems);
echo $p->get_pagination();
?>`


Post onpage argument

`<?php
$onPage = $_GET['page']
$totalitems = $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->users");
$p = new paginate($totalitems);
echo $p->get_pagination($onPage);
?>`


Example implementation

`<?php
//get total items from database
$totalitems = $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->users");

$p = new paginate($totalitems);
$limit = $p->itemsPerPage;
$offset = $p->get_offset();

//your query. add the offset and limit to the query
$query = "SELECT $wpdb->users.ID FROM $wpdb->users LIMIT $offset, $limit";
$user_ids = $wpdb->get_results($query);
//your loop
foreach($user_ids as $id) {
  //do something...
}

//pagination html
echo $p->get_pagination();
?>`