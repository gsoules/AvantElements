# AvantElements (plugin for Omeka Classic)

#################################################

> **This plugin is under development. Please wait for Release 2.0.**

#################################################

The AvantElements plugin adds data entry and validation features to the Omeka admin interface. These features help ensure data integrity and make data entry easier for archivists. The plugin provides the following:

* Auto creation of Identifier value when adding a new item.
* Control of element display order and mixing of Dublin Core and non Dublin Core elements on public pages
* Identifier validation to ensure that the Identifier is unique.
* Bolder error messages.
* Clone item feature.
* Data validation for specific elements.
* Detection of carriage returns, leading/trailing spaces, and en/em dashes where prohibited.
* Validation for Date, Year Start, Year End. Hide start/end when same.
* Control of field widths.
* Auto creation of links to one or more other items having the same element value as the displayed item.
* Auto creation of links to external web resources.
* Option to hide the HTML checkbox on any element.
* Option to hide the Add Input button on any element.
* Option to make an element required.
* Option to provide a default value for fields of a newly created item.
* Suggestion feature for Creator and Publisher fields.
* Automatic update of Creator and Publisher fields when a corresponding Title field is modified.
If the item has more than one title, the sync occurs with the first title.


 > At this time, the AvantElements implementation is specific to the Southwest Harbor Public Library's [Digital Archive](http://swhplibrary.net/archive) and therefore **this plugin is not yet usable as-is for another Omeka installation**. However, the source code is provided here for the benefit of software developers who want to learn about the logic or adapt it for use on another project. 
This plugin was developed for the [Southwest Harbor Public Library](http://www.swhplibrary.org/), in Southwest Harbor, Maine. Funding was provided in part by the [John S. and James L. Knight Foundation](https://knightfoundation.org/).

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

Copyright
---------

* Created by [gsoules](https://github.com/gsoules) for the Southwest Harbor Public Library's [Digital Archive](http://swhplibrary.net/archive)
* Copyright George Soules, 2016-2017.
* See [LICENSE](https://github.com/gsoules/AvantRelationships/blob/master/LICENSE) for more information.

