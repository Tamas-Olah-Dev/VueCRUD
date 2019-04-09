# Introduction

This is a Vue.js-based CRUD model manager for Eloquent models. With a little setup it can provide a full CRUD interface (paginated index with optional filters, creation, editing and deletion) to any Eloquent model.

# Installation:

The package needs the project to have vue.js included (it's default in new Laravel projects)

Install the package with
```
composer require datalytix/vuecrud
```

and use 
```
artisan vendor:publish --force 
```
to publish the JS components. When running this command the first time everything in the package should be published, but later only choose vuecrud-scripts from the tags, so that customized views or configurations aren't overwritten. 
If you don't have the Vue component auto-discovery enabled in app.js, add the modules published in resources/js/components.

If everything is done, run
```
npm run dev
```
(or prod, depending on the situation)

# Adding to a model:

- run artisan vuecrud:generate Modelname
- set SUBJECT_SLUG const on the model (typically to a slug version of the model name, so that it can be used in route names and other similar places)
- add VueCRUDManageable trait to model
- set up the abstract methods required by the trait
- set up form fields in formdatabuilder (_modelname_VueCRUDFormdatabuilder)
- add Model::setVueCRUDRoutes() to routes
- edit vuecrud.php in app/config for customization

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

When using a Treeselect component, the library needs to be installed by running npm install --save @riophae/vue-treeselect, and 
app.js has to import it by adding the following to it:

 ```
import Treeselect from '@riophae/vue-treeselect'

import '@riophae/vue-treeselect/dist/vue-treeselect.css'

Vue.component('treeselect', Treeselect);
 ```

The valueset model should have a getVueTreeselectCompatibleCollection public static method that provides the node list. It should use the canBeTurnedIntoKeyValueCollection trait's getVueTreeselectCompatibleValueset method.

## Multi-step forms
The package supports multi-step (multi-stage?) forms, where the different steps only appear when the previous step's form elements were validated. Saving only occurs on the last step. To use multi-step forms:
- use the setStep(int $step) method on form elements in the formdatabuilder
- it's optional (but recommended) to add headers to each step so that the form sections can be distinct. This has to be done in the formdatabuilder's constructor, with the addStepLabel method.
- the save method of the request needs a check before saving/updating: you should insert
 
 ```
 if (!$this->isCurrentStepFinal()) {
     return response('OK');
 }
 ```
as the first three lines of the method.

## File uploads
The FileCollector component allows for uploading files via ajax POST requests. By default it uses the 'public' disk of the built-in Storage service. If you want to add custom handling to uploads and removals, there are two functions you need to define in the controller in question:
 ```
 // returns a filename string
public function processUploadedFile($absolutePathOfFile)
 ```
 

 ```
 //returns null
processRemovedFile($publicUrlOfFile) 
 ```
