1.1
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

* Tidied up and improved README
* Warden_Mailer will now use default email set in Email package
* Made the install tasks table_prefix aware. [Tenga](https://github.com/Tenga)
* Code formatted


1.0 / 2012-01-18
================

Huge changes to pretty much everything since initial release. See README for full details.
Contributions from: [andreoav](https://github.com/andreoav), [JesseObrien](https://github.com/JesseObrien),
[rclanan](https://github.com/rclanan)