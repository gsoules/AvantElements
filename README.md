# AvantElements (plugin for Omeka Classic)

#################################################

> **This plugin is under development. Please wait for Release 2.0.**

#################################################

Provides data entry and validation features to the Omeka admin interface and allows customization of 
elements on the public interface. These features help ensure data integrity, make data entry easier,
and provide an enhanced experience for end users.

## Table of Contents

- [Dependencies](https://github.com/gsoules/AvantElements#dependencies)
- [Installation](https://github.com/gsoules/AvantElements#installation)
- [Duplicate Item Feature](https://github.com/gsoules/AvantElements#duplicate-item-feature)
- [Usage](https://github.com/gsoules/AvantElements#usage)
    - [Configuration Options](https://github.com/gsoules/AvantElements#configuration-options)
        - [Display Order Option](https://github.com/gsoules/AvantElements#display-order-option)
        - [Implicit Link Option](https://github.com/gsoules/AvantElements#implicit-link-option)
        - [External Link Option](https://github.com/gsoules/AvantElements#external-link-option)
        - [Hide Descriptions Option](https://github.com/gsoules/AvantElements#hide-descriptions-option)
        - [Validation Option](https://github.com/gsoules/AvantElements#validation-option)
        - [Allow Add Input Option](https://github.com/gsoules/AvantElements#allow-add-input-option)
        - [Allow HTML Option](https://github.com/gsoules/AvantElements#allow-html-option)
        - [Text Field Option](https://github.com/gsoules/AvantElements#text-field-option)
        - [SimpleVocab Field Option](https://github.com/gsoules/AvantElements#simplevocab-field-option)
        - [Checkbox Field Option](https://github.com/gsoules/AvantElements#checkbox-field-option)
        - [Read-only Field Option](https://github.com/gsoules/AvantElements#read-only-field-option)
        - [Default Value Option](https://github.com/gsoules/AvantElements#default-value-option)
        - [Suggest Option](https://github.com/gsoules/AvantElements#suggest-option)
        - [Title Sync Option](https://github.com/gsoules/AvantElements#title-sync-option)
        - [Custom Callback Option](https://github.com/gsoules/AvantElements#custom-callback-option)
- [Warning](https://github.com/gsoules/AvantElements#warning)
- [License](https://github.com/gsoules/AvantElements#license)
- [Copyright](https://github.com/gsoules/AvantElements#copyright)
- [Credits](https://github.com/gsoules/AvantElements#credits)
        

## Dependencies
The AvantElements plugin requires that the [AvantCommon] plugin be installed and activated.


## Installation

To install the AvantElements plugin, follow these steps:

1. First install and activate the [AvantCommon] plugin.
1. Unzip the AvantElements-master file into your Omeka installation's plugin directory.
1. Rename the folder to AvantElements.
1. Activate the plugin from the Admin → Settings → Plugins page.
1. Configure the AvantCommon plugin to specify your item identifier and title elements.
1. Configure the AvantElements plugin as described in the [Configuration Options](https://github.com/gsoules/AvantElements#configuration-options) section below.


### Duplicate Item Feature
When AvantElements is installed, a logged in admin or super user will see a **Duplicate This Item** button when viewing
an item in the admin interface. When you click this button, a new browser tab opens to display an **Add an Item**
page with its fields already filled in with data from the Item being duplicated. The one exception is the field used as
the item's unique identifier (see [AvantCommon Identifier Element option](https://github.com/gsoules/AvantCommon#usage)).
That field is left blank.

This feature can save a lot of time when entering a series of items that contain nearly the same data, but vary in just
a few places. Note that only an item's element values, not images or other file attachments, are duplicated.

## Usage
Once installed, AvantElements overrides many of Omeka's native user interface features for the public Show page and for
the admin Show and Edit pages. How it affects those pages depends on which configuration options are selected.

### Configuration Options
The sections that follow describe each of the options on the AvantElements plugin configuration page.

Many options require that you specify a formatted list of information using commas or other characters as separators.
For these options, spaces before and after the separators are ignored.

Syntax for some options is shown using [BNF notation](https://en.wikipedia.org/wiki/Backus%E2%80%93Naur_form).

---
#### Display Order Option
This option lets you specify the order, top to bottom, in which elements appear on public Show pages. Normally Omeka
displays Dublin Core elements first followed by Item Type Metadata elements. This option lets you intermingle both kinds
of elements in any sequence you like.

This option does not control the order of elements on admin pages. On the the admin Edit page, the order of elements on the
Dublin Core tab is dictated by the order on the Edit Element Set page. The order of elements on the Item Type Metadata
tab is dictated by the order on the Edit Item Type page.

###### Syntax:

Specify each element name on a separate row.

---
#### Implicit Link Option
Use this option to specify which elements will have their value display as a hyperlink to other elements that have
the exact same value. For example, if you list the `Type` element using this option, the text for the `Type` field on
public and admin Show pages will display a hyperlink that when clicked, will display search results listing every other
item that has the same value. If no other items share the value, there will be no hyperlink.

This feature will work whether or not [AvantSearch] is installed.

You can style implicit links using the class `metadata-search-link`.

###### Syntax:

Specify each element name on a separate row.

---
#### External Link Option
Use this option to specify which elements will have their value display as a hyperlink where the hyperlink's `href`
attribute is the element's value. For instance, you may have an element named Web Resource that is used to store the
URL to a web page that represents an item. If the item were for a book, the Web Resource might link to an online copy of
the book.

You can style external links using the class `metadata-external-link`.

**Important:** The URL stored as the value for an element used as an external link should start with `http://` or `https://`
Otherwise the browser will attempt to locate the resource on the Omeka site.

###### Syntax:

The syntax for each row of the External Link option is

    <element-name> [ “,” <open-in-new-tab> ] [ “:” <link-text> ]

Where:

* `<element-name>` is the name of an Omeka element.
* `<open-in-new-tab>` is an optional parameter with value "true" | "false" (without quotes )to indicate whether the
linked-to page should be opened in a new browser tab. If the parameter is omitted, the default is "true".
* `<link-text>` is an optional parameter specifying text to appear as the link text. If the parameter is omitted, the
URL from the element value appears as the link text.

###### Example:
```
Web Resource: View this item online
```

The example above will generate a hyperlink like the one shown below for the element value `http://www.somewebsite.com`:

    <a href="http://www.somewebsite.com" class="metadata-external-link" target="_blank">View this item online</a>

---
#### Hide Descriptions Option
When checked, this option hides the descriptive information that Omeka normally displays on the admin Edit page to
explain what Dublin Core means and what each element is used for. Use this option to make the Edit page more compact
and to hide information that is often not useful to anyone, but a first time user.

---
#### Validation Option
The Validation option lets you specify one or more validation rules for an element. When you click the Save Changes
button on the admin Edit page, AvantElements applies the rules. If the element value does not satisfy the rule, the
page reloads, still in edit mode, with an error message displayed at the top of the page. 

###### Syntax:

The syntax for each row of the Validation option is

    <element-name> ":" <rule> {"," <rule>}

Where:

* `<element-name>` is the name of an Omeka element.
* `<rule>` is "required" | "date" | "year" | "simple-text"

The table below explains the meaning of the rules.

Rule|Explanation
--------|------------
required | The element value must not be blank or contain only spaces.
date | The element value must be a date in the form YYY-MM-DD e.g. 2018-05-29. This format ensures that dates will sort properly.
year | The element value must be a four digit year
simple-text | The element value must not contain carriage returns, tabs, leading or trailing spaces, en or em dashes. If the text contains any of these, AvantElements will not report an error, but before saving the item, it will remove carriage returns and tabs, strip leading or trailing spaces, and replace an en or em dash with a hyphen.  This option is especially useful for the Title element to ensure that every title has simple, consistent formatting that can be reliably searched.

You can specify more than one rule as shown in the example below for the Title element.

###### Example:
```
Title: required, simple-text
Type: required
Subject: required
Creator: simple-text
Date: date
```

---
#### Allow Add Input Option
This option overrides Omeka's default behavior of displaying an "Add Input" button for every element on the admin Edit
page. AvantElements reverses this behavior so that the button appears only on the elements specified with this option.

###### Syntax:

Specify each element name on a separate row.

---
#### Allow HTML Option
This option overrides Omeka's default behavior of displaying a "Use HTML" checkbox for every element on the admin Edit
page. AvantElements reverses this behavior so that the checkbox appears only on the elements specified with this option.

###### Syntax:

Specify each element name on a separate row.

---
#### Text Field Option
The Text Field option lets you specify which elements should display on the admin Edit page as a one-line text box
instead of a multi-line textarea which is what Omeka displays normally.

###### Syntax:

The syntax for each row of the Text Field option is

    <element-name> [ ":" <width> ]

Where:

* `<element-name>` is the name of an Omeka element.
* `<width>` is an optional integer parameter indicating the width in pixels of the text box. If the parameter is omitted, the 
text box occupies the maximum available width.

---
#### SimpleVocab Field Option
The purpose of this option is to let you specify the width of dropdown lists that are displayed and populated by the
[SimpleVocab plugin](https://omeka.org/classic/docs/Plugins/SimpleVocab/). It lets you specify the width of the
dropdown in pixels.

By default, SimpleVocab always displays the dropdown as 300px wide.

Note that this option will not be available if the SimpleVocab plugin is not installed.

###### Syntax:

The syntax for each row of the Text Field option is

    <element-name> [ ":" <width> ]

Where:

* `<element-name>` is the name of an Omeka element.
* `<width>` is an optional integer  parameter indicating the width in pixels of the dropdown list. If the parameter is omitted, the 
dropdown list occupies the maximum available width.

---
#### Checkbox Field Option
This option lets you treat an element as a boolean (true or false) value that displays as a checkbox on the admin Edit
page. An element value of zero is considered false, and any other value is considered true. When you specify an element
using this option, you also provide text to display on the admin and public Show page to indicate whether the value is
true or false (the checkbox itself only appears on the admin Edit page).

###### Syntax:

The syntax for each row of the Checkbox Field option is

    <element-name> ":" <true-value> "|" <false-value>

Where:

* `<element-name>` is the name of an Omeka element.
* `<true-value>` is the text that should display on the Show page when the value is true.
* `<false-value>` is the text that should display on the Show page when the value is false.

###### Example:
```
Approved: Yes | No
```

---
#### Read-only Field Option
Elements listed using this option appear on the admin Edit page as read-only text. Use this option for element values
that are set by a plugin or other mechanism, but cannot be edited by an administrator.

###### Syntax:

Specify each element name on a separate row.

---
#### Default Value Option
Use the Default Value option to provide text that should be automatically filled in for an element when you add a new item.

If the element displays as a dropdown list with contents that come from the SimpleVocab plugin, be sure that the
default value you provide is one of the vocabulary values. For example, if you have a Status element with SimpleVocab
values 'Pending Approval', 'Approved', and 'Denied', the default value must be one of those three options.

###### Syntax:

The syntax for each row of the Default Value option is

    <element-name> ":" <value>

Where:

* `<element-name>` is the name of an Omeka element.
* `<value>` is the text that should be used for the default value of the element when a new item is added.

###### Example:
```
Status: Pending Approval
```

---
#### Suggest Option
The Suggest option lets you designate elements that should automatically suggest a list of values to choose from as
you are typing into the field. The behavior is similar to how the Add Tags field works on the Tags tab.

The suggested values come can come from two different sources:
- The values of the specified element in the other items in the database. For example, if you specify the Creator
element, then as you type, the Suggest logic queries all of the other Creator elements in the database looking for
values that contain the text you typed. This feature is especially useful for ensuring consistent data entry because
it lets you choose from text that already exists.
- A SimpleVocab vocabulary for the element. For this to work, you must also specify the same element name using the
Text Field option described above. By specifing the element in both the Text Field and Suggest options, the field will
display as a text box, not a dropdown list, and the suggestions will come from the vocabulary.

###### Syntax:

Specify each element name on a separate row.

---
#### Title Sync Option
The Title Sync option lets you specify the name of an element that contains text that must match the value of the Title
element in another item. For example, suppose the Creator element in ten different photograph items contains the name
of the photographer "John Smith" who took all ten pictures. Also suppose another item's Title element contains "John
Smith" and its description has biographical information about this photographer. Now suppose you realize that John's 
last name is really "Smyth" and so you edit the Title field in the bio item. If Creator is specified as an element in
the Title Sync option, the ten items that have "John Smith" as their Creator will automatically be updated to contain
the new value "John Smyth".

This option, along with the Suggest option described above, help to ensure data consistency among items. This is
especially important for searching purposes so that if, for example, a user finds one item matching "Smyth" they find
all the others. Note that although Omeka uses a relational database, it does not have separate tables for elements
like Creator which are commonly used to establish relationships between elements. The Omeka model makes for simpler
searching, but puts the burden on archivists to ensure data consistency. Options like Suggest and Title Sync help ease
that burden.
 

###### Syntax:

Specify each element name on a separate row.

---
#### Custom Callback Option
This is an advanced feature intended for you only by PHP programmers who have at least basic familiarity with how
Omeka plugins work. The Custom Callback option lets you specify actions to be performed by custom written PHP functions
that you provide.

###### Syntax:

The syntax for each row of the Default Value option is

    <element-name> "," <callback-type> ":" <class-name> "," <function-name>

Where:

* `<element-name> | "<item">` indicates that the callback is for the named element or for the entire item
* `<callback-type>` is "validate" | "save" | "filter" | "default" | "suggest"
* `<class-name>` is the name of a PHP class in a custom plugin
* `<function-name>` is the name of a public static function in <class-name>

The table below explains the meaning of the callback types.

Type | Used with | What the callback function must do
--------|------------|-----------
filter | \<element-name\> | Return a filtered version of the element's text. As an example, the filter could change "2018-05-29" to "May 5, 2018"
default | \<element-name\> | Provide a default value for the element when a new item is added
suggest | \<element-name\> | Return a list of suggestions while the user types into the element's field
validate | \<element-name\> or "\<item\>" | Validate the element text, or the item as a whole, and supply an error message if the text or item is invalid
save | "\<item\>" | Perform processing that occurs immediately after an item is saved to the database

###### Example:
The example below shows custom functions located in two different classes, Gcihs and DigitalArchive. The easiest way
to provide custom classes is to add your own .php file to the models folder of the [AvantCustom] plugin. See the source
code for that plugin for examples.
```
Identifier, default: DigitalArchive, getDefaultIdentifier
Identifier, validate: DigitalArchive, validateIdentifier
Accession #, validate: Gcihs, validateAccesssionNumber
Rights, filter: DigitalArchive, filterRights
<item>, save: Gcihs, saveItem
```

## Warning

Use this software at your own risk.

##  License

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

## Copyright

* Created by [gsoules](https://github.com/gsoules) 
* Copyright George Soules, 2016-2018.
* See [LICENSE](https://github.com/gsoules/AvantElements/blob/master/LICENSE) for more information.


## Credits
This plugin was originally developed for the Southwest Harbor Public Library [Digital Archive](http://swhplibrary.net/archive).
Funding was provided in part by the [John S. and James L. Knight Foundation](https://knightfoundation.org/).

[AvantAdmin]:https://github.com/gsoules/AvantAdmin
[AvantCommon]:https://github.com/gsoules/AvantCommon
[AvantCustom]:https://github.com/gsoules/AvantCustom
[AvantElements]:https://github.com/gsoules/AvantElements
[AvantRelationships]:https://github.com/gsoules/AvantRelationships
[AvantSearch]:https://github.com/gsoules/AvantSearch