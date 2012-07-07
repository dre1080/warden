1.2 / 2012-07-07
================

##### Major Changes

* Native omniauthable feature removed in favor of using Fuel Ninjauth
  with Warden adapter (https://github.com/happyninjas/fuel-ninjauth).
* PHP-CryptLib removed in favor of PHPASS.

##### Other Changes

* No more `Warden::instance` use `Warden::forge()` instead.
* Can now find User roles using the Model_User object. 
  Eg. `$user->is_ROLE()` where "ROLE" is the name of the role you're checking for, returns a bool.
* Many various bugfixes.
* Fixed tasks. View `php oil r warden help`.
* Code formatted. Much less code, same great features.


1.1 / 2012-03-06
================

##### New examples added

* Omniauthable example added
* Example added to show functionality of `confirmable` feature, other features
  except `omniauthable` work similar to this, view /examples folder

##### Deleted deprecated methods in v1.0

* `Warden::authenticate_user()` and `Warden::http_authenticate_user()` deleted
  in favor of `Warden::authenticate()` and `Warden::http_authenticate()` respectively
* Removed unnecessary _init() method in Warden class

##### Other Changes

* Fixed issue where `Warden::current_user()` was returning false, even when logged in.
* Tidied up and improved README
* Warden_Mailer will now use default email set in Email package
* Made the install tasks table_prefix aware ([Tenga](https://github.com/Tenga))
* Code formatted


1.0 / 2012-01-18
================

Huge changes to pretty much everything since initial release. See README for full details.
Contributions from: [andreoav](https://github.com/andreoav), [JesseObrien](https://github.com/JesseObrien),
[rclanan](https://github.com/rclanan)