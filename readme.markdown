LiteMVC 0.2.0
=============

A fast, lightweight framework for PHP 5.3+
Optimised for speed and low memory footprint, typical page loads with opcode  
caching enabled are around 5ms.

Some modules are incomplete or for testing purposes, see below for details

Changes from 0.1.0
------------------

* Mostly moved main modules into the framework root
* New abstract resource types Resource, Loadable, Dataset added
* All modules  are a type of resource based on functionality required by the  
module
* Remove preload in favour of load, loaded in order specified in config
* Started to add PHPUnit tests
* Request module now must be loaded, allows init without processing request for  
unit testing
* Loable module dependencies are now processed by init method to allow for  
greater flexibility for unit testing
* Added class map loading to autoload to speed up loading framework classes
* Various tweaks and improvements
* License changed to GPLv3

Module status
-------------

**App - Stable**  
**Auth - Stable**  
**Autoload - Stable**  
**Captcha - Stable**  
**Config - Stable**  
**Controller - Stable**  
**Database - Stable**  
**Dispatcher - Stable**  
Email - Incomplete  
**Error - Stable**  
*Form - Testing*  
**Model - Stable**  
OAuth2 - Incomplete  
*REST - Testing*  
**Request - Stable**  
**Session - Stable**  
**Theme - Stable**  
**View - Stable**

License
-------

LiteMVC is free software: you can redistribute it and/or modify it under the  
terms of the GNU General Public License as published by the Free Software  
Foundation, either version 3 of the License, or any later version.

LiteMVC is distributed in the hope that it will be useful, but WITHOUT ANY  
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A  
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with  
LiteMVC. If not, see <http://www.gnu.org/licenses/>.
