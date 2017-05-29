<?php
	
	
/*

+rewrite URLS to be controller/method/param1/param2 (too hard!)
+controller directory
--index should become a router, replace Flow object
--finds the controller, method, params

controllers should pick templates and views
--all controllers have view object?
--controllers invoke objects in their construct
--admin controller checks for password? or is that by method?

CONTROLLERS:
user.php
registration.php
workshop.php
admin.php


views directory

Flow.php
UserModel.php
UserContoller.php
DB.php
Model.php
Controller.php

View.php
  *get a snippet
  *get the page, put a header and footer on it
  *set the heading?

*Form.php

same query string structure
?ac=&v=


classes

common
--autoload

BaseModel

BaseController

Controller
  which arguments


BaseView
  (template location)
  parse URL
  get arguments
  build nav bar?
  set template (with data)


Tools

Security

DB

UserFactory
  make user
  find users
  get log in form

User
  get user by email
  get user by id
  get user by key
  get: (all columns from database)
  change email of a user
  delete user
  email login link
  are we logged in?


Form
  validate email

Element
  Texty
  TextArea
  Dropdown
  Radio
  Checkbox
  Hidden
  Submit

Workshop
WorkshopFactory
Registration
RegistrationFactory

Carrier
CarrierFactory
  get all carriers
  get carriers drop down
  get edit text preferences form

Location
LocationFactory

Key
  remember passed-in key
  check memory for key
  verify current key (does it match the user)
  get key by user id (make one if needed)
  generate new key










*/	
	
	
	
	
	
	
?>