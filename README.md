# CustomVocab

Create custom vocabularies and add them as data types to your resource templates.

The Custom Vocab module allows you to create a controlled vocabulary and add it to a specific property in a resource template. When using that template for an item, the property will load with a dropdown limited to the options of the controlled vocabulary, rather than a text entry box.

For example, you may want to create an institution-specific list of locations that correspond to different collections on your campus, or a controlled list of people or places related to your holdings. This can help reduce typos and name variations, and can allow you to offer [metadata browsing](https://omeka.org/s/modules/MetadataBrowse/) for more fields.

Custom Vocab is available to users who are at the Editor role and above.

A custom vocabulary can be imported from another Omeka installation, or exported to another installation.

You can set the controlled vocabulary terms to a list of entered terms, to a list of existing items, or to a list of external URIs with or without labels:

- Terms: a list of plain-text terms, one word or phrase per line. This populates the property as text.
- Items: a drop-down of Item Sets in your Omeka S installation. Choosing one of these will create a custom vocab populated by items from that item set. When used, the property is populated as an Omeka Resource, not text.
- URIs: a list of URIs with or without labels, one URI per line. To include a label, add a space and the label after the URI (for example, "https://youromekainstall.org/item/1119 Canada"). When used, the property will populate as a link to the external resource.

Custom Vocabularies are applied through resource templates. When you are editing the template:

- Add the property to which you want to apply the Custom Vocab.
- Edit the property.
- In the drawer which opens on the right, go to the Other options section and find the Data type dropdown.
- Scroll through the dropdown and select the Custom Vocabulary you want to use.
- Click set changes at the bottom of the drawer.

Be sure to save your changes. 

When this Resource Template is used in an Item or Item Set, the designated properties will always load as a drop down menu with the values from the custom vocabulary.

See the [Omeka S user manual](http://omeka.org/s/docs/user-manual/modules/customvocab/) for user documentation.

# For developers

## Accessing Custom Vocabs through the API

If you are a developer trying to access the custom vocabulary list through the API, the end points are the following:

- List of custom vocabularies are at `/api/custom_vocabs`
- The details of a specific custom vocabulary is at `/api/custom_vocabs/<id>`

# Copyright

CustomVocab is Copyright © 2016-present Corporation for Digital Scholarship, Vienna, Virginia, USA http://digitalscholar.org

The Corporation for Digital Scholarship distributes the Omeka source code
under the GNU General Public License, version 3 (GPLv3). The full text
of this license is given in the license file.

The Omeka name is a registered trademark of the Corporation for Digital Scholarship.

Third-party copyright in this distribution is noted where applicable.

All rights not expressly granted are reserved.

