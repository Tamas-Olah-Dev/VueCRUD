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

# Short version:

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
- use the setConditions(array $conditions) method to set up the conditions based on which the form element will appear. A condition definition looks like this: 
 ```
['field' => fieldname, 'value' => value]
    
//so if we want to show the field only if the 'name' property is 'Danny' or 'Gilbert', we use
    
['field' => 'name', 'value' => 'Danny']
['field' => 'name', 'value' => 'Gilbert']
 ```
 
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

# Detailed version

## Adding VueCRUD capabilities to a model

### Basics
Throughout this chapter, Modelname will always refer to the model in question (e.g. User).
 
The first step is an artisan command:
```
php artisan vuecrud:generate Modelname 
```

This command creates the necessary helper classes (controllers, requests, formdatabuilders and dataproviders).

Next, open the model, add the following three constants:
```
const SUBJECT_SLUG = 'model_name_slug' //e.g. 'user';
const SUBJECT_NAME = 'Name label' //e.g. 'Felhasználó;
const SUBJECT_NAME_PLURAL = 'Plural name label' //e.g. 'Felhasználók';
```
It's typically required but worth mentioning that the $fillable, $with, $dates and $appends properties should be filled as necessary. $appends controls which dynamic properties (a.k.a mutators) are appended to the model when it's converted to JSON. Since most of the time the package uses JSON, all mutators should be added here. 

### The VueCRUDManageable trait

Next add the VueCRUDManageable trait to the model. It will require implementing some functions:
```
public static function getVueCRUDIndexFilters()
```
This returns a collection of VueCRUDIndexfilterBase descendants. Currently they can be of TextVueCRUDIndexfilter and SelectVueCRUDIndexfilter class. A typical setting looks like this:
```
$result = new Illuminate\Support\Collection();
$filter = new TextVueCRUDIndexfilter(
$field,
$label,
''    
);
$result->put(VueCRUDIndexfilterBase::buildPropertyName($field), $filter)
```
$field can be either a property name to search, or (when searching for text) an array of property names. With this definition the dataprovider class can automatically append full-text search constraints to the query.  

```
public static function getVueCRUDIndexColumns()
```
This one has to return an array of labels keyed by property names (they can be mutators, too), to set up how a list of elements should look like. So something like:

return ['id' => 'ID', 'name' => 'Név'];

```
public static function getVueCRUDDetailsFields()
```
Similar to the previous one, this method returns an array that defines how the list of details in the detail view should look like. The component will render a simple DL element for the list.
```
public static function getVueCRUDSortingIndexColumns()
```
This function returns an array defining the columns that allow sorting the list. The key is the column's name (the key in getVueCRUDIndexColumns), while the value is the property used to sort the query. This is to allow mutators to offer sorting (e.g. a 'created_at_label' => 'created_at' definition will allow proper sorting while displaying the label defined in getCreatedAtLabelAttribute)

There are some optional overrides to the trait as well:

```
public static function getVueCRUDModellistButtons()
public static function getModelManagerMainButtons()
```
These allows for customizing the buttons in the model manager component. For details refer to the version in the trait.

```
public static function shouldVueCRUDOperationsBeDisplayed()
```
This allows for injecting authorization logic to the list, like only enabling operations for admin users.

```
public function addAdditionalDetails()
```
If this function is defined on the model, the Details view will incorporate the string it returns. Laravel allows for views to be returned as string via ->render(), so it can be used for complex pages as well. 

The trait also provides the necessary routes. For those to be registered, the Modelname::setVueCRUDRoutes($subjectSlug = null, $urlBase = '/') method has to be called in web.php.  

### Controllers
Generated with the other scaffolding. The base controllers typically need no customizations.

### Data providers

Data provider classes build and execute the index queries, incorporating sorting and filtering options and pagination. They are in App\Dataproviders, and one is created for each model. Basic usage does not warrant any changes in them, but the getBaseQuery function can be changed if special options are needed.

### Formdatabuilders

Formdatabuilder classes provide all the necessary data for creation and update forms, including element positioning and validation. The main method where the form elements are defined is

```
protected static function getFields()
```
This returns a collection of elements keyed by field name (typically the db column's name, but it's not a requirement, as the storing methods don't just map one-on-one). There are multiple form field options to choose from (see below in the Formelement classes chapter), but the typical way this method is structured looks like this:
```
$formelements = new Collection()
$filter = (new FilterClass())
->setLabel('Label')
->setMandatory(true)
...;
$formelements->put('fieldname', $filter);
...

return $formelements;
```
Formdatabuilders also allow for writing custom getters for when components need the data in a special format. If for example a formfield component handling a model relationship needs an array of objects consisting of ids and names, one can define a getter like this:

```
// let's assume that the field name we defined in the getFields method is 'roles'

public function get_roles_value()
{
    //if the subject is null, meaning we are creating a new element, we return an empty array
    //otherwise we use the relationship defined on the model and transform it for the component
    return $this->subject === null
        ? []
        : $this->subject->roles->transform(function($item) {
            return ['id' => $item->id, 'name' => $item->name];
        });
}
```


#### Formelement classes

All formelement classes descend from VueCRUDFormfield. The base class offers these configuration methods:
```
public function setContainerClass($containerClass)
```
This allows for setting a CSS class on the containing div of the form element and its label. With Bootstrap's grid system for example this is where we can build the form grid with 'col-' classes.
```
public function setAdditionalClass($additionalClass)
```
The base formdatabuilder defines the default form element class as 
`protected $defaultContainerClass = 'form-group col';`. The setAdditionalClass method allows adding extra CSS class definitions to the element.
```
public function setLabel($label)
```
This sets the label string of the form element.
```
public function setValuesetClass($valuesetClass)
```
For selects and select-based formfields this function allows for setting the available options. The class provided here has to offer a 'public static function getKeyValueCollection' method (the canBeTurnedIntoKeyValueCollection trait provides one that is typically sufficient for models but can also be customized).
```
public function setValuesetGetter($valuesetGetter)
```
If we want another function for getting the valueset from the valueset provider class, this is where we can provide the name of the static method to use. 
```
public function setValuesetSortedGetter($valuesetSortedGetter)
```
As Javascript does not guarantee the order of the valueset to remain the same as the query provides it, this is where we can provide a list that is not $key => $item but $item => $key. 
```
public function setMandatory($mandatory)
```
A boolean value representing whether or not the field is treated as mandatory (the label gets an extra asterisk and the 'required' validation rule is added automatically) 
```
public function setDefault($default)
```
Sets a default value.
```
public function setRules($rules)
```
Allows adding Laravel validation rules, using the same syntax Laravel does.
```
public function setMessages($messages)
```
Allows adding Laravel validation messages, using the same syntax Laravel does.
```
public function setAddChooseMessage($addChooseMessage)
```
Only relevant in select-type fields, this option allows adding a 'Choose...' option to the list.
```
public function setProps($props, $merge = true)
```
This allows for sending custom props to Vue components. If $merge is false, the props set beforehand are overwritten with $props.
```
public function setHelpTooltip($helpTooltip)
```
If set with this function, a <span class="edit-form-label-tooltip"> element will be added to the label, with the content.
```
public function setOnlyWhenCreating($onlyWhenCreating)
```
This allows for declaring that a formfield is only needed when the form is used to create an element (so it won't show up in editing forms).
```
public function setStep(int $step)
```
For multi-step forms, this allows setting the step the field should appear in.
```
public function setConditions(array $conditions)
```
For multi-step forms, this allows for setting conditions in which the field should appear. $conditions is an array of arrays, each of which has a 'field' element and a "value" element. The form element will only appear during its step if the current subject's 'field' value is equal to 'value'. Multiple definitions are evaluated in an OR relationship.
```
public function setHideIf(array $hideIf, $merge = true)
```
This allows for setting conditions on which the form element should be hidden. $hideIf is an array consiting of 0-indexed arrays with three elements: the first defines the field to check, the second is the comparison operator ('=', '!=', '<' or '>'), and the third is the value. Both the first and the third elements can either be literals or (if they start with '$') fieldnames. So if we define ['$name', '!=', 'Bill'], the form field will be hidden if `subject->name != 'Bill'`. 

##### Available formelement classes and their configuration (where applies)

###### CheckboxgroupVueCRUDFormfield
A list of checkboxes operating as a multi-select component
###### DatepickerVueCRUDFormfield
A Vue.js-based datepicker component. With the `setShowTodayButton` function the 'Go to today' button can be hidden (makes sense for birth date forms, for example). A special getter should be defined for the field in the formdatabuilder, to provide the date value in `Y-m-d` format.
###### DateTimepickerVueCRUDFormfield
A Vue.js-based date-time picker component. With the `setShowTodayButton` function the 'Go to today' button can be hidden (makes sense for birth date forms, for example). A special getter should be defined for the field in the formdatabuilder, to provide the date value in `Y-m-d H:i:s` format.
###### FileCollectorVueCRUDFormfield
A Vue.js file upload component. It automatically uploads added files to the public Storage drive (and if specific methods are defined in the controller, it also runs those). At configuration the addRoutes method has to be used with the SUBJECT_SLUG of the related model, so that routes are configured. It also has to be set to 'Object' mode by calling setObjectMode() (the default 'url' mode just uploads the files and gives back a public url). The setLimit method can be used to limit the maximum number of uploadable files.
  
By defining the processUploadedFileToObject and the processRemovedFileObject public methods in ModelnameVueCRUDController, we can add extra processing steps. The former receives the full path of the uploaded file and has to return an object, while the second gets the id of the file to be removed and needs to return a standard response.  
###### RadiogroupVueCRUDFormfield
This is a basic radio group field.
###### RichtextTrixVueCRUDFormfield
A rich text editor based on the Trix editor by Bootcamp.
###### SelectVueCRUDFormfield
A simple single select field.
###### SlugVueCRUDFormfield
A text field that allows generating a slug from the contents of another text field (defined by `addSourceFieldName($fieldname)`).
###### TextareaVueCRUDFormfield
A simple text area.
###### TextVueCRUDFormfield
A simple text input.
###### NumberVueCRUDFormfield
A simple number input. With the `setForceInteger(bool $value)` method it can be set to allow only integer values (checked during validation)
###### VueTreeselectVueCRUDFormfield
A VueTreeselect wrapper component. Instead of getKeyValueCollection, it uses the valuesetClass's `public static getVueTreeselectCollection()` method. While this has to be defined, typically it's enough that it returns the `getVueTreeselectCompatibleValueset()` function's results from the canBeTurnedIntoKeyValueCollection trait. For tree-type models where relationships are set up (a model has a nullable model_id pointing to a parent model if it's a child, and a hasMany relationship to list the children) the relationship name should be passed to this function to build a tree.
The `setMultiple(bool $multiple)` option allows for enabling/disabling multiselect.
###### TreeselectWithAddButtonVueCRUDFormfield
This is a wrapper for a VueTreeselect and an 'Add' button that can be connected to another VueCRUDManageable model. At configuration the addRoutes method has to be used with the SUBJECT_SLUG of the related model, so that routes are configured. The `setTreeselectLabelFieldName($fieldName)` function is needed to set up the property of the model to be used as a node label.  
###### VueComponentVueCRUDFormfield
This allows for using any custom v-model-compatible Vue.js component as a form field.
###### YesNoSelectVueCRUDFormfield
A simple Yes/No select.

### Form requests

These are also built for the model when the scaffolding is generated. While they work as-is for basic models, they are supposed to be customized.
#### Authorization
Like in the base Laravel form requests, the `public function authorize()` function should be examined and rewritten as necessary.
#### Validation
Validation data is provided by the formdatabuilder class of the model.
#### Saving/updating
The `public function save($subject = null)` method is called by the controller when storing ($subject === null) or updating ($subject !== null) a model. 
