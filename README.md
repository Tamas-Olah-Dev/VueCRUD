# Introduction

This is a Vue.js-based CRUD model manager for Eloquent models. With a little setup it can provide a full CRUD interface (paginated index with optional filters, creation, editing and deletion) to any Eloquent model.

# Installation:

The package needs the project to have vue.js set up
##### composer require datalytix/vuecrud
##### artisan vendor:publish
If you don't have the Vue component auto-discovery enabled in app.js, add the modules published in resources/js/components.
##### npm run dev


# Adding to a model:

- run artisan vuecrud:generate Modelname
- set SUBJECT_SLUG const on the model (typically to a slug version of the model name, so that it can be used in route names and other similar places)
- add VueCRUDManageable trait to model
- set up the abstract methods required by the trait
- set up form fields in formdatabuilder (_modelname_VueCRUDFormdatabuilder)
- add Model::setVueCRUDRoutes() to routes

For translations to work, there are three things needed:
1) Translations should be in the JSON format in Laravel
2) The main Blade view layout should have somewhere a script containing this: window.laravelLocale = '{{ app()->getLocale() }}'
3) The JS bootstrapper (typically app.js in resources/js) needs to have this code: 
###### window.laravelLocales = {}; 
###### window.laravelLocales['hu'] = require('../lang/hu.json')

with the second row repeated for every language the app has. 

# Noteworthy

When using the Trix rich text editor, attachments are saved and removed in the public drive automatically as they are added to and removed from the rich text component.
 https://laravel.com/docs/5.8/filesystem#the-public-disk
